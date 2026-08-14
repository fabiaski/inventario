<?php

require_once __DIR__ . '/../config/conexion.php';

// Verificar que la petición sea POST
if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    header("Location: agregar.php");
    exit;
}

// Obtener datos
$producto = trim($_POST['producto']);
$proveedor = trim($_POST['proveedor']);
$unidad_medida = trim($_POST['unidad_medida']);
$precio = $_POST['precio'];
$fecha_cotizacion = $_POST['fecha_cotizacion'];

// Validación
if (
    empty($producto) ||
    empty($unidad_medida) ||
    empty($precio) ||
    empty($fecha_cotizacion)
) {
    header("Location: agregar.php?mensaje=error");
    exit;
}

// Si el proveedor está vacío
if ($proveedor === "") {
    $proveedor = null;
}

$sql = "INSERT INTO productos
(
    producto,
    proveedor,
    unidad_medida,
    precio,
    fecha_cotizacion
)
VALUES
(
    ?, ?, ?, ?, ?
)";

$stmt = $conexion->prepare($sql);

$stmt->bind_param(
    "ssssd",
    $producto,
    $proveedor,
    $unidad_medida,
    $precio,
    $fecha_cotizacion
);

if ($stmt->execute()) {

    header("Location: index.php?mensaje=guardado");
    exit;

} else {

    echo "Error: " . $stmt->error;
}

$stmt->close();
$conexion->close();