<?php

require_once __DIR__ . '/../config/conexion.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: index.php");
    exit;
}

$id = (int) $_GET['id'];

$sql = "DELETE FROM productos WHERE id = ?";

$stmt = $conexion->prepare($sql);

if (!$stmt) {
    header("Location: index.php?mensaje=errorEliminar");
    exit;
}

$stmt->bind_param("i", $id);

if ($stmt->execute()) {

    header("Location: index.php?mensaje=eliminado");

} else {

    header("Location: index.php?mensaje=errorEliminar");

}

$stmt->close();
$conexion->close();
exit;