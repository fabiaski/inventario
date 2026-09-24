
<?php

require_once __DIR__ . '/../../config/conexion.php';

$id = (int) ($_POST['id'] ?? 0);

$titulo = trim($_POST['titulo'] ?? '');

$descripcion = trim(
    $_POST['descripcion'] ?? ''
);

$fecha = $_POST['fecha'] ?? '';

$hora = $_POST['hora'] ?? '';


// Validar ID

if ($id <= 0) {

    header('Location: index.php?error=id');
    exit;

}


// Validar título

if ($titulo === '') {

    header('Location: index.php?error=titulo');
    exit;

}


// Validar fecha

if ($fecha === '') {

    header('Location: index.php?error=fecha');
    exit;

}


// Hora opcional

if ($hora === '') {

    $hora = null;

}


$sql = "
    UPDATE eventos
    SET
        titulo = ?,
        descripcion = ?,
        fecha = ?,
        hora = ?
    WHERE id = ?
";


$stmt = $conexion->prepare($sql);

$stmt->bind_param(
    "ssssi",
    $titulo,
    $descripcion,
    $fecha,
    $hora,
    $id
);


if ($stmt->execute()) {

    $stmt->close();

    header('Location: index.php?editado=1');

    exit;

}


$stmt->close();

header('Location: index.php?error=editar');

exit;

