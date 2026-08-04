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
<html lang="es">

<head>

    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">

    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title><?= $titulo ?></title>

    <meta name="description" content="Sistema de Inventario">
    <meta name="author" content="Fabiaski">

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css" rel="stylesheet">

    <!-- CSS del sistema -->
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/estilos.css">

</head>

<body>