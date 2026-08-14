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

$facturaId = (int) ($_POST['factura_id'] ?? 0);

$proveedor = trim(
    $_POST['proveedor'] ?? ''
);

$numeroFactura = trim(
    $_POST['numero_factura'] ?? ''
);

$valor = $_POST['valor'] ?? 0;

$porcentajeIva =
    $_POST['porcentaje_iva'] ?? 19;

$observacion = trim(
    $_POST['observacion'] ?? ''
);


//==================================================
// VALIDAR
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

if (!is_numeric($valor)) {
    exit('El valor no es válido.');
}

if (!is_numeric($porcentajeIva)) {
    exit('El porcentaje de IVA no es válido.');
}


$valor = (float) $valor;

$porcentajeIva = (int) $porcentajeIva;


if ($valor < 0) {
    exit('El valor no puede ser negativo.');
}


if (
    $porcentajeIva < 0 ||
    $porcentajeIva > 100
) {
    exit('El porcentaje de IVA no es válido.');
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
    exit('La factura no existe.');
}


$contratoId =
    (int) $factura['contrato_id'];


//==================================================
// CALCULAR IVA
//==================================================

if ($porcentajeIva > 0) {

    $valorSinIva =
        $valor /
        (
            1 +
            ($porcentajeIva / 100)
        );

} else {

    $valorSinIva = $valor;

}


$valorIva =
    $valor - $valorSinIva;


//==================================================
// ACTUALIZAR
//==================================================

$sqlActualizar = "
    UPDATE facturas
    SET
        proveedor = ?,
        numero_factura = ?,
        valor = ?,
        valor_sin_iva = ?,
        porcentaje_iva = ?,
        valor_iva = ?,
        observacion = ?
    WHERE id = ?
";


$stmtActualizar =
    $conexion->prepare($sqlActualizar);


if (!$stmtActualizar) {
    exit(
        'Error preparando actualización: '
        . $conexion->error
    );
}


$stmtActualizar->bind_param(
    "ssddidsi",
    $proveedor,
    $numeroFactura,
    $valor,
    $valorSinIva,
    $porcentajeIva,
    $valorIva,
    $observacion,
    $facturaId
);


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