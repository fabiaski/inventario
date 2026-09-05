<?php

require_once __DIR__ . '/../../config/conexion.php';


//========================================
// OBTENER ID
//========================================

$cotizacionId = (int) ($_GET['id'] ?? 0);


if ($cotizacionId <= 0) {

    exit('Cotización no válida.');

}


//========================================
// INICIAR TRANSACCIÓN
//========================================

$conexion->begin_transaction();


try {


    //========================================
    // ELIMINAR DETALLES
    //========================================

    $sqlDetalle = "
        DELETE FROM detalle_cotizacion
        WHERE cotizacion_id = ?
    ";


    $stmtDetalle =
        $conexion->prepare($sqlDetalle);


    if (!$stmtDetalle) {

        throw new Exception(
            "Error preparando eliminación de detalles: "
            . $conexion->error
        );

    }


    $stmtDetalle->bind_param(
        "i",
        $cotizacionId
    );


    $stmtDetalle->execute();


    $stmtDetalle->close();


    //========================================
    // ELIMINAR COTIZACIÓN
    //========================================

    $sqlCotizacion = "
        DELETE FROM cotizaciones
        WHERE id = ?
    ";


    $stmtCotizacion =
        $conexion->prepare($sqlCotizacion);


    if (!$stmtCotizacion) {

        throw new Exception(
            "Error preparando eliminación de cotización: "
            . $conexion->error
        );

    }


    $stmtCotizacion->bind_param(
        "i",
        $cotizacionId
    );


    $stmtCotizacion->execute();


    //========================================
    // VERIFICAR SI EXISTÍA
    //========================================

    if ($stmtCotizacion->affected_rows === 0) {

        throw new Exception(
            "La cotización no existe."
        );

    }


    $stmtCotizacion->close();


    //========================================
    // CONFIRMAR
    //========================================

    $conexion->commit();


    //========================================
    // VOLVER AL LISTADO
    //========================================

    header("Location: cotizacion.php");

    exit;


} catch (Exception $e) {


    //========================================
    // DESHACER CAMBIOS
    //========================================

    $conexion->rollback();


    echo "Error al eliminar la cotización: "
        . $e->getMessage();

}