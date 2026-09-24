
<?php

require_once __DIR__ . '/../../config/conexion.php';

$titulo = trim($_POST['titulo'] ?? '');
$descripcion = trim($_POST['descripcion'] ?? '');
$fecha = $_POST['fecha'] ?? '';
$hora = $_POST['hora'] ?? null;


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


// Si no se coloca hora, guardar NULL

if ($hora === '') {
    $hora = null;
}


$sql = "
    INSERT INTO eventos
    (
        titulo,
        descripcion,
        fecha,
        hora
    )
    VALUES
    (
        ?,
        ?,
        ?,
        ?
    )
";


$stmt = $conexion->prepare($sql);

if (!$stmt) {

    die(
        'Error preparando la consulta: '
        . $conexion->error
    );

}


$stmt->bind_param(
    "ssss",
    $titulo,
    $descripcion,
    $fecha,
    $hora
);


if ($stmt->execute()) {

    $stmt->close();

    header('Location: index.php?guardado=1');
    exit;

}


$stmt->close();

header('Location: index.php?error=guardar');
exit;
