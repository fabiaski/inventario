<?php

session_start();

if (!isset($_SESSION['usuario_id'])) {
    header('Location: /inventario/login');
    exit;
}

require_once __DIR__ . '/../../config/conexion.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /inventario/usuarios');
    exit;
}


// ==========================================================
// DATOS
// ==========================================================

$id = (int) ($_POST['id'] ?? 0);

$nombre = trim($_POST['nombre'] ?? '');
$apellido = trim($_POST['apellido'] ?? '');
$usuario = trim($_POST['usuario'] ?? '');

$rolId = (int) ($_POST['rol_id'] ?? 0);

$estado = isset($_POST['estado'])
    ? (int) $_POST['estado']
    : 1;

$password = $_POST['password'] ?? '';
$passwordConfirm = $_POST['password_confirm'] ?? '';


// ==========================================================
// VALIDAR ID
// ==========================================================

if ($id <= 0 || $id === 1) {
    header('Location: /inventario/usuarios?error=usuario_no_valido');
    exit;
}


// ==========================================================
// VALIDAR DATOS
// ==========================================================

if (
    $nombre === '' ||
    $apellido === '' ||
    $usuario === '' ||
    $rolId <= 0
) {
    header('Location: /inventario/usuarios/editar/' . $id . '?error=campos');
    exit;
}

if (!in_array($estado, [0, 1], true)) {
    $estado = 1;
}


// ==========================================================
// VALIDAR CONTRASEÑA
// ==========================================================

if ($password !== '' || $passwordConfirm !== '') {

    if (strlen($password) < 6) {
        header('Location: /inventario/usuarios/editar/' . $id . '?error=password');
        exit;
    }

    if ($password !== $passwordConfirm) {
        header('Location: /inventario/usuarios/editar/' . $id . '?error=password_diferente');
        exit;
    }
}


// ==========================================================
// VERIFICAR USUARIO
// ==========================================================

$sqlExiste = "
    SELECT id
    FROM usuarios
    WHERE usuario = ?
      AND id <> ?
    LIMIT 1
";

$stmtExiste = $conexion->prepare($sqlExiste);

$stmtExiste->bind_param(
    'si',
    $usuario,
    $id
);

$stmtExiste->execute();

$resultadoExiste = $stmtExiste->get_result();

if ($resultadoExiste->num_rows > 0) {

    $stmtExiste->close();

    header('Location: /inventario/usuarios/editar/' . $id . '?error=usuario_existente');
    exit;
}

$stmtExiste->close();


// ==========================================================
// VERIFICAR ROL
// ==========================================================

$sqlRol = "
    SELECT id
    FROM roles
    WHERE id = ?
      AND estado = 1
    LIMIT 1
";

$stmtRol = $conexion->prepare($sqlRol);

$stmtRol->bind_param(
    'i',
    $rolId
);

$stmtRol->execute();

$resultadoRol = $stmtRol->get_result();

if ($resultadoRol->num_rows === 0) {

    $stmtRol->close();

    header('Location: /inventario/usuarios/editar/' . $id . '?error=rol');
    exit;
}

$stmtRol->close();


// ==========================================================
// ACTUALIZAR
// ==========================================================

if ($password !== '') {

    $passwordHash = password_hash(
        $password,
        PASSWORD_DEFAULT
    );

    $sql = "
        UPDATE usuarios
        SET
            nombre = ?,
            apellido = ?,
            usuario = ?,
            password = ?,
            rol_id = ?,
            estado = ?
        WHERE id = ?
    ";

    $stmt = $conexion->prepare($sql);

    $stmt->bind_param(
        'ssssiii',
        $nombre,
        $apellido,
        $usuario,
        $passwordHash,
        $rolId,
        $estado,
        $id
    );

} else {

    $sql = "
        UPDATE usuarios
        SET
            nombre = ?,
            apellido = ?,
            usuario = ?,
            rol_id = ?,
            estado = ?
        WHERE id = ?
    ";

    $stmt = $conexion->prepare($sql);

    $stmt->bind_param(
        'sssiii',
        $nombre,
        $apellido,
        $usuario,
        $rolId,
        $estado,
        $id
    );
}


if (!$stmt->execute()) {

    $stmt->close();

    header('Location: /inventario/usuarios/editar/' . $id . '?error=actualizar');
    exit;
}

$stmt->close();


// ==========================================================
// REGISTRAR ACTIVIDAD
// ==========================================================

$usuarioActualId = (int) $_SESSION['usuario_id'];

$descripcion = 'Actualizó el usuario "' . $usuario . '"';

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
        'editar',
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
// REDIRECCIÓN
// ==========================================================

header('Location: /inventario/usuarios?mensaje=actualizado');
exit;