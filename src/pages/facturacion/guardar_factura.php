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

$proveedor = trim($_POST['proveedor'] ?? '');

$numeroFactura = trim($_POST['numero_factura'] ?? '');

$valor = trim($_POST['valor'] ?? '');

$observacion = trim($_POST['observacion'] ?? '');


//==================================================
// VALIDAR CONTRATO
//==================================================

if ($contratoId <= 0) {
    exit('Contrato no válido.');
}


//==================================================
// VALIDAR PROVEEDOR
//==================================================

if ($proveedor === '') {
    exit('El proveedor es obligatorio.');
}


//==================================================
// VALIDAR NÚMERO DE FACTURA
//==================================================

if ($numeroFactura === '') {
    exit('El número de factura es obligatorio.');
}


//==================================================
// LIMPIAR VALOR DE FACTURA
//==================================================

$valor = str_replace(['.', ','], '', $valor);

if ($valor === '' || !is_numeric($valor)) {
    exit('El valor de la factura no es válido.');
}

$valor = (float) $valor;

if ($valor < 0) {
    exit('El valor de la factura no puede ser negativo.');
}


//==================================================
// INFORMACIÓN TRIBUTARIA
//==================================================

$tieneIva = isset($_POST['tiene_iva']) ? 1 : 0;

$valorIva = null;


$tieneImpoconsumo =
    isset($_POST['tiene_impoconsumo']) ? 1 : 0;

$valorImpoconsumo = null;


$tieneRetencion =
    isset($_POST['tiene_retencion']) ? 1 : 0;

$valorRetencion = null;


//==================================================
// VALIDAR IVA
//==================================================

if ($tieneIva) {

    $valorIva = trim($_POST['valor_iva'] ?? '');

    $valorIva = str_replace(['.', ','], '', $valorIva);

    if ($valorIva === '' || !is_numeric($valorIva)) {
        exit('Debe ingresar un valor válido para el IVA.');
    }

    $valorIva = (float) $valorIva;

    if ($valorIva < 0) {
        exit('El valor del IVA no puede ser negativo.');
    }
}


//==================================================
// VALIDAR IMPOCONSUMO
//==================================================

if ($tieneImpoconsumo) {

    $valorImpoconsumo =
        trim($_POST['valor_impoconsumo'] ?? '');

    $valorImpoconsumo =
        str_replace(['.', ','], '', $valorImpoconsumo);

    if (
        $valorImpoconsumo === '' ||
        !is_numeric($valorImpoconsumo)
    ) {
        exit(
            'Debe ingresar un valor válido para el Impoconsumo.'
        );
    }

    $valorImpoconsumo = (float) $valorImpoconsumo;

    if ($valorImpoconsumo < 0) {
        exit(
            'El valor del Impoconsumo no puede ser negativo.'
        );
    }
}


//==================================================
// VALIDAR RETENCIÓN
//==================================================

if ($tieneRetencion) {

    $valorRetencion =
        trim($_POST['valor_retencion'] ?? '');

    $valorRetencion =
        str_replace(['.', ','], '', $valorRetencion);

    if (
        $valorRetencion === '' ||
        !is_numeric($valorRetencion)
    ) {
        exit(
            'Debe ingresar un valor válido para la Retención.'
        );
    }

    $valorRetencion = (float) $valorRetencion;

    if ($valorRetencion < 0) {
        exit(
            'El valor de la Retención no puede ser negativo.'
        );
    }
}


//==================================================
// VERIFICAR QUE EL CONTRATO EXISTA
//==================================================

$sqlContrato = "
    SELECT id
    FROM contratos
    WHERE id = ?
";

$stmtContrato = $conexion->prepare($sqlContrato);

if (!$stmtContrato) {
    exit(
        'Error preparando consulta: ' .
        $conexion->error
    );
}

$stmtContrato->bind_param(
    "i",
    $contratoId
);

$stmtContrato->execute();

$resultadoContrato =
    $stmtContrato->get_result();

if (!$resultadoContrato->fetch_assoc()) {

    $stmtContrato->close();

    exit('El contrato no existe.');
}

$stmtContrato->close();


//==================================================
// GUARDAR FACTURA
//==================================================

$sql = "
    INSERT INTO facturas (
        contrato_id,
        proveedor,
        numero_factura,
        valor,
        tiene_iva,
        valor_iva,
        tiene_impoconsumo,
        valor_impoconsumo,
        tiene_retencion,
        valor_retencion,
        observacion
    )
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
";


$stmt = $conexion->prepare($sql);

if (!$stmt) {
    exit(
        'Error preparando el registro: ' .
        $conexion->error
    );
}


//==================================================
// ASIGNAR VALORES
//==================================================

$stmt->bind_param(
    "issdididids",
    $contratoId,
    $proveedor,
    $numeroFactura,
    $valor,
    $tieneIva,
    $valorIva,
    $tieneImpoconsumo,
    $valorImpoconsumo,
    $tieneRetencion,
    $valorRetencion,
    $observacion
);


//==================================================
// EJECUTAR
//==================================================

if (!$stmt->execute()) {

    $error = $stmt->error;

    $stmt->close();

    exit(
        'Error al guardar la factura: ' .
        $error
    );
}


$facturaId = $conexion->insert_id;

$stmt->close();


//==================================================
// GUARDAR SOPORTE
//==================================================

if (
    isset($_FILES['soporte']) &&
    $_FILES['soporte']['error'] === UPLOAD_ERR_OK
) {

    $archivo = $_FILES['soporte'];

    $nombreOriginal =
        basename($archivo['name']);

    $tipoArchivo =
        $archivo['type'];

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
        exit(
            'Tipo de archivo no permitido.'
        );
    }


    //==================================================
    // CARPETA DE SOPORTES
    //==================================================

    $carpeta =
        __DIR__ . '/../uploads/soportes_facturas/';


    if (!is_dir($carpeta)) {

        mkdir(
            $carpeta,
            0777,
            true
        );
    }


    //==================================================
    // NOMBRE ÚNICO
    //==================================================

    $nombreArchivo =
        uniqid(
            'factura_',
            true
        ) . '.' . $extension;


    $rutaArchivo =
        $carpeta . $nombreArchivo;


    //==================================================
    // MOVER ARCHIVO
    //==================================================

    if (
        move_uploaded_file(
            $archivo['tmp_name'],
            $rutaArchivo
        )
    ) {

        $sqlSoporte = "
            INSERT INTO soportes_factura (
                factura_id,
                archivo,
                tipo_archivo
            )
            VALUES (?, ?, ?)
        ";


        $stmtSoporte =
            $conexion->prepare(
                $sqlSoporte
            );


        if ($stmtSoporte) {

            $stmtSoporte->bind_param(
                "iss",
                $facturaId,
                $nombreArchivo,
                $tipoArchivo
            );

            $stmtSoporte->execute();

            $stmtSoporte->close();
        }
    }
}


//==================================================
// VOLVER AL CONTRATO
//==================================================

header(
    "Location: ver.php?id=" .
    $contratoId
);

exit;

?>