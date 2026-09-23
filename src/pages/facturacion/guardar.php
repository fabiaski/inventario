<?php

require_once __DIR__ . '/../../config/conexion.php';


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

$fecha = $_POST['fecha'] ?? '';


//==================================================
// VALOR DEL CONTRATO
//==================================================

$valorContrato = trim(
    $_POST['valor_contrato'] ?? ''
);

// Quitar puntos y comas
$valorContrato = str_replace(['.', ','], '', $valorContrato);


if ($valorContrato === '' || !is_numeric($valorContrato)) {

    exit('El valor del contrato no es válido.');

}

$valorContrato = (float) $valorContrato;


if ($valorContrato < 0) {

    exit('El valor del contrato no puede ser negativo.');

}


//==================================================
// IVA
//==================================================

$tieneIva = isset($_POST['tiene_iva']) ? 1 : 0;

$valorIva = null;


if ($tieneIva) {

    $valorIva = trim(
        $_POST['valor_iva'] ?? ''
    );

    // Quitar puntos y comas
    $valorIva = str_replace(['.', ','], '', $valorIva);


    if ($valorIva === '' || !is_numeric($valorIva)) {

header("Location: agregar-fact.php?error=iva");
exit;
    }


    $valorIva = (float) $valorIva;


    if ($valorIva < 0) {

        exit('El valor del IVA no puede ser negativo.');

    }

}


//==================================================
// IMPOCONSUMO
//==================================================

$tieneImpoconsumo = isset($_POST['tiene_impoconsumo']) ? 1 : 0;

$valorImpoconsumo = null;


if ($tieneImpoconsumo) {

    $valorImpoconsumo = trim(
        $_POST['valor_impoconsumo'] ?? ''
    );

    // Quitar puntos y comas
    $valorImpoconsumo = str_replace(['.', ','], '', $valorImpoconsumo);


    if ($valorImpoconsumo === '' || !is_numeric($valorImpoconsumo)) {

header("Location: agregar-fact.php?error=impoconsumo");
exit;
    }


    $valorImpoconsumo = (float) $valorImpoconsumo;


    if ($valorImpoconsumo < 0) {

        exit('El valor del Impoconsumo no puede ser negativo.');

    }

}


//==================================================
// RETENCIÓN
//==================================================

$tieneRetencion = isset($_POST['tiene_retencion']) ? 1 : 0;

$valorRetencion = null;


if ($tieneRetencion) {

    $valorRetencion = trim(
        $_POST['valor_retencion'] ?? ''
    );

    // Quitar puntos y comas
    $valorRetencion = str_replace(['.', ','], '', $valorRetencion);


    if ($valorRetencion === '' || !is_numeric($valorRetencion)) {

header("Location: agregar-fact.php?error=retencion");
exit;
    }


    $valorRetencion = (float) $valorRetencion;


    if ($valorRetencion < 0) {

        exit('El valor de la Retención no puede ser negativo.');

    }

}


//==================================================
// VALIDACIONES GENERALES
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


//==================================================
// INSERTAR CONTRATO
//==================================================

$sql = "
    INSERT INTO contratos (
        numero_contrato,
        objeto_contrato,
        valor_contrato,
        tiene_iva,
        valor_iva,
        tiene_impoconsumo,
        valor_impoconsumo,
        tiene_retencion,
        valor_retencion,
        fecha
    )
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
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
    "ssdiddidds",
    $numeroContrato,
    $objetoContrato,
    $valorContrato,
    $tieneIva,
    $valorIva,
    $tieneImpoconsumo,
    $valorImpoconsumo,
    $tieneRetencion,
    $valorRetencion,
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