<?php

session_start();

require_once __DIR__ . '/../../config/conexion.php';
require_once __DIR__ . '/../../includes/permisos.php';

header('Content-Type: application/json; charset=utf-8');


// ==================================================
// VALIDAR SESIÓN
// ==================================================

if (!isset($_SESSION['usuario_id'])) {

    echo json_encode([
        'success' => false,
        'error' => 'La sesión ha expirado. Inicie sesión nuevamente.'
    ]);

    exit;
}


// ==================================================
// VALIDAR PERMISO
// ==================================================

if (!tienePermiso('favores', 'editar')) {

    echo json_encode([
        'success' => false,
        'error' => 'No tiene permiso para finalizar favores.'
    ]);

    exit;
}


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
// OBTENER PERSONA
// ==================================================

$stmt = $conexion->prepare("
    SELECT
        id,
        nombre,
        estado
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

$persona = $resultado->fetch_assoc();

$nombrePersona = $persona['nombre'];
$estadoActual = $persona['estado'];

$stmt->close();


// ==================================================
// VERIFICAR ESTADO
// ==================================================

if ($estadoActual !== 'activo') {

    echo json_encode([
        'success' => false,
        'error' => 'La persona no existe o ya está finalizada.'
    ]);

    exit;
}


// ==================================================
// INICIAR TRANSACCIÓN
// ==================================================

$conexion->begin_transaction();


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

    $conexion->rollback();

    echo json_encode([
        'success' => false,
        'error' => 'Error al preparar la actualización: ' . $conexion->error
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
        'error' => 'No se pudo finalizar la persona: ' . $error
    ]);

    exit;
}


// ==================================================
// VERIFICAR ACTUALIZACIÓN
// ==================================================

if ($stmt->affected_rows <= 0) {

    $stmt->close();

    $conexion->rollback();

    echo json_encode([
        'success' => false,
        'error' => 'La persona no existe o ya está finalizada.'
    ]);

    exit;
}

$stmt->close();


// ==================================================
// REGISTRAR ACTIVIDAD
// ==================================================

$usuarioId = (int) $_SESSION['usuario_id'];
$modulo = 'favores';
$accion = 'finalizar';
$descripcion = 'Finalizó la persona "' . $nombrePersona . '"';
$referenciaId = $id;
$ip = $_SERVER['REMOTE_ADDR'] ?? '';

$actividad = $conexion->prepare("
    INSERT INTO actividad
    (
        usuario_id,
        modulo,
        accion,
        descripcion,
        referencia_id,
        ip
    )
    VALUES (?, ?, ?, ?, ?, ?)
");

if (!$actividad) {

    $conexion->rollback();

    echo json_encode([
        'success' => false,
        'error' => 'No se pudo registrar la actividad: ' . $conexion->error
    ]);

    exit;
}

$actividad->bind_param(
    'isssis',
    $usuarioId,
    $modulo,
    $accion,
    $descripcion,
    $referenciaId,
    $ip
);

if (!$actividad->execute()) {

    $error = $actividad->error;

    $actividad->close();

    $conexion->rollback();

    echo json_encode([
        'success' => false,
        'error' => 'No se pudo registrar la actividad: ' . $error
    ]);

    exit;
}

$actividad->close();


// ==================================================
// CONFIRMAR TRANSACCIÓN
// ==================================================

$conexion->commit();


// ==================================================
// RESPUESTA
// ==================================================

echo json_encode([
    'success' => true,
    'mensaje' => 'La persona fue finalizada correctamente.'
]);

exit;