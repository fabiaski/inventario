<?php

require_once __DIR__ . '/../../config/conexion.php';


//==================================================
// VALIDAR MÉTODO
//==================================================

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    exit('Acceso no permitido.');
}


//==================================================
// RECIBIR DATOS
//==================================================

$contratoId = (int) ($_POST['contrato_id'] ?? 0);

$proveedor = trim(
    $_POST['proveedor'] ?? ''
);

$numeroFactura = trim(
    $_POST['numero_factura'] ?? ''
);

$valor = $_POST['valor'] ?? 0;

$porcentajeIva = $_POST['porcentaje_iva'] ?? 19;

$observacion = trim(
    $_POST['observacion'] ?? ''
);


//==================================================
// VALIDACIONES
//==================================================

if ($contratoId <= 0) {
    exit('Contrato no válido.');
}

if ($proveedor === '') {
    exit('El proveedor es obligatorio.');
}

if ($numeroFactura === '') {
    exit('El número de factura es obligatorio.');
}

if (!is_numeric($valor)) {
    exit('El valor de la factura no es válido.');
}

if (!is_numeric($porcentajeIva)) {
    exit('El porcentaje de IVA no es válido.');
}


$valor = (float) $valor;

$porcentajeIva = (int) $porcentajeIva;


if ($valor < 0) {
    exit('El valor de la factura no puede ser negativo.');
}


if (
    $porcentajeIva < 0 ||
    $porcentajeIva > 100
) {
    exit(
        'El porcentaje de IVA debe estar entre 0 y 100.'
    );
}


//==================================================
// BUSCAR CONTRATO
//==================================================

$sqlContrato = "
    SELECT
        id,
        numero_contrato
    FROM contratos
    WHERE id = ?
";


$stmtContrato =
    $conexion->prepare($sqlContrato);


if (!$stmtContrato) {
    exit(
        'Error preparando consulta del contrato: '
        . $conexion->error
    );
}


$stmtContrato->bind_param(
    "i",
    $contratoId
);


$stmtContrato->execute();


$resultadoContrato =
    $stmtContrato->get_result();


$contrato =
    $resultadoContrato->fetch_assoc();


$stmtContrato->close();


if (!$contrato) {
    exit('El contrato no existe.');
}


//==================================================
// CALCULAR IVA
//==================================================

if ($porcentajeIva > 0) {

    $valorSinIva =
        $valor /
        (
            1 +
            ($porcentajeIva / 100)
        );

} else {

    $valorSinIva = $valor;

}


$valorIva =
    $valor - $valorSinIva;


//==================================================
// INICIAR TRANSACCIÓN
//==================================================

$conexion->begin_transaction();


$archivoGuardado = null;

$rutaArchivo = null;


try {


    //==================================================
    // GUARDAR FACTURA
    //==================================================

    $sqlFactura = "
        INSERT INTO facturas (
            contrato_id,
            proveedor,
            numero_factura,
            valor,
            valor_sin_iva,
            porcentaje_iva,
            valor_iva,
            observacion
        )
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ";


    $stmtFactura =
        $conexion->prepare($sqlFactura);


    if (!$stmtFactura) {

        throw new Exception(
            'Error preparando factura: '
            . $conexion->error
        );

    }


    $stmtFactura->bind_param(
        "issddids",
        $contratoId,
        $proveedor,
        $numeroFactura,
        $valor,
        $valorSinIva,
        $porcentajeIva,
        $valorIva,
        $observacion
    );


    if (!$stmtFactura->execute()) {

        throw new Exception(
            'Error guardando factura: '
            . $stmtFactura->error
        );

    }


    // ID de la factura recién creada

    $facturaId =
        $conexion->insert_id;


    $stmtFactura->close();


    //==================================================
    // PROCESAR SOPORTE
    //==================================================

    if (
        isset($_FILES['soporte']) &&
        $_FILES['soporte']['error']
        !== UPLOAD_ERR_NO_FILE
    ) {


        if (
            $_FILES['soporte']['error']
            !== UPLOAD_ERR_OK
        ) {

            throw new Exception(
                'Ocurrió un error al subir el soporte.'
            );

        }


        //==================================================
        // TAMAÑO MÁXIMO: 10 MB
        //==================================================

        $tamanoMaximo =
            10 * 1024 * 1024;


        if (
            $_FILES['soporte']['size']
            > $tamanoMaximo
        ) {

            throw new Exception(
                'El soporte no puede superar los 10 MB.'
            );

        }


        //==================================================
        // OBTENER EXTENSIÓN
        //==================================================

        $nombreOriginal =
            $_FILES['soporte']['name'];


        $extension =
            strtolower(
                pathinfo(
                    $nombreOriginal,
                    PATHINFO_EXTENSION
                )
            );


        $extensionesPermitidas = [
            'pdf',
            'jpg',
            'jpeg',
            'png'
        ];


        if (
            !in_array(
                $extension,
                $extensionesPermitidas,
                true
            )
        ) {

            throw new Exception(
                'Tipo de archivo no permitido. '
                . 'Solo se permiten PDF, JPG, JPEG y PNG.'
            );

        }


        //==================================================
        // VALIDAR MIME
        //==================================================

        $finfo =
            finfo_open(FILEINFO_MIME_TYPE);


        $mime =
            finfo_file(
                $finfo,
                $_FILES['soporte']['tmp_name']
            );


        finfo_close($finfo);


        $mimesPermitidos = [

            'pdf' => 'application/pdf',

            'jpg' => 'image/jpeg',

            'jpeg' => 'image/jpeg',

            'png' => 'image/png'

        ];


        if (
            !isset(
                $mimesPermitidos[$extension]
            )
            ||
            $mime !==
            $mimesPermitidos[$extension]
        ) {

            throw new Exception(
                'El tipo real del archivo '
                . 'no coincide con su extensión.'
            );

        }


        //==================================================
        // CARPETA DE SOPORTES
        //==================================================

        $carpetaSoportes =
            __DIR__
            . '/../uploads/soportes_facturas/';


        if (
            !is_dir($carpetaSoportes)
        ) {

            if (
                !mkdir(
                    $carpetaSoportes,
                    0755,
                    true
                )
            ) {

                throw new Exception(
                    'No fue posible crear '
                    . 'la carpeta de soportes.'
                );

            }

        }


        //==================================================
        // OBTENER NÚMERO DE FACTURA
        //==================================================

        /*
         * Contamos las facturas que ya existen
         * para este contrato.
         *
         * Ejemplo:
         *
         * Contrato ABC-001
         *
         * factura_1
         * factura_2
         * factura_3
         */


        $sqlNumero = "
            SELECT COUNT(*) AS total
            FROM facturas
            WHERE contrato_id = ?
        ";


        $stmtNumero =
            $conexion->prepare($sqlNumero);


        if (!$stmtNumero) {

            throw new Exception(
                'Error calculando número de factura: '
                . $conexion->error
            );

        }


        $stmtNumero->bind_param(
            "i",
            $contratoId
        );


        $stmtNumero->execute();


        $resultadoNumero =
            $stmtNumero->get_result();


        $filaNumero =
            $resultadoNumero->fetch_assoc();


        $stmtNumero->close();


        $numeroFacturaInterno =
            (int) $filaNumero['total'];


        //==================================================
        // LIMPIAR NOMBRE DEL CONTRATO
        //==================================================

        $numeroContrato =
            trim(
                $contrato['numero_contrato']
            );


        /*
         * Quitamos caracteres que no son
         * apropiados para un nombre de archivo.
         */


        $numeroContrato =
            preg_replace(
                '/[^A-Za-z0-9_\-]/',
                '_',
                $numeroContrato
            );


        //==================================================
        // CREAR NOMBRE DEL ARCHIVO
        //==================================================

        $archivoGuardado =
            $numeroContrato
            . '_factura_'
            . $numeroFacturaInterno
            . '.'
            . $extension;


        $rutaArchivo =
            $carpetaSoportes
            . $archivoGuardado;


        //==================================================
        // EVITAR SOBRESCRIBIR ARCHIVOS
        //==================================================

        $contador = 1;


        $nombreBase =
            $numeroContrato
            . '_factura_'
            . $numeroFacturaInterno;


        while (
            file_exists($rutaArchivo)
        ) {

            $archivoGuardado =
                $nombreBase
                . '_'
                . $contador
                . '.'
                . $extension;


            $rutaArchivo =
                $carpetaSoportes
                . $archivoGuardado;


            $contador++;

        }


        //==================================================
        // MOVER ARCHIVO
        //==================================================

        if (
            !move_uploaded_file(
                $_FILES['soporte']['tmp_name'],
                $rutaArchivo
            )
        ) {

            throw new Exception(
                'No fue posible guardar el archivo.'
            );

        }


        //==================================================
        // GUARDAR SOPORTE EN BD
        //==================================================

        $sqlSoporte = "
            INSERT INTO soportes_factura (
                factura_id,
                archivo,
                tipo_archivo
            )
            VALUES (?, ?, ?)
        ";


        $stmtSoporte =
            $conexion->prepare($sqlSoporte);


        if (!$stmtSoporte) {

            throw new Exception(
                'Error preparando soporte: '
                . $conexion->error
            );

        }


        $stmtSoporte->bind_param(
            "iss",
            $facturaId,
            $archivoGuardado,
            $mime
        );


        if (
            !$stmtSoporte->execute()
        ) {

            throw new Exception(
                'Error guardando soporte: '
                . $stmtSoporte->error
            );

        }


        $stmtSoporte->close();

    }


    //==================================================
    // CONFIRMAR TRANSACCIÓN
    //==================================================

    $conexion->commit();


    //==================================================
    // REDIRECCIONAR
    //==================================================

    header(
        "Location: ver.php?id="
        . $contratoId
    );


    exit;


} catch (Exception $e) {


    //==================================================
    // DESHACER CAMBIOS
    //==================================================

    $conexion->rollback();


    //==================================================
    // ELIMINAR ARCHIVO SI YA SE HABÍA GUARDADO
    //==================================================

    if (
        $rutaArchivo !== null &&
        file_exists($rutaArchivo)
    ) {

        unlink($rutaArchivo);

    }


    echo "
        <div style='
            font-family: Arial;
            padding: 30px;
        '>

            <h3>
                Error al guardar la factura
            </h3>

            <p>
                "
                . htmlspecialchars(
                    $e->getMessage()
                )
                . "
            </p>

            <a
                href='agregar_factura.php?id="
                . $contratoId
                . "'
            >
                Volver
            </a>

        </div>
    ";

    exit;

}