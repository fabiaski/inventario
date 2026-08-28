<?php

require_once __DIR__ . '/../../config/conexion.php';


// ==================================================
// VALIDAR DATOS
// ==================================================

$procesoId = (int) ($_POST['proceso_id'] ?? 0);
$procesoProductoId = (int) ($_POST['proceso_producto_id'] ?? 0);
$comprado = (int) ($_POST['comprado'] ?? 0);

if (
    $procesoId <= 0 ||
    $procesoProductoId <= 0 ||
    !in_array($comprado, [0, 1], true)
) {
    header('Location: index.php');
    exit;
}


// ==================================================
// VERIFICAR QUE EL REGISTRO PERTENEZCA AL PROCESO
// ==================================================

$sqlVerificar = "
    SELECT id
    FROM proceso_productos
    WHERE id = ?
    AND proceso_id = ?
    LIMIT 1
";

$stmtVerificar = $conexion->prepare($sqlVerificar);

$stmtVerificar->bind_param(
    'ii',
    $procesoProductoId,
    $procesoId
);

$stmtVerificar->execute();

$resultadoVerificar = $stmtVerificar->get_result();

if ($resultadoVerificar->num_rows === 0) {

    $stmtVerificar->close();

    header(
        'Location: index.php?mensaje=producto_no_encontrado'
    );

    exit;
}

$stmtVerificar->close();


// ==================================================
// FECHA DE COMPRA
// ==================================================

if ($comprado === 1) {

    $fechaCompra = date('Y-m-d H:i:s');

} else {

    $fechaCompra = null;

}


// ==================================================
// ACTUALIZAR PRODUCTO
// ==================================================

$sqlActualizar = "
    UPDATE proceso_productos
    SET
        comprado = ?,
        fecha_compra = ?
    WHERE id = ?
    AND proceso_id = ?
";

$stmtActualizar = $conexion->prepare($sqlActualizar);

if (!$stmtActualizar) {
    die(
        'Error al preparar la actualización: ' .
        $conexion->error
    );
}

$stmtActualizar->bind_param(
    'isii',
    $comprado,
    $fechaCompra,
    $procesoProductoId,
    $procesoId
);

$stmtActualizar->execute();

$stmtActualizar->close();


// ==================================================
// REGRESAR
// ==================================================

header(
    'Location: ver.php?id=' . $procesoId . '&mensaje=actualizado'
);

exit;