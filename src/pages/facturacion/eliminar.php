<?php

require_once __DIR__ . '/../../config/conexion.php';


//==================================================
// INICIAR SESIÓN
//==================================================

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


//==================================================
// VALIDAR ID DEL CONTRATO
//==================================================

$contratoId = (int) ($_GET['id'] ?? 0);

if ($contratoId <= 0) {
    exit('Contrato no válido.');
}


//==================================================
// VERIFICAR QUE EL CONTRATO EXISTA
//==================================================

$sqlContrato = "
    SELECT
        id,
        numero_contrato
    FROM contratos
    WHERE id = ?
";


$stmtContrato = $conexion->prepare($sqlContrato);

if (!$stmtContrato) {
    exit(
        'Error preparando la consulta: '
        . $conexion->error
    );
}


$stmtContrato->bind_param(
    "i",
    $contratoId
);


$stmtContrato->execute();


$resultadoContrato =
    $stmtContrato->get_result();


$contrato =
    $resultadoContrato->fetch_assoc();


$stmtContrato->close();


if (!$contrato) {
    exit('El contrato no existe.');
}


$numeroContrato = $contrato['numero_contrato'];


//==================================================
// BUSCAR SOPORTES DE LAS FACTURAS
//==================================================

$sqlSoportes = "
    SELECT
        sf.archivo
    FROM soportes_factura sf

    INNER JOIN facturas f
        ON f.id = sf.factura_id

    WHERE f.contrato_id = ?
";


$stmtSoportes =
    $conexion->prepare(
        $sqlSoportes
    );


if (!$stmtSoportes) {
    exit(
        'Error preparando los soportes: '
        . $conexion->error
    );
}


$stmtSoportes->bind_param(
    "i",
    $contratoId
);


$stmtSoportes->execute();


$resultadoSoportes =
    $stmtSoportes->get_result();


$soportes = [];


while (
    $soporte =
    $resultadoSoportes->fetch_assoc()
) {

    $soportes[] =
        $soporte['archivo'];

}


$stmtSoportes->close();


//==================================================
// ELIMINAR ARCHIVOS FÍSICOS
//==================================================

$directorioSoportes =
    __DIR__
    . '/../../../uploads/soportes_facturas/';


foreach ($soportes as $archivo) {

    $rutaArchivo =
        $directorioSoportes
        . $archivo;


    if (
        is_file($rutaArchivo)
    ) {

        unlink($rutaArchivo);

    }

}


//==================================================
// ELIMINAR CONTRATO
//==================================================

$sqlEliminar = "
    DELETE FROM contratos
    WHERE id = ?
";


$stmtEliminar =
    $conexion->prepare(
        $sqlEliminar
    );


if (!$stmtEliminar) {
    exit(
        'Error preparando la eliminación: '
        . $conexion->error
    );
}


$stmtEliminar->bind_param(
    "i",
    $contratoId
);


if (!$stmtEliminar->execute()) {

    $stmtEliminar->close();

    exit(
        'Error eliminando el contrato: '
        . $conexion->error
    );

}


$stmtEliminar->close();


//==================================================
// REGISTRAR ACTIVIDAD
//==================================================

$usuarioId = (int) ($_SESSION['usuario_id'] ?? 0);

if ($usuarioId > 0) {

    $modulo = 'facturacion';
    $accion = 'eliminar';

    $descripcion =
        'Eliminó el contrato '
        . $numeroContrato;

    $ip =
        $_SERVER['REMOTE_ADDR']
        ?? null;


    $sqlActividad = "
        INSERT INTO actividad (
            usuario_id,
            modulo,
            accion,
            descripcion,
            referencia_id,
            ip
        )
        VALUES (?, ?, ?, ?, ?, ?)
    ";


    $stmtActividad =
        $conexion->prepare(
            $sqlActividad
        );


    if ($stmtActividad) {

        $stmtActividad->bind_param(
            "isssis",
            $usuarioId,
            $modulo,
            $accion,
            $descripcion,
            $contratoId,
            $ip
        );


        $stmtActividad->execute();


        $stmtActividad->close();

    }

}


//==================================================
// VOLVER
//==================================================

header(
    'Location: /inventario/facturacion'
);

exit;