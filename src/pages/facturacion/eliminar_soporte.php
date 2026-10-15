<?php

require_once __DIR__ . '/../../config/conexion.php';


//==================================================
// INICIAR SESIÓN
//==================================================

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


//==================================================
// VALIDAR MÉTODO
//==================================================

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    exit('Acceso no permitido.');
}


//==================================================
// RECIBIR ID DEL SOPORTE
//==================================================

$soporteId =
    (int) ($_GET['id'] ?? 0);


if ($soporteId <= 0) {
    exit('Soporte no válido.');
}


//==================================================
// BUSCAR SOPORTE
//==================================================

$sql = "
    SELECT
        sf.id,
        sf.factura_id,
        sf.archivo,
        f.contrato_id
    FROM soportes_factura sf

    INNER JOIN facturas f
        ON f.id = sf.factura_id

    WHERE sf.id = ?
";


$stmt =
    $conexion->prepare($sql);


if (!$stmt) {

    exit(
        'Error preparando consulta: '
        . $conexion->error
    );

}


$stmt->bind_param(
    "i",
    $soporteId
);


$stmt->execute();


$resultado =
    $stmt->get_result();


$soporte =
    $resultado->fetch_assoc();


$stmt->close();


if (!$soporte) {
    exit('El soporte no existe.');
}


$facturaId =
    (int) $soporte['factura_id'];

$contratoId =
    (int) $soporte['contrato_id'];

$nombreArchivo =
    $soporte['archivo'];


//==================================================
// BUSCAR NÚMERO DEL CONTRATO
//==================================================

$numeroContrato = '';


$sqlContrato = "
    SELECT
        numero_contrato
    FROM contratos
    WHERE id = ?
";


$stmtContrato =
    $conexion->prepare(
        $sqlContrato
    );


if ($stmtContrato) {

    $stmtContrato->bind_param(
        "i",
        $contratoId
    );

    $stmtContrato->execute();

    $resultadoContrato =
        $stmtContrato->get_result();

    $contrato =
        $resultadoContrato->fetch_assoc();

    if ($contrato) {

        $numeroContrato =
            $contrato['numero_contrato'];

    }

    $stmtContrato->close();

}


//==================================================
// RUTA DEL ARCHIVO
//==================================================

$rutaArchivo =
    __DIR__
    . '/../../../uploads/soportes_facturas/'
    . $nombreArchivo;


//==================================================
// ELIMINAR REGISTRO DE BD
//==================================================

$sqlEliminar = "
    DELETE FROM soportes_factura
    WHERE id = ?
";


$stmtEliminar =
    $conexion->prepare($sqlEliminar);


if (!$stmtEliminar) {

    exit(
        'Error preparando eliminación: '
        . $conexion->error
    );

}


$stmtEliminar->bind_param(
    "i",
    $soporteId
);


if (
    !$stmtEliminar->execute()
) {

    $error =
        $stmtEliminar->error;


    $stmtEliminar->close();


    exit(
        'Error eliminando soporte: '
        . $error
    );

}


$stmtEliminar->close();


//==================================================
// ELIMINAR ARCHIVO FÍSICO
//==================================================

if (
    file_exists($rutaArchivo)
) {

    if (
        !unlink($rutaArchivo)
    ) {

        /*
         * El registro ya fue eliminado
         * de la BD, pero el archivo físico
         * no pudo eliminarse.
         */

        echo "
            <script>
                alert(
                    'El soporte fue eliminado de la base de datos, pero no fue posible eliminar el archivo físico.'
                );

                window.location.href =
                    '/inventario/facturacion/editar-factura/'
                    . $facturaId;
            </script>
        ";

        exit;

    }

}


//==================================================
// REGISTRAR ACTIVIDAD
//==================================================

$usuarioId =
    (int) ($_SESSION['usuario_id'] ?? 0);


if ($usuarioId > 0) {

    $modulo = 'facturacion';

    $accion = 'eliminar_soporte';

    if ($numeroContrato !== '') {

        $descripcion =
            'Eliminó el soporte de la factura #'
            . $facturaId
            . ' del contrato '
            . $numeroContrato;

    } else {

        $descripcion =
            'Eliminó el soporte de la factura #'
            . $facturaId;

    }

    $ip =
        $_SERVER['REMOTE_ADDR']
        ?? null;


    $sqlActividad = "
        INSERT INTO actividad (
            usuario_id,
            modulo,
            accion,
            descripcion,
            referencia_id,
            ip
        )
        VALUES (?, ?, ?, ?, ?, ?)
    ";


    $stmtActividad =
        $conexion->prepare(
            $sqlActividad
        );


    if ($stmtActividad) {

        $stmtActividad->bind_param(
            "isssis",
            $usuarioId,
            $modulo,
            $accion,
            $descripcion,
            $soporteId,
            $ip
        );


        $stmtActividad->execute();


        $stmtActividad->close();

    }

}


//==================================================
// VOLVER
//==================================================

header(
    'Location: /inventario/facturacion/editar-factura/'
    . $facturaId
);

exit;