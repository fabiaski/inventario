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

$proveedor = trim($_POST['proveedor'] ?? '');

$numeroFactura = trim($_POST['numero_factura'] ?? '');

$valor = trim($_POST['valor'] ?? '');

$observacion = trim($_POST['observacion'] ?? '');


//==================================================
// INFORMACIÓN TRIBUTARIA
//==================================================

$tieneIva = isset($_POST['tiene_iva']) ? 1 : 0;

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
// FUNCIÓN PARA CONVERTIR DINERO COLOMBIANO
// 79.500,00 → 79500.00
//==================================================

function convertirDecimal($valor)
{
    $valor = trim($valor);

    if ($valor === '') {
        return null;
    }

    // Quitar puntos de miles
    $valor = str_replace('.', '', $valor);

    // Cambiar coma decimal por punto
    $valor = str_replace(',', '.', $valor);

    if (!is_numeric($valor)) {
        return null;
    }

    return number_format(
        (float) $valor,
        2,
        '.',
        ''
    );
}


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
    exit('El número de factura es obligatorio.');
}


//==================================================
// CONVERTIR VALOR FACTURA
//==================================================

$valor = convertirDecimal($valor);

if ($valor === null) {
    exit('El valor no es válido.');
}

if ((float) $valor < 0) {
    exit('El valor no puede ser negativo.');
}


//==================================================
// VALIDAR IVA
//==================================================

if ($tieneIva) {

    $valorIva = convertirDecimal(
        $_POST['valor_iva'] ?? ''
    );

    if ($valorIva === null) {
        exit(
            'Debe ingresar un valor válido para el IVA.'
        );
    }

    if ((float) $valorIva < 0) {
        exit(
            'El valor del IVA no puede ser negativo.'
        );
    }
}


//==================================================
// VALIDAR IMPOCONSUMO
//==================================================

if ($tieneImpoconsumo) {

    $valorImpoconsumo = convertirDecimal(
        $_POST['valor_impoconsumo'] ?? ''
    );

    if ($valorImpoconsumo === null) {
        exit(
            'Debe ingresar un valor válido para el Impoconsumo.'
        );
    }

    if ((float) $valorImpoconsumo < 0) {
        exit(
            'El valor del Impoconsumo no puede ser negativo.'
        );
    }
}


//==================================================
// VALIDAR RETENCIÓN
//==================================================

if ($tieneRetencion) {

    $valorRetencion = convertirDecimal(
        $_POST['valor_retencion'] ?? ''
    );

    if ($valorRetencion === null) {
        exit(
            'Debe ingresar un valor válido para la Retención.'
        );
    }

    if ((float) $valorRetencion < 0) {
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


$stmtFactura = $conexion->prepare($sqlFactura);

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

$resultado = $stmtFactura->get_result();

$factura = $resultado->fetch_assoc();

$stmtFactura->close();


if (!$factura) {
    exit('La factura no existe.');
}


$contratoId = (int) $factura['contrato_id'];


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


$stmtActualizar = $conexion->prepare(
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

    $error = $stmtActualizar->error;

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
    "Location: /inventario/facturacion/ver/"
    . $contratoId
);

exit;

?>