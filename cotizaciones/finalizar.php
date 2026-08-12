<?php

require_once __DIR__ . '/../config/conexion.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    exit('Acceso no permitido.');
}

$cotizacionId = (int) ($_POST['cotizacion_id'] ?? 0);

if ($cotizacionId <= 0) {
    exit('Cotización no válida.');
}

$sql = "
    UPDATE cotizaciones
    SET estado = 'Finalizada'
    WHERE id = ?
";

$stmt = $conexion->prepare($sql);

if (!$stmt) {
    exit('Error preparando la consulta: ' . $conexion->error);
}

$stmt->bind_param(
    "i",
    $cotizacionId
);

$stmt->execute();

$stmt->close();

header(
    "Location: ver.php?id=" . $cotizacionId
);

exit;