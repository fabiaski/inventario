<?php

require_once __DIR__ . '/../../config/conexion.php';

$texto = $_GET["q"] ?? "";

$sql = "
    SELECT
        id,
        producto,
        unidad_medida,
        precio
    FROM productos
    WHERE producto LIKE ?
    ORDER BY producto
    LIMIT 10
";

$stmt = $conexion->prepare($sql);

$buscar = "%" . $texto . "%";

$stmt->bind_param("s", $buscar);

$stmt->execute();

$resultado = $stmt->get_result();

$productos = [];

while ($fila = $resultado->fetch_assoc()) {

    $productos[] = $fila;

}

echo json_encode(
    $productos,
    JSON_UNESCAPED_UNICODE
);