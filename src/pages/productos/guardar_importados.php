<?php

require_once __DIR__ . '/../../config/conexion.php';
require_once __DIR__ . '/../../includes/permisos.php';

requierePermiso('importar_excel', 'importar');


// ==========================================================
// VALIDAR MÉTODO
// ==========================================================

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    exit('Acceso no permitido.');
}


// ==========================================================
// RECIBIR DATOS
// ==========================================================

$fechaCotizacion = $_POST['fecha_cotizacion'] ?? '';

$productosJson = $_POST['productos'] ?? '[]';

$productos = json_decode(
    $productosJson,
    true
);


// ==========================================================
// VALIDAR FECHA
// ==========================================================

if (empty($fechaCotizacion)) {
    exit('La fecha de cotización es obligatoria.');
}


// ==========================================================
// VALIDAR PRODUCTOS
// ==========================================================

if (
    !is_array($productos) ||
    empty($productos)
) {
    exit('No hay productos para guardar.');
}


// ==========================================================
// CONTADORES
// ==========================================================

$guardados = 0;

$omitidos = 0;

$errores = [];


// ==========================================================
// TRANSACCIÓN
// ==========================================================

$conexion->begin_transaction();


try {

    // ======================================================
    // PREPARAR INSERT
    // ======================================================

    $sql = "
        INSERT INTO productos
        (
            producto,
            unidad_medida,
            precio,
            proveedor,
            fecha_cotizacion
        )
        VALUES
        (
            ?,
            ?,
            ?,
            NULL,
            ?
        )
    ";

    $stmt = $conexion->prepare($sql);

    if (!$stmt) {

        throw new Exception(
            'Error preparando INSERT: ' .
            $conexion->error
        );
    }


    // ======================================================
    // PREPARAR CONSULTA DE EXISTENCIA
    // ======================================================

    $sqlExiste = "
        SELECT id
        FROM productos
        WHERE
            LOWER(TRIM(producto)) = LOWER(TRIM(?))
            AND LOWER(TRIM(unidad_medida)) = LOWER(TRIM(?))
        LIMIT 1
    ";

    $stmtExiste = $conexion->prepare($sqlExiste);

    if (!$stmtExiste) {

        throw new Exception(
            'Error preparando consulta de existencia: ' .
            $conexion->error
        );
    }


    // ======================================================
    // RECORRER PRODUCTOS
    // ======================================================

    foreach ($productos as $producto) {

        $nombre = trim(
            $producto['producto'] ?? ''
        );

        $unidad = trim(
            $producto['unidad_medida'] ?? ''
        );

        $precio = (float) (
            $producto['precio'] ?? 0
        );


        // ==================================================
        // IGNORAR FILAS VACÍAS
        // ==================================================

        if (
            $nombre === '' ||
            $unidad === ''
        ) {

            $omitidos++;

            continue;
        }


        // ==================================================
        // COMPROBAR SI YA EXISTE
        // ==================================================

        $stmtExiste->bind_param(
            'ss',
            $nombre,
            $unidad
        );

        $stmtExiste->execute();

        $resultadoExiste =
            $stmtExiste->get_result();


        if (
            $resultadoExiste &&
            $resultadoExiste->num_rows > 0
        ) {

            $omitidos++;

            continue;
        }


        // ==================================================
        // INSERTAR PRODUCTO
        // ==================================================

        $stmt->bind_param(
            'ssds',
            $nombre,
            $unidad,
            $precio,
            $fechaCotizacion
        );


        if (!$stmt->execute()) {

            $errores[] =
                'No se pudo guardar: ' .
                $nombre .
                ' - ' .
                $stmt->error;

            continue;
        }


        $guardados++;
    }


    // ======================================================
    // CERRAR CONSULTAS
    // ======================================================

    $stmt->close();

    $stmtExiste->close();


    // ======================================================
    // CONFIRMAR TRANSACCIÓN
    // ======================================================

    $conexion->commit();


    // ======================================================
    // REGISTRAR ACTIVIDAD
    // ======================================================

    if ($guardados > 0) {

        $usuarioId =
            (int) ($_SESSION['usuario_id'] ?? 0);

        $descripcion =
            'Importó ' .
            $guardados .
            ' productos desde Excel';

        $ip =
            $_SERVER['REMOTE_ADDR'] ?? null;


        $sqlActividad = "
            INSERT INTO actividad
            (
                usuario_id,
                modulo,
                accion,
                descripcion,
                referencia_id,
                ip
            )
            VALUES
            (?, ?, ?, ?, NULL, ?)
        ";


        $stmtActividad =
            $conexion->prepare($sqlActividad);


        if ($stmtActividad) {

            $modulo = 'importar_excel';

            $accion = 'importar';


            $stmtActividad->bind_param(
                'issss',
                $usuarioId,
                $modulo,
                $accion,
                $descripcion,
                $ip
            );


            $stmtActividad->execute();

            $stmtActividad->close();
        }
    }


} catch (Throwable $e) {

    // ======================================================
    // DESHACER TRANSACCIÓN
    // ======================================================

    $conexion->rollback();

    exit(
        'Error al guardar los productos: ' .
        $e->getMessage()
    );
}


// ==========================================================
// REDIRIGIR A TODOS LOS PRODUCTOS
// ==========================================================

header(
    'Location: /inventario/productos?mensaje=importados'
);

exit;