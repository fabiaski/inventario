<?php
require_once __DIR__ . '/../config/config.php';

// Permite cambiar el título desde cada página.
// Ejemplo:
// $titulo = "Productos";
if (!isset($titulo)) {
    $titulo = "Sistema de Inventario";
}
?>

<!DOCTYPE html>
<html lang="en">

<!--head -->

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="adminHMD professional admin dashboard template">
    <title>myg</title>

    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/vendors/bootstrap-icons/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>

<!--head -->

<body>
    <div class="admin-shell">