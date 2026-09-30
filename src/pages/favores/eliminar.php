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
// VERIFICAR PERSONA
// ==================================================

$stmt = $conexion->prepare("
    SELECT id
    FROM favores_personas
    WHERE id = ?
    LIMIT 1
");

if (!$stmt) {

    echo json_encode([
        'success' => false,
        'error' => 'Error al preparar consulta: ' . $conexion->error
    ]);

    exit;
}

$stmt->bind_param('i', $id);

if (!$stmt->execute()) {

    echo json_encode([
        'success' => false,
        'error' => 'Error al consultar persona: ' . $stmt->error
    ]);

    $stmt->close();
    exit;
}

$resultado = $stmt->get_result();

if ($resultado->num_rows === 0) {

    echo json_encode([
        'success' => false,
        'error' => 'La persona no existe.'
    ]);

    $stmt->close();
    exit;
}

$stmt->close();


// ==================================================
// INICIAR TRANSACCIÓN
// ==================================================

$conexion->begin_transaction();


// ==================================================
// ELIMINAR MOVIMIENTOS
// ==================================================

$stmt = $conexion->prepare("
    DELETE FROM favores_movimientos
    WHERE persona_id = ?
");

if (!$stmt) {

    $conexion->rollback();

    echo json_encode([
        'success' => false,
        'error' => 'Error al preparar eliminación de movimientos: ' .
                   $conexion->error
    ]);

    exit;
}

$stmt->bind_param('i', $id);

if (!$stmt->execute()) {

    $error = $stmt->error;

    $stmt->close();

    $conexion->rollback();

    echo json_encode([
        'success' => false,
        'error' => 'No se pudieron eliminar los movimientos: ' . $error
    ]);

    exit;
}

$stmt->close();


// ==================================================
// ELIMINAR PERSONA
// ==================================================

$stmt = $conexion->prepare("
    DELETE FROM favores_personas
    WHERE id = ?
");

if (!$stmt) {

    $conexion->rollback();

    echo json_encode([
        'success' => false,
        'error' => 'Error al preparar eliminación de persona: ' .
                   $conexion->error
    ]);

    exit;
}

$stmt->bind_param('i', $id);

if (!$stmt->execute()) {

    $error = $stmt->error;

    $stmt->close();

    $conexion->rollback();

    echo json_encode([
        'success' => false,
        'error' => 'No se pudo eliminar la persona: ' . $error
    ]);

    exit;
}

$stmt->close();


// ==================================================
// CONFIRMAR
// ==================================================

$conexion->commit();


echo json_encode([
    'success' => true,
    'mensaje' => 'La persona y todos sus movimientos fueron eliminados correctamente.'
]);

exit;