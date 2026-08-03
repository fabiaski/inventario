<?php

require_once __DIR__ . '/../config/conexion.php';

// Verificar que la petición sea POST
if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    header('Location: agregar.php');
    exit;
}

// Obtener datos del formulario
$nombre_producto = trim($_POST['nombre_producto']);
$especificaciones = trim($_POST['especificaciones']);
$cantidad = $_POST['cantidad'];
$unidad_medida = trim($_POST['unidad_medida']);
$precio = $_POST['precio'];
$proveedor = trim($_POST['proveedor']);
$fecha_cotizacion = $_POST['fecha_cotizacion'];

// Validación básica
if (
    empty($nombre_producto) ||
    empty($cantidad) ||
    empty($unidad_medida) ||
    empty($precio) ||
    empty($proveedor) ||
    empty($fecha_cotizacion)
) {
    die("Todos los campos obligatorios deben estar completos.");
}

// Consulta preparada
$sql = "INSERT INTO productos
(
    nombre_producto,
    especificaciones,
    cantidad,
    unidad_medida,
    precio,
    proveedor,
    fecha_cotizacion
)
VALUES
(
    ?, ?, ?, ?, ?, ?, ?
)";

$stmt = $conexion->prepare($sql);

$stmt->bind_param(
    "ssdsdss",
    $nombre_producto,
    $especificaciones,
    $cantidad,
    $unidad_medida,
    $precio,
    $proveedor,
    $fecha_cotizacion
);

if ($stmt->execute()) {

    header("Location: index.php?mensaje=guardado");
    exit;

} else {

    header("Location: agregar.php?mensaje=error");
    exit;

}

$stmt->close();
$conexion->close();