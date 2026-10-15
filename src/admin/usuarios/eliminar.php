<?php

session_start();

if (!isset($_SESSION['usuario_id'])) {
    header('Location: /inventario/login');
    exit;
}

require_once __DIR__ . '/../../config/conexion.php';


// ==========================================================
// VALIDAR ID
// ==========================================================

$id = (int) ($_GET['id'] ?? 0);

if ($id <= 0) {
    header('Location: /inventario/usuarios?error=datos');
    exit;
}


// ==========================================================
// PROTEGER ADMINISTRADOR PRINCIPAL
// ==========================================================

if ($id === 1) {
    header('Location: /inventario/usuarios?error=datos');
    exit;
}


// ==========================================================
// CONSULTAR USUARIO
// ==========================================================

$sql = "
    SELECT
        id,
        nombre,
        apellido,
        usuario,
        estado
    FROM usuarios
    WHERE id = ?
    LIMIT 1
";

$stmt = $conexion->prepare($sql);

if (!$stmt) {
    header('Location: /inventario/usuarios?error=datos');
    exit;
}

$stmt->bind_param('i', $id);
$stmt->execute();

$resultado = $stmt->get_result();
$usuario = $resultado->fetch_assoc();

$stmt->close();


// ==========================================================
// VALIDAR USUARIO
// ==========================================================

if (!$usuario) {
    header('Location: /inventario/usuarios?error=datos');
    exit;
}


// ==========================================================
// SI YA ESTÁ INACTIVO
// ==========================================================

if ((int) $usuario['estado'] === 0) {
    header('Location: /inventario/usuarios?mensaje=desactivado');
    exit;
}


// ==========================================================
// DESACTIVAR USUARIO
// ==========================================================

$sqlActualizar = "
    UPDATE usuarios
    SET estado = 0
    WHERE id = ?
      AND id <> 1
";

$stmtActualizar = $conexion->prepare($sqlActualizar);

if (!$stmtActualizar) {
    header('Location: /inventario/usuarios?error=datos');
    exit;
}

$stmtActualizar->bind_param('i', $id);

if (!$stmtActualizar->execute()) {
    $stmtActualizar->close();

    header('Location: /inventario/usuarios?error=datos');
    exit;
}

$stmtActualizar->close();


// ==========================================================
// REGISTRAR ACTIVIDAD
// ==========================================================

$usuarioActualId = (int) $_SESSION['usuario_id'];

$nombreCompleto = trim(
    $usuario['nombre'] . ' ' . $usuario['apellido']
);

$descripcion = 'Desactivó el usuario "' . $nombreCompleto . '"';

$ip = $_SERVER['REMOTE_ADDR'] ?? null;

$sqlActividad = "
    INSERT INTO actividad
    (
        usuario_id,
        modulo,
        accion,
        descripcion,
        referencia_id,
        ip
    )
    VALUES
    (
        ?,
        'usuarios',
        'eliminar',
        ?,
        ?,
        ?
    )
";

$stmtActividad = $conexion->prepare($sqlActividad);

if ($stmtActividad) {

    $stmtActividad->bind_param(
        'isis',
        $usuarioActualId,
        $descripcion,
        $id,
        $ip
    );

    $stmtActividad->execute();
    $stmtActividad->close();
}


// ==========================================================
// REDIRECCIONAR
// ==========================================================

header('Location: /inventario/usuarios?mensaje=desactivado');
exit;