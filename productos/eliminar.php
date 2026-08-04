<?php

require_once __DIR__ . '/../config/conexion.php';

if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit;
}

$id = intval($_GET['id']);

$sql = "DELETE FROM productos WHERE id = ?";

$stmt = $conexion->prepare($sql);

$stmt->bind_param("i", $id);

if ($stmt->execute()) {

    header("Location: index.php?mensaje=eliminado");
    exit;

} else {

    header("Location: index.php?mensaje=errorEliminar");
    exit;

}

$stmt->close();
$conexion->close();