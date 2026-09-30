<?php

require_once __DIR__ . '/../../config/conexion.php';

header('Content-Type: application/json; charset=utf-8');


// ==================================================
// VALIDAR ID
// ==================================================

$id = (int) ($_POST['id'] ?? 0);

if ($id <= 0) {

    echo json_encode([
        'success' => false,
        'error' => 'ID de persona no válido.'
    ]);

    exit;
}


// ==================================================
// FINALIZAR PERSONA
// ==================================================

$stmt = $conexion->prepare("
    UPDATE favores_personas
    SET
        estado = 'finalizado',
        fecha_finalizacion = NOW()
    WHERE id = ?
      AND estado = 'activo'
");

if (!$stmt) {

    echo json_encode([
        'success' => false,
        'error' => 'Error al preparar la consulta: ' . $conexion->error
    ]);

    exit;
}


$stmt->bind_param('i', $id);


if (!$stmt->execute()) {

    echo json_encode([
        'success' => false,
        'error' => 'No se pudo finalizar la persona: ' . $stmt->error
    ]);

    $stmt->close();

    exit;
}


// ==================================================
// VERIFICAR RESULTADO
// ==================================================

if ($stmt->affected_rows > 0) {

    echo json_encode([
        'success' => true,
        'mensaje' => 'La persona fue finalizada correctamente.'
    ]);

} else {

    echo json_encode([
        'success' => false,
        'error' => 'La persona no existe o ya está finalizada.'
    ]);
}


$stmt->close();

exit;