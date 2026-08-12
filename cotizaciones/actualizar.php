<?php

require_once __DIR__ . '/../config/conexion.php';


//========================================
// VALIDAR MÉTODO
//========================================

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

    exit('Acceso no permitido.');

}


//========================================
// ID DE LA COTIZACIÓN
//========================================

$cotizacionId = (int) ($_POST['cotizacion_id'] ?? 0);


if ($cotizacionId <= 0) {

    exit('Cotización no válida.');

}


//========================================
// DATOS DE LA COTIZACIÓN
//========================================

$cliente =
    $_POST['cliente'] ?? '';

$fecha =
    $_POST['fecha'] ?? date('Y-m-d');

$observaciones =
    $_POST['observaciones'] ?? '';

$porcentajeRetencion =
    $_POST['porcentaje_retencion'] ?? 0;

$aplicaPago1 =
    $_POST['aplica_pago1'] ?? 0;

$aplicaPago2 =
    $_POST['aplica_pago2'] ?? 0;

$porcentajeGananciaIdeal =
    $_POST['porcentaje_ganancia_ideal'] ?? 20;

$totalVenta =
    $_POST['total_venta'] ?? 0;

$valorRetencion =
    $_POST['valor_retencion'] ?? 0;

$valorPagos =
    $_POST['valor_pagos'] ?? 0;

$llega =
    $_POST['llega'] ?? 0;

$ganancia =
    $_POST['ganancia'] ?? 0;

$gananciaIdeal =
    $_POST['ganancia_ideal'] ?? 0;

$diferencia =
    $_POST['diferencia'] ?? 0;


//========================================
// PRODUCTOS
//========================================

$productos = json_decode(
    $_POST['productos'] ?? '[]',
    true
);


if (empty($productos)) {

    exit(
        'La cotización debe tener al menos un producto.'
    );

}


//========================================
// TRANSACCIÓN
//========================================

$conexion->begin_transaction();


try {


    //========================================
    // ACTUALIZAR COTIZACIÓN
    //========================================

    $sql = "
        UPDATE cotizaciones SET

            cliente = ?,

            fecha = ?,

            observaciones = ?,

            porcentaje_retencion = ?,

            aplica_pago1 = ?,

            aplica_pago2 = ?,

            porcentaje_ganancia_ideal = ?,

            total_venta = ?,

            valor_retencion = ?,

            valor_pagos = ?,

            llega = ?,

            ganancia = ?,

            ganancia_ideal = ?,

            diferencia = ?

        WHERE id = ?
    ";


    $stmt = $conexion->prepare($sql);


    if (!$stmt) {

        throw new Exception(
            "Error preparando actualización: "
            . $conexion->error
        );

    }


    //========================================
    // DATOS DEL UPDATE
    //========================================

    $stmt->bind_param(
        "sssdiiddddddddi",

        $cliente,

        $fecha,

        $observaciones,

        $porcentajeRetencion,

        $aplicaPago1,

        $aplicaPago2,

        $porcentajeGananciaIdeal,

        $totalVenta,

        $valorRetencion,

        $valorPagos,

        $llega,

        $ganancia,

        $gananciaIdeal,

        $diferencia,

        $cotizacionId
    );


    $stmt->execute();

    $stmt->close();


    //========================================
    // ELIMINAR PRODUCTOS ANTERIORES
    //========================================

    $sqlEliminar = "
        DELETE FROM detalle_cotizacion
        WHERE cotizacion_id = ?
    ";


    $stmtEliminar =
        $conexion->prepare($sqlEliminar);


    if (!$stmtEliminar) {

        throw new Exception(
            "Error preparando eliminación: "
            . $conexion->error
        );

    }


    $stmtEliminar->bind_param(
        "i",
        $cotizacionId
    );


    $stmtEliminar->execute();

    $stmtEliminar->close();


    //========================================
    // INSERTAR PRODUCTOS ACTUALIZADOS
    //========================================

    $sqlDetalle = "
        INSERT INTO detalle_cotizacion (

            cotizacion_id,

            producto_id,

            cantidad,

            valor_unidad,

            porcentaje_incremento,

            valor_incremento,

            valor_unidad_incremento,

            valor_total_unidad,

            total_venta

        )

        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
    ";


    $stmtDetalle =
        $conexion->prepare($sqlDetalle);


    if (!$stmtDetalle) {

        throw new Exception(
            "Error preparando detalle: "
            . $conexion->error
        );

    }


    //========================================
    // RECORRER PRODUCTOS
    //========================================

    foreach ($productos as $producto) {


        $productoId =
            (int) $producto['producto_id'];


        $cantidad =
            (float) $producto['cantidad'];


        $valorUnidad =
            (float) $producto['valor_unidad'];


        $porcentajeIncremento =
            (float) $producto['porcentaje_incremento'];


        $valorIncremento =
            (float) $producto['valor_incremento'];


        $valorUnidadIncremento =
            (float) $producto['valor_unidad_incremento'];


        $valorTotalUnidad =
            (float) $producto['valor_total_unidad'];


        $totalVentaProducto =
            (float) $producto['total_venta'];


        //========================================
        // INSERTAR DETALLE
        //========================================

        $stmtDetalle->bind_param(
            "iiddddddd",

            $cotizacionId,

            $productoId,

            $cantidad,

            $valorUnidad,

            $porcentajeIncremento,

            $valorIncremento,

            $valorUnidadIncremento,

            $valorTotalUnidad,

            $totalVentaProducto
        );


        $stmtDetalle->execute();

    }


    $stmtDetalle->close();


    //========================================
    // CONFIRMAR CAMBIOS
    //========================================

    $conexion->commit();


    //========================================
    // REDIRECCIONAR
    //========================================

    header(
        "Location: ver.php?id=" . $cotizacionId
    );

    exit;


} catch (Exception $e) {


    //========================================
    // DESHACER CAMBIOS
    //========================================

    $conexion->rollback();


    echo "Error al actualizar la cotización: "
        . $e->getMessage();

}