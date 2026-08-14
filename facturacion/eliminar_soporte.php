<?php

require_once __DIR__ . '/../config/conexion.php';


//==================================================
// VALIDAR MÉTODO
//==================================================

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    exit('Acceso no permitido.');
}


//==================================================
// RECIBIR ID DEL SOPORTE
//==================================================

$soporteId =
    (int) ($_GET['id'] ?? 0);


if ($soporteId <= 0) {
    exit('Soporte no válido.');
}


//==================================================
// BUSCAR SOPORTE
//==================================================

$sql = "
    SELECT
        sf.id,
        sf.factura_id,
        sf.archivo,
        f.contrato_id
    FROM soportes_factura sf

    INNER JOIN facturas f
        ON f.id = sf.factura_id

    WHERE sf.id = ?
";


$stmt =
    $conexion->prepare($sql);


if (!$stmt) {

    exit(
        'Error preparando consulta: '
        . $conexion->error
    );

}


$stmt->bind_param(
    "i",
    $soporteId
);


$stmt->execute();


$resultado =
    $stmt->get_result();


$soporte =
    $resultado->fetch_assoc();


$stmt->close();


if (!$soporte) {
    exit('El soporte no existe.');
}


$facturaId =
    (int) $soporte['factura_id'];


//==================================================
// RUTA DEL ARCHIVO
//==================================================

$rutaArchivo =
    __DIR__
    . '/../uploads/soportes_facturas/'
    . $soporte['archivo'];


//==================================================
// ELIMINAR REGISTRO DE BD
//==================================================

$sqlEliminar = "
    DELETE FROM soportes_factura
    WHERE id = ?
";


$stmtEliminar =
    $conexion->prepare($sqlEliminar);


if (!$stmtEliminar) {

    exit(
        'Error preparando eliminación: '
        . $conexion->error
    );

}


$stmtEliminar->bind_param(
    "i",
    $soporteId
);


if (
    !$stmtEliminar->execute()
) {

    $error =
        $stmtEliminar->error;


    $stmtEliminar->close();


    exit(
        'Error eliminando soporte: '
        . $error
    );

}


$stmtEliminar->close();


//==================================================
// ELIMINAR ARCHIVO FÍSICO
//==================================================

if (
    file_exists($rutaArchivo)
) {

    if (
        !unlink($rutaArchivo)
    ) {

        /*
         * El registro ya fue eliminado
         * de la BD, pero el archivo físico
         * no pudo eliminarse.
         */

        echo "
            <script>
                alert(
                    'El soporte fue eliminado de la base de datos, pero no fue posible eliminar el archivo físico.'
                );

                window.location.href =
                    'editar_factura.php?id="
                    . $facturaId
                    . "';
            </script>
        ";

        exit;

    }

}


//==================================================
// VOLVER
//==================================================

header(
    "Location: editar_factura.php?id="
    . $facturaId
);

exit;