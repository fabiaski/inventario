<?php

session_start();

if (!isset($_SESSION['usuario_id'])) {
    header('Location: /inventario/login');
    exit;
}

require_once __DIR__ . '/../../config/conexion.php';


// ==========================================================
// VALIDAR MÉTODO
// ==========================================================

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /inventario/usuarios');
    exit;
}


// ==========================================================
// RECIBIR DATOS
// ==========================================================

$nombre = trim($_POST['nombre'] ?? '');
$apellido = trim($_POST['apellido'] ?? '');
$usuario = trim($_POST['usuario'] ?? '');
$password = $_POST['password'] ?? '';
$passwordConfirm = $_POST['password_confirm'] ?? '';
$rolId = (int) ($_POST['rol_id'] ?? 0);
$estado = isset($_POST['estado']) ? (int) $_POST['estado'] : 1;


// ==========================================================
// VALIDAR CAMPOS
// ==========================================================

if (
    $nombre === '' ||
    $apellido === '' ||
    $usuario === '' ||
    $password === '' ||
    $passwordConfirm === '' ||
    $rolId <= 0
) {
    header('Location: /inventario/usuarios?error=campos');
    exit;
}


// ==========================================================
// VALIDAR CONTRASEÑA
// ==========================================================

if ($password !== $passwordConfirm) {
    header('Location: /inventario/usuarios?error=password');
    exit;
}

if (strlen($password) < 6) {
    header('Location: /inventario/usuarios?error=password_corta');
    exit;
}


// ==========================================================
// VALIDAR ESTADO
// ==========================================================

if ($estado !== 0 && $estado !== 1) {
    $estado = 1;
}


// ==========================================================
// VERIFICAR QUE EL USUARIO NO EXISTA
// ==========================================================

$sqlExiste = "
    SELECT id
    FROM usuarios
    WHERE usuario = ?
    LIMIT 1
";

$stmtExiste = $conexion->prepare($sqlExiste);

if (!$stmtExiste) {
    header('Location: /inventario/usuarios?error=error');
    exit;
}

$stmtExiste->bind_param('s', $usuario);
$stmtExiste->execute();

$resultadoExiste = $stmtExiste->get_result();

if ($resultadoExiste->num_rows > 0) {
    $stmtExiste->close();

    header('Location: /inventario/usuarios?error=usuario');
    exit;
}

$stmtExiste->close();


// ==========================================================
// VERIFICAR QUE EL ROL EXISTA Y ESTÉ ACTIVO
// ==========================================================

$sqlRol = "
    SELECT id
    FROM roles
    WHERE id = ?
      AND estado = 1
    LIMIT 1
";

$stmtRol = $conexion->prepare($sqlRol);

if (!$stmtRol) {
    header('Location: /inventario/usuarios?error=error');
    exit;
}

$stmtRol->bind_param('i', $rolId);
$stmtRol->execute();

$resultadoRol = $stmtRol->get_result();

if ($resultadoRol->num_rows === 0) {
    $stmtRol->close();

    header('Location: /inventario/usuarios?error=rol');
    exit;
}

$stmtRol->close();


// ==========================================================
// ENCRIPTAR CONTRASEÑA
// ==========================================================

$passwordHash = password_hash($password, PASSWORD_DEFAULT);

if ($passwordHash === false) {
    header('Location: /inventario/usuarios?error=error');
    exit;
}


// ==========================================================
// GUARDAR USUARIO
// ==========================================================

$sqlInsertar = "
    INSERT INTO usuarios
    (
        nombre,
        apellido,
        usuario,
        password,
        rol_id,
        estado
    )
    VALUES
    (
        ?,
        ?,
        ?,
        ?,
        ?,
        ?
    )
";

$stmtInsertar = $conexion->prepare($sqlInsertar);

if (!$stmtInsertar) {
    header('Location: /inventario/usuarios?error=error');
    exit;
}

$stmtInsertar->bind_param(
    'ssssii',
    $nombre,
    $apellido,
    $usuario,
    $passwordHash,
    $rolId,
    $estado
);

if (!$stmtInsertar->execute()) {

    $stmtInsertar->close();

    header('Location: /inventario/usuarios?error=error');
    exit;
}

$usuarioNuevoId = $stmtInsertar->insert_id;

$stmtInsertar->close();


// ==========================================================
// REGISTRAR ACTIVIDAD
// ==========================================================

$descripcion = 'Creó el usuario "' . $usuario . '"';
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
        'agregar',
        ?,
        ?,
        ?
    )
";

$stmtActividad = $conexion->prepare($sqlActividad);

if ($stmtActividad) {

    $usuarioActualId = (int) $_SESSION['usuario_id'];

    $stmtActividad->bind_param(
        'isis',
        $usuarioActualId,
        $descripcion,
        $usuarioNuevoId,
        $ip
    );

    $stmtActividad->execute();
    $stmtActividad->close();
}


// ==========================================================
// FINALIZAR
// ==========================================================

header('Location: /inventario/usuarios?mensaje=guardado');
exit;