<?php

require_once __DIR__ . '/../../config/conexion.php';


//==================================================
// INICIAR SESIÓN
//==================================================

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


//==================================================
// VALIDAR ID
//==================================================

$facturaId = (int) ($_GET['id'] ?? 0);

if ($facturaId <= 0) {
    exit('Factura no válida.');
}


//==================================================
// BUSCAR FACTURA
//==================================================

$sqlFactura = "
    SELECT
        id,
        contrato_id
    FROM facturas
    WHERE id = ?
";


$stmtFactura =
    $conexion->prepare(
        $sqlFactura
    );


if (!$stmtFactura) {
    exit(
        'Error preparando la consulta: '
        . $conexion->error
    );
}


$stmtFactura->bind_param(
    "i",
    $facturaId
);


$stmtFactura->execute();


$resultadoFactura =
    $stmtFactura->get_result();


$factura =
    $resultadoFactura->fetch_assoc();


$stmtFactura->close();


if (!$factura) {
    exit('La factura no existe.');
}


//==================================================
// GUARDAR ID DEL CONTRATO
//==================================================

$contratoId =
    (int) $factura['contrato_id'];


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
// BUSCAR SOPORTES
//==================================================

$sqlSoportes = "
    SELECT
        archivo
    FROM soportes_factura
    WHERE factura_id = ?
";


$stmtSoportes =
    $conexion->prepare(
        $sqlSoportes
    );


if (!$stmtSoportes) {
    exit(
        'Error preparando los soportes: '
        . $conexion->error
    );
}


$stmtSoportes->bind_param(
    "i",
    $facturaId
);


$stmtSoportes->execute();


$resultadoSoportes =
    $stmtSoportes->get_result();


$soportes = [];


while (
    $soporte =
    $resultadoSoportes->fetch_assoc()
) {

    $soportes[] =
        $soporte['archivo'];

}


$stmtSoportes->close();


//==================================================
// ELIMINAR ARCHIVOS FÍSICOS
//==================================================

$directorioSoportes =
    __DIR__
    . '/../../../uploads/soportes_facturas/';


foreach ($soportes as $archivo) {

    $rutaArchivo =
        $directorioSoportes
        . $archivo;


    if (
        is_file($rutaArchivo)
    ) {

        unlink($rutaArchivo);

    }

}


//==================================================
// ELIMINAR SOPORTES DE LA BASE DE DATOS
//==================================================

$sqlEliminarSoportes = "
    DELETE FROM soportes_factura
    WHERE factura_id = ?
";


$stmtEliminarSoportes =
    $conexion->prepare(
        $sqlEliminarSoportes
    );


if (!$stmtEliminarSoportes) {
    exit(
        'Error preparando la eliminación '
        . 'de soportes: '
        . $conexion->error
    );
}


$stmtEliminarSoportes->bind_param(
    "i",
    $facturaId
);


if (
    !$stmtEliminarSoportes->execute()
) {

    $stmtEliminarSoportes->close();

    exit(
        'Error eliminando los soportes: '
        . $conexion->error
    );

}


$stmtEliminarSoportes->close();


//==================================================
// ELIMINAR FACTURA
//==================================================

$sqlEliminar = "
    DELETE FROM facturas
    WHERE id = ?
";


$stmtEliminar =
    $conexion->prepare(
        $sqlEliminar
    );


if (!$stmtEliminar) {
    exit(
        'Error preparando la eliminación: '
        . $conexion->error
    );
}


$stmtEliminar->bind_param(
    "i",
    $facturaId
);


if (
    !$stmtEliminar->execute()
) {

    $error =
        $stmtEliminar->error;

    $stmtEliminar->close();

    exit(
        'Error eliminando la factura: '
        . $error
    );

}


$stmtEliminar->close();


//==================================================
// REGISTRAR ACTIVIDAD
//==================================================

$usuarioId =
    (int) ($_SESSION['usuario_id'] ?? 0);


if ($usuarioId > 0) {

    $modulo = 'facturacion';

    $accion = 'eliminar';

    if ($numeroContrato !== '') {

        $descripcion =
            'Eliminó la factura #'
            . $facturaId
            . ' del contrato '
            . $numeroContrato;

    } else {

        $descripcion =
            'Eliminó la factura #'
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
            $facturaId,
            $ip
        );


        $stmtActividad->execute();


        $stmtActividad->close();

    }

}


//==================================================
// VOLVER AL CONTRATO
//==================================================

header(
    'Location: /inventario/facturacion/ver/'
    . $contratoId
);

exit;