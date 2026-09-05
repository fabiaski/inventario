<?php

require_once __DIR__ . '/../../config/conexion.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: productos.php");
    exit;
}

$id = (int) $_GET['id'];

$sql = "DELETE FROM productos WHERE id = ?";

$stmt = $conexion->prepare($sql);

if (!$stmt) {
    header("Location: productos.php?mensaje=errorEliminar");
    exit;
}

$stmt->bind_param("i", $id);

if ($stmt->execute()) {

    header("Location: productos.php?mensaje=eliminado");

} else {

    header("Location: productos.php?mensaje=errorEliminar");

}

$stmt->close();
$conexion->close();
exit;