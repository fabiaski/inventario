<?php

require_once __DIR__ . '/../../config/conexion.php';


// ==================================================
// VALIDAR ID DEL PROCESO
// ==================================================

$procesoId = (int) ($_GET['id'] ?? 0);

if ($procesoId <= 0) {
    header('Location: index.php');
    exit;
}


// ==================================================
// VERIFICAR QUE EL PROCESO EXISTA
// ==================================================

$sqlProceso = "
    SELECT
        id,
        nombre_contrato,
        estado
    FROM procesos
    WHERE id = ?
    LIMIT 1
";

$stmtProceso = $conexion->prepare($sqlProceso);

if (!$stmtProceso) {
    die(
        'Error al preparar la consulta: ' .
        $conexion->error
    );
}

$stmtProceso->bind_param(
    'i',
    $procesoId
);

$stmtProceso->execute();

$resultadoProceso = $stmtProceso->get_result();

$proceso = $resultadoProceso->fetch_assoc();

$stmtProceso->close();


// ==================================================
// PROCESO NO EXISTE
// ==================================================

if (!$proceso) {

    header(
        'Location: index.php?mensaje=proceso_no_encontrado'
    );

    exit;
}


// ==================================================
// VERIFICAR ESTADO
// ==================================================

if ($proceso['estado'] === 'finalizado') {

    header(
        'Location: finalizados.php?mensaje=ya_finalizado'
    );

    exit;
}


// ==================================================
// CONTAR PRODUCTOS
// ==================================================

$sqlProductos = "
    SELECT
        COUNT(*) AS total_productos,

        SUM(
            CASE
                WHEN comprado = 1 THEN 1
                ELSE 0
            END
        ) AS productos_comprados

    FROM proceso_productos

    WHERE proceso_id = ?
";

$stmtProductos = $conexion->prepare($sqlProductos);

if (!$stmtProductos) {
    die(
        'Error al consultar los productos: ' .
        $conexion->error
    );
}

$stmtProductos->bind_param(
    'i',
    $procesoId
);

$stmtProductos->execute();

$resultadoProductos = $stmtProductos->get_result();

$datosProductos = $resultadoProductos->fetch_assoc();

$stmtProductos->close();


// ==================================================
// DATOS
// ==================================================

$totalProductos = (int) ($datosProductos['total_productos'] ?? 0);

$productosComprados = (int) (
    $datosProductos['productos_comprados'] ?? 0
);


// ==================================================
// NO PERMITIR FINALIZAR SIN PRODUCTOS
// ==================================================

if ($totalProductos === 0) {

    header(
        'Location: ver.php?id=' .
        $procesoId .
        '&mensaje=sin_productos'
    );

    exit;
}


// ==================================================
// NO PERMITIR FINALIZAR SI FALTAN PRODUCTOS
// ==================================================

if ($productosComprados < $totalProductos) {

    header(
        'Location: ver.php?id=' .
        $procesoId .
        '&mensaje=productos_pendientes'
    );

    exit;
}


// ==================================================
// FINALIZAR PROCESO
// ==================================================

$sqlFinalizar = "
    UPDATE procesos

    SET
        estado = 'finalizado',
        fecha_finalizacion = NOW()

    WHERE id = ?
    AND estado = 'proceso'
";

$stmtFinalizar = $conexion->prepare($sqlFinalizar);

if (!$stmtFinalizar) {
    die(
        'Error al preparar la finalización: ' .
        $conexion->error
    );
}

$stmtFinalizar->bind_param(
    'i',
    $procesoId
);

$stmtFinalizar->execute();

$stmtFinalizar->close();


// ==================================================
// REDIRECCIONAR A FINALIZADOS
// ==================================================

header(
    'Location: finalizados.php?mensaje=finalizado'
);

exit;