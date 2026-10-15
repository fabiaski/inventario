<?php

session_start();

require_once __DIR__ . '/../config/conexion.php';


// ==========================================================
// GUARDAR ID DEL USUARIO ANTES DE CERRAR SESIÓN
// ==========================================================

$usuarioId = $_SESSION['usuario_id'] ?? null;


// ==========================================================
// REGISTRAR LOGOUT
// ==========================================================

if ($usuarioId) {

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
            'logout',
            'Cierre de sesión',
            NULL,
            ?
        )
    ";

    $stmtActividad = $conexion->prepare($sqlActividad);

    if ($stmtActividad) {

        $stmtActividad->bind_param(
            'is',
            $usuarioId,
            $ip
        );

        $stmtActividad->execute();
        $stmtActividad->close();
    }
}


// ==========================================================
// ELIMINAR SESIÓN
// ==========================================================

$_SESSION = [];


// ==========================================================
// ELIMINAR COOKIE DE SESIÓN
// ==========================================================

if (ini_get('session.use_cookies')) {

    $params = session_get_cookie_params();

    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params['path'],
        $params['domain'],
        $params['secure'],
        $params['httponly']
    );
}


// ==========================================================
// DESTRUIR SESIÓN
// ==========================================================

session_destroy();


// ==========================================================
// VOLVER AL LOGIN
// ==========================================================

header('Location: /inventario/login');
exit;