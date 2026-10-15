<?php

session_start();

require_once __DIR__ . '/../config/conexion.php';


// ==========================================================
// VALIDAR MÉTODO DE ACCESO
// ==========================================================

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /inventario/login');
    exit;
}


// ==========================================================
// RECIBIR DATOS
// ==========================================================

$usuario  = trim($_POST['usuario'] ?? '');
$password = $_POST['password'] ?? '';


// ==========================================================
// VALIDAR CAMPOS
// ==========================================================

if ($usuario === '' || $password === '') {
    header('Location: /inventario/login?error=credenciales');
    exit;
}


// ==========================================================
// BUSCAR USUARIO
// ==========================================================

$sql = "
    SELECT
        u.id,
        u.nombre,
        u.apellido,
        u.usuario,
        u.password,
        u.rol_id,
        u.estado,
        r.nombre AS rol_nombre
    FROM usuarios u
    INNER JOIN roles r
        ON r.id = u.rol_id
    WHERE u.usuario = ?
    LIMIT 1
";

$stmt = $conexion->prepare($sql);

if (!$stmt) {
    header('Location: /inventario/login?error=sesion');
    exit;
}

$stmt->bind_param('s', $usuario);
$stmt->execute();

$resultado = $stmt->get_result();
$usuarioData = $resultado->fetch_assoc();

$stmt->close();


// ==========================================================
// VALIDAR USUARIO Y CONTRASEÑA
// ==========================================================

if (
    !$usuarioData ||
    !password_verify($password, $usuarioData['password'])
) {
    header('Location: /inventario/login?error=credenciales');
    exit;
}


// ==========================================================
// VALIDAR ESTADO DEL USUARIO
// ==========================================================

if ((int) $usuarioData['estado'] !== 1) {
    header('Location: /inventario/login?error=inactivo');
    exit;
}


// ==========================================================
// REGENERAR ID DE SESIÓN
// ==========================================================

session_regenerate_id(true);


// ==========================================================
// GUARDAR DATOS DEL USUARIO EN LA SESIÓN
// ==========================================================

$_SESSION['usuario_id']       = (int) $usuarioData['id'];
$_SESSION['usuario_nombre']   = $usuarioData['nombre'];
$_SESSION['usuario_apellido'] = $usuarioData['apellido'];
$_SESSION['usuario']          = $usuarioData['usuario'];
$_SESSION['rol_id']           = (int) $usuarioData['rol_id'];
$_SESSION['rol_nombre']       = $usuarioData['rol_nombre'];


// ==========================================================
// ACTUALIZAR ÚLTIMO ACCESO
// ==========================================================

$sqlAcceso = "
    UPDATE usuarios
    SET ultimo_acceso = NOW()
    WHERE id = ?
";

$stmtAcceso = $conexion->prepare($sqlAcceso);

if ($stmtAcceso) {

    $stmtAcceso->bind_param(
        'i',
        $usuarioData['id']
    );

    $stmtAcceso->execute();
    $stmtAcceso->close();
}


// ==========================================================
// REGISTRAR ACTIVIDAD - LOGIN
// ==========================================================

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
        'sistema',
        'login',
        'Inicio de sesión',
        NULL,
        ?
    )
";

$stmtActividad = $conexion->prepare($sqlActividad);

if ($stmtActividad) {

    $stmtActividad->bind_param(
        'is',
        $usuarioData['id'],
        $ip
    );

    $stmtActividad->execute();
    $stmtActividad->close();
}


// ==========================================================
// REDIRECCIÓN
// ==========================================================
//
// Todos los usuarios, incluyendo Administradores,
// entran inicialmente al calendario.
//
if ((int) $usuarioData['id'] === 1) {

    header('Location: /inventario/admin');
    exit;
}


header('Location: /inventario/calendario');
exit;