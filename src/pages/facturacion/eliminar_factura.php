<?php

require_once __DIR__ . '/../../config/conexion.php';


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


$stmtFactura = $conexion->prepare($sqlFactura);

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
    . '/../uploads/soportes_facturas/';


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


if (!$stmtEliminar->execute()) {

    $stmtEliminar->close();

    exit(
        'Error eliminando la factura: '
        . $conexion->error
    );

}


$stmtEliminar->close();


//==================================================
// VOLVER AL CONTRATO
//==================================================

header(
    'Location: ver.php?id='
    . $contratoId
);

exit;