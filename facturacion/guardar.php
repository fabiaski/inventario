<?php

require_once __DIR__ . '/../config/conexion.php';


//==================================================
// VALIDAR MÉTODO
//==================================================

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

    exit('Acceso no permitido.');

}


//==================================================
// RECIBIR DATOS
//==================================================

$numeroContrato = trim(
    $_POST['numero_contrato'] ?? ''
);

$objetoContrato = trim(
    $_POST['objeto_contrato'] ?? ''
);

$valorContrato = $_POST['valor_contrato'] ?? 0;

$fecha = $_POST['fecha'] ?? '';


//==================================================
// VALIDACIONES
//==================================================

if ($numeroContrato === '') {

    exit('El número de contrato es obligatorio.');

}


if ($objetoContrato === '') {

    exit('El objeto del contrato es obligatorio.');

}


if ($fecha === '') {

    exit('La fecha es obligatoria.');

}


if (!is_numeric($valorContrato)) {

    exit('El valor del contrato no es válido.');

}


$valorContrato = (float) $valorContrato;


if ($valorContrato < 0) {

    exit('El valor del contrato no puede ser negativo.');

}


//==================================================
// INSERTAR CONTRATO
//==================================================

$sql = "
    INSERT INTO contratos (
        numero_contrato,
        objeto_contrato,
        valor_contrato,
        fecha
    )
    VALUES (?, ?, ?, ?)
";


$stmt = $conexion->prepare($sql);


if (!$stmt) {

    exit(
        'Error preparando el registro: '
        . $conexion->error
    );

}


//==================================================
// VINCULAR DATOS
//==================================================

$stmt->bind_param(
    "ssds",
    $numeroContrato,
    $objetoContrato,
    $valorContrato,
    $fecha
);


//==================================================
// EJECUTAR
//==================================================

if (!$stmt->execute()) {

    $error = $stmt->error;

    $stmt->close();

    exit(
        'Error al guardar el contrato: '
        . $error
    );

}


//==================================================
// OBTENER ID
//==================================================

$contratoId = $conexion->insert_id;


$stmt->close();


//==================================================
// REDIRECCIONAR
//==================================================

header(
    "Location: ver.php?id=" . $contratoId
);

exit;