<?php

require_once __DIR__ . '/../../config/conexion.php';

if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    header("Location: index.php");
    exit;
}

$id = intval($_POST['id']);

$producto = trim($_POST['producto']);
$proveedor = trim($_POST['proveedor']);
$unidad_medida = trim($_POST['unidad_medida']);
$precio = $_POST['precio'];
$fecha_cotizacion = $_POST['fecha_cotizacion'];

$sql = "UPDATE productos
SET
    producto = ?,
    proveedor = ?,
    unidad_medida = ?,
    precio = ?,
    fecha_cotizacion = ?
WHERE id = ?";

$stmt = $conexion->prepare($sql);

$stmt->bind_param(
    "sssdsi",
    $producto,
    $proveedor,
    $unidad_medida,
    $precio,
    $fecha_cotizacion,
    $id
);

if ($stmt->execute()) {

    header("Location: index.php?mensaje=actualizado");
    exit;

} else {

    header("Location: editar.php?id=$id&mensaje=error");
    exit;

}

$stmt->close();
$conexion->close();