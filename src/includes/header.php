<?php

// ==========================================================
// INICIAR SESIÓN
// ==========================================================

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


// ==========================================================
// EVITAR CACHÉ
// ==========================================================

header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Cache-Control: post-check=0, pre-check=0', false);
header('Pragma: no-cache');
header('Expires: 0');


// ==========================================================
// VERIFICAR SESIÓN
// ==========================================================

if (!isset($_SESSION['usuario_id'])) {

    header('Location: /inventario/login?error=sesion');
    exit;
}


// ==========================================================
// CONFIGURACIÓN
// ==========================================================

require_once __DIR__ . '/../config/config.php';

?>


<!DOCTYPE html>
<html lang="es">

<!--head -->

<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>M&G BRO</title>
    <!-- plugins:css -->
     <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="/inventario/src/assets/vendors/feather/feather.css">
    <link rel="stylesheet" href="/inventario/src/assets/vendors/mdi/css/materialdesignicons.min.css">
    <link rel="stylesheet" href="/inventario/src/assets/vendors/ti-icons/css/themify-icons.css">
    <link rel="stylesheet" href="/inventario/src/assets/vendors/font-awesome/css/font-awesome.min.css">
    <link rel="stylesheet" href="/inventario/src/assets/vendors/typicons/typicons.css">
    <link rel="stylesheet" href="/inventario/src/assets/vendors/simple-line-icons/css/simple-line-icons.css">
    <link rel="stylesheet" href="/inventario/src/assets/vendors/css/vendor.bundle.base.css">
    <link rel="stylesheet" href="/inventario/src/assets/vendors/bootstrap-datepicker/bootstrap-datepicker.min.css">
    <!-- endinject -->
    <!-- Plugin css for this page -->
    <link rel="stylesheet" href="/inventario/src/assets/vendors/datatables.net-bs4/dataTables.bootstrap4.css">
    <link rel="stylesheet" type="text/css" href="/inventario/src/assets/js/select.dataTables.min.css">
    <!-- End plugin css for this page -->
    <!-- inject:css -->
    <link rel="stylesheet" href="/inventario/src/assets/css/style.css">
    <!-- endinject -->
    <link rel="shortcut icon" href="/inventario/src/assets/images/LOGO.png" />
  </head>

  C:\xampp\htdocs\inventario\src\assets\css

  C:\xampp\htdocs\inventario\src\pages\includes
<!--head -->

<body class="with-welcome-text">
    <div class="container-scroller">
        

