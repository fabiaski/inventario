<?php

require_once __DIR__ . '/../../config/conexion.php';


// ==================================================
// VALIDAR DATOS
// ==================================================

$procesoId = (int) ($_POST['proceso_id'] ?? 0);
$productoId = (int) ($_POST['producto_id'] ?? 0);

if ($procesoId <= 0 || $productoId <= 0) {
    header('Location: index.php');
    exit;
}


// ==================================================
// VERIFICAR QUE EL PROCESO EXISTA Y ESTÉ ACTIVO
// ==================================================

$sqlProceso = "
    SELECT id
    FROM procesos
    WHERE id = ?
    AND estado = 'proceso'
    LIMIT 1
";

$stmtProceso = $conexion->prepare($sqlProceso);
$stmtProceso->bind_param('i', $procesoId);
$stmtProceso->execute();

$resultadoProceso = $stmtProceso->get_result();

if ($resultadoProceso->num_rows === 0) {
    $stmtProceso->close();

    header('Location: index.php');
    exit;
}

$stmtProceso->close();


// ==================================================
// VERIFICAR QUE EL PRODUCTO EXISTA
// ==================================================

$sqlProducto = "
    SELECT id
    FROM productos
    WHERE id = ?
    LIMIT 1
";

$stmtProducto = $conexion->prepare($sqlProducto);
$stmtProducto->bind_param('i', $productoId);
$stmtProducto->execute();

$resultadoProducto = $stmtProducto->get_result();

if ($resultadoProducto->num_rows === 0) {
    $stmtProducto->close();

    header(
        'Location: ver.php?id=' . $procesoId . '&mensaje=producto_no_encontrado'
    );

    exit;
}

$stmtProducto->close();


// ==================================================
// EVITAR PRODUCTOS DUPLICADOS
// ==================================================

$sqlExiste = "
    SELECT id
    FROM proceso_productos
    WHERE proceso_id = ?
    AND producto_id = ?
    LIMIT 1
";

$stmtExiste = $conexion->prepare($sqlExiste);
$stmtExiste->bind_param(
    'ii',
    $procesoId,
    $productoId
);

$stmtExiste->execute();

$resultadoExiste = $stmtExiste->get_result();

if ($resultadoExiste->num_rows > 0) {

    $stmtExiste->close();

    header(
        'Location: ver.php?id=' . $procesoId . '&mensaje=producto_existente'
    );

    exit;
}

$stmtExiste->close();


// ==================================================
// AGREGAR PRODUCTO
// ==================================================

$sqlInsertar = "
    INSERT INTO proceso_productos (
        proceso_id,
        producto_id,
        comprado
    )
    VALUES (?, ?, 0)
";

$stmtInsertar = $conexion->prepare($sqlInsertar);

if (!$stmtInsertar) {
    die(
        'Error al preparar el registro: ' .
        $conexion->error
    );
}

$stmtInsertar->bind_param(
    'ii',
    $procesoId,
    $productoId
);

$stmtInsertar->execute();

$stmtInsertar->close();


// ==================================================
// REGRESAR AL PROCESO
// ==================================================

header(
    'Location: ver.php?id=' . $procesoId . '&mensaje=producto_agregado'
);

exit;