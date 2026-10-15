<?php

session_start();

if (!isset($_SESSION['usuario_id'])) {
    header('Location: /inventario/login?error=sesion');
    exit;
}

if (
    !isset($_SESSION['rol_nombre']) ||
    $_SESSION['rol_nombre'] !== 'Administrador'
) {
    header('Location: /inventario/calendario');
    exit;
}

require_once __DIR__ . '/include/header.php';
require_once __DIR__ . '/include/sidebar.php';
require_once __DIR__ . '/include/navbar.php';

?>