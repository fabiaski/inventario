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

$facturaId = (int) ($_POST['factura_id'] ?? 0);

$proveedor = trim(
    $_POST['proveedor'] ?? ''
);

$numeroFactura = trim(
    $_POST['numero_factura'] ?? ''
);

$valor = trim(
    $_POST['valor'] ?? ''
);

$observacion = trim(
    $_POST['observacion'] ?? ''
);


//==================================================
// INFORMACIÓN TRIBUTARIA
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
// VALIDAR FACTURA
//==================================================

if ($facturaId <= 0) {

    exit('Factura no válida.');

}


if ($proveedor === '') {

    exit('El proveedor es obligatorio.');

}


if ($numeroFactura === '') {

    exit(
        'El número de factura es obligatorio.'
    );

}


//==================================================
// LIMPIAR VALOR
//==================================================

$valor =
    str_replace(
        ['.', ','],
        '',
        $valor
    );


if (
    $valor === ''
    || !is_numeric($valor)
) {

    exit(
        'El valor no es válido.'
    );

}


$valor =
    (float) $valor;


if ($valor < 0) {

    exit(
        'El valor no puede ser negativo.'
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
// BUSCAR FACTURA
//==================================================

$sqlFactura = "
    SELECT
        id,
        contrato_id
    FROM facturas
    WHERE id = ?
";


$stmtFactura =
    $conexion->prepare($sqlFactura);


if (!$stmtFactura) {

    exit(
        'Error preparando consulta: '
        . $conexion->error
    );

}


$stmtFactura->bind_param(
    "i",
    $facturaId
);


$stmtFactura->execute();


$resultado =
    $stmtFactura->get_result();


$factura =
    $resultado->fetch_assoc();


$stmtFactura->close();


if (!$factura) {

    exit(
        'La factura no existe.'
    );

}


$contratoId =
    (int) $factura['contrato_id'];


//==================================================
// ACTUALIZAR FACTURA
//==================================================

$sqlActualizar = "
    UPDATE facturas
    SET
        proveedor = ?,
        numero_factura = ?,
        valor = ?,
        tiene_iva = ?,
        valor_iva = ?,
        tiene_impoconsumo = ?,
        valor_impoconsumo = ?,
        tiene_retencion = ?,
        valor_retencion = ?,
        observacion = ?
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
    "ssdidididsi",
    $proveedor,
    $numeroFactura,
    $valor,
    $tieneIva,
    $valorIva,
    $tieneImpoconsumo,
    $valorImpoconsumo,
    $tieneRetencion,
    $valorRetencion,
    $observacion,
    $facturaId
);


//==================================================
// EJECUTAR
//==================================================

if (!$stmtActualizar->execute()) {

    $error =
        $stmtActualizar->error;

    $stmtActualizar->close();

    exit(
        'Error actualizando factura: '
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