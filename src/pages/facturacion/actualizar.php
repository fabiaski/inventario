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

$contratoId = (int) ($_POST['id'] ?? 0);

$numeroContrato = trim(
    $_POST['numero_contrato'] ?? ''
);

$fecha = trim(
    $_POST['fecha'] ?? ''
);

$objetoContrato = trim(
    $_POST['objeto_contrato'] ?? ''
);

$valorContrato = trim(
    $_POST['valor_contrato'] ?? ''
);


//==================================================
// RECIBIR INFORMACIÓN TRIBUTARIA
//==================================================

$tieneIva =
    isset($_POST['tiene_iva'])
    ? 1
    : 0;

$valorIva = null;


$tieneImpoconsumo =
    isset($_POST['tiene_impoconsumo'])
    ? 1
    : 0;

$valorImpoconsumo = null;


$tieneRetencion =
    isset($_POST['tiene_retencion'])
    ? 1
    : 0;

$valorRetencion = null;


//==================================================
// VALIDAR ID
//==================================================

if ($contratoId <= 0) {

    exit('Contrato no válido.');

}


//==================================================
// VALIDAR CAMPOS
//==================================================

if ($numeroContrato === '') {

    exit('El número de contrato es obligatorio.');

}


if ($fecha === '') {

    exit('La fecha es obligatoria.');

}


if ($objetoContrato === '') {

    exit('El objeto del contrato es obligatorio.');

}


//==================================================
// LIMPIAR VALOR DEL CONTRATO
//==================================================

$valorContrato =
    str_replace(
        ['.', ','],
        '',
        $valorContrato
    );


if (
    $valorContrato === ''
    || !is_numeric($valorContrato)
) {

    exit('El valor del contrato no es válido.');

}


$valorContrato =
    (float) $valorContrato;


if ($valorContrato < 0) {

    exit(
        'El valor del contrato no puede ser negativo.'
    );

}


//==================================================
// VALIDAR IVA
//==================================================

if ($tieneIva) {

    $valorIva =
        trim(
            $_POST['valor_iva'] ?? ''
        );


    $valorIva =
        str_replace(
            ['.', ','],
            '',
            $valorIva
        );


    if (
        $valorIva === ''
        || !is_numeric($valorIva)
    ) {

        exit(
            'Debe ingresar un valor válido para el IVA.'
        );

    }


    $valorIva =
        (float) $valorIva;


    if ($valorIva < 0) {

        exit(
            'El valor del IVA no puede ser negativo.'
        );

    }

}


//==================================================
// VALIDAR IMPOCONSUMO
//==================================================

if ($tieneImpoconsumo) {

    $valorImpoconsumo =
        trim(
            $_POST['valor_impoconsumo'] ?? ''
        );


    $valorImpoconsumo =
        str_replace(
            ['.', ','],
            '',
            $valorImpoconsumo
        );


    if (
        $valorImpoconsumo === ''
        || !is_numeric($valorImpoconsumo)
    ) {

        exit(
            'Debe ingresar un valor válido para el Impoconsumo.'
        );

    }


    $valorImpoconsumo =
        (float) $valorImpoconsumo;


    if ($valorImpoconsumo < 0) {

        exit(
            'El valor del Impoconsumo no puede ser negativo.'
        );

    }

}


//==================================================
// VALIDAR RETENCIÓN
//==================================================

if ($tieneRetencion) {

    $valorRetencion =
        trim(
            $_POST['valor_retencion'] ?? ''
        );


    $valorRetencion =
        str_replace(
            ['.', ','],
            '',
            $valorRetencion
        );


    if (
        $valorRetencion === ''
        || !is_numeric($valorRetencion)
    ) {

        exit(
            'Debe ingresar un valor válido para la Retención.'
        );

    }


    $valorRetencion =
        (float) $valorRetencion;


    if ($valorRetencion < 0) {

        exit(
            'El valor de la Retención no puede ser negativo.'
        );

    }

}


//==================================================
// VERIFICAR QUE EL CONTRATO EXISTA
//==================================================

$sqlExiste = "
    SELECT id
    FROM contratos
    WHERE id = ?
";


$stmtExiste =
    $conexion->prepare($sqlExiste);


if (!$stmtExiste) {

    exit(
        'Error preparando consulta: '
        . $conexion->error
    );

}


$stmtExiste->bind_param(
    "i",
    $contratoId
);


$stmtExiste->execute();


$resultado =
    $stmtExiste->get_result();


if (!$resultado->fetch_assoc()) {

    $stmtExiste->close();

    exit(
        'El contrato no existe.'
    );

}


$stmtExiste->close();


//==================================================
// ACTUALIZAR CONTRATO
//==================================================

$sqlActualizar = "
    UPDATE contratos
    SET
        numero_contrato = ?,
        fecha = ?,
        objeto_contrato = ?,
        valor_contrato = ?,
        tiene_iva = ?,
        valor_iva = ?,
        tiene_impoconsumo = ?,
        valor_impoconsumo = ?,
        tiene_retencion = ?,
        valor_retencion = ?
    WHERE id = ?
";


$stmtActualizar =
    $conexion->prepare(
        $sqlActualizar
    );


if (!$stmtActualizar) {

    exit(
        'Error preparando actualización: '
        . $conexion->error
    );

}


//==================================================
// ASIGNAR VALORES
//==================================================

$stmtActualizar->bind_param(
    "sssdidididi",
    $numeroContrato,
    $fecha,
    $objetoContrato,
    $valorContrato,
    $tieneIva,
    $valorIva,
    $tieneImpoconsumo,
    $valorImpoconsumo,
    $tieneRetencion,
    $valorRetencion,
    $contratoId
);


//==================================================
// EJECUTAR ACTUALIZACIÓN
//==================================================

if (!$stmtActualizar->execute()) {

    $error =
        $stmtActualizar->error;

    $stmtActualizar->close();

    exit(
        'Error actualizando contrato: '
        . $error
    );

}


$stmtActualizar->close();


//==================================================
// VOLVER AL CONTRATO
//==================================================

header(
    "Location: ver.php?id="
    . $contratoId
);

exit;

?>