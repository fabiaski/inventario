<?php

require_once __DIR__ . '/../config/conexion.php';


//==================================================
// VALIDAR MÉTODO
//==================================================

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

    header('Location: index.php');

    exit;

}


//==================================================
// RECIBIR DATOS
//==================================================

$numeroContrato =
    trim(
        $_POST['numero_contrato'] ?? ''
    );


$entidad =
    trim(
        $_POST['entidad'] ?? ''
    );


$objeto =
    trim(
        $_POST['objeto'] ?? ''
    );


$valor =
    (float) (
        $_POST['valor'] ?? 0
    );


$anio =
    (int) (
        $_POST['anio'] ?? 0
    );


//==================================================
// VALIDACIONES
//==================================================

$errores = [];


if ($numeroContrato === '') {

    $errores[] =
        'El número de contrato es obligatorio.';

}


if ($entidad === '') {

    $errores[] =
        'La entidad es obligatoria.';

}


if ($objeto === '') {

    $errores[] =
        'El objeto del contrato es obligatorio.';

}


if ($valor <= 0) {

    $errores[] =
        'El valor del contrato debe ser mayor que cero.';

}


if (
    $anio < 2000 ||
    $anio > 2100
) {

    $errores[] =
        'El año del contrato no es válido.';

}


//==================================================
// SI HAY ERRORES
//==================================================

if (!empty($errores)) {

    echo '<h3>Error al guardar el contrato</h3>';

    echo '<ul>';

    foreach ($errores as $error) {

        echo '<li>';

        echo htmlspecialchars($error);

        echo '</li>';

    }

    echo '</ul>';

    echo '<a href="agregar.php">Volver</a>';

    exit;

}


//==================================================
// INSERTAR CONTRATO
//==================================================

$sql = "
    INSERT INTO contratos_smlmv (
        numero_contrato,
        entidad,
        objeto,
        valor,
        anio
    )
    VALUES (?, ?, ?, ?, ?)
";


$stmt =
    $conexion->prepare($sql);


if (!$stmt) {

    exit(
        'Error preparando la consulta: '
        . $conexion->error
    );

}


//==================================================
// ASIGNAR PARÁMETROS
//==================================================

$stmt->bind_param(
    "sssdi",
    $numeroContrato,
    $entidad,
    $objeto,
    $valor,
    $anio
);


//==================================================
// EJECUTAR
//==================================================

if (!$stmt->execute()) {

    $error =
        $stmt->error;

    $stmt->close();

    exit(
        'Error guardando el contrato: '
        . htmlspecialchars($error)
    );

}


$stmt->close();


//==================================================
// REDIRIGIR
//==================================================

header(
    'Location: index.php?guardado=1'
);

exit;