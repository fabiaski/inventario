<?php

require_once __DIR__ . '/../../config/conexion.php';


//==================================================
// VALIDAR MÉTODO
//==================================================

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    exit('Acceso no permitido.');
}


//==================================================
// RECIBIR FACTURA
//==================================================

$facturaId = (int) ($_POST['factura_id'] ?? 0);

if ($facturaId <= 0) {
    exit('Factura no válida.');
}


//==================================================
// BUSCAR FACTURA Y CONTRATO
//==================================================

$sql = "
    SELECT
        f.id,
        f.contrato_id,
        f.numero_factura,
        c.numero_contrato
    FROM facturas f

    INNER JOIN contratos c
        ON c.id = f.contrato_id

    WHERE f.id = ?
";


$stmt = $conexion->prepare($sql);

if (!$stmt) {
    exit(
        'Error preparando consulta: '
        . $conexion->error
    );
}


$stmt->bind_param(
    "i",
    $facturaId
);


$stmt->execute();


$resultado = $stmt->get_result();


$factura = $resultado->fetch_assoc();


$stmt->close();


if (!$factura) {
    exit('La factura no existe.');
}


$contratoId =
    (int) $factura['contrato_id'];


//==================================================
// VALIDAR ARCHIVO
//==================================================

if (
    !isset($_FILES['soporte']) ||
    $_FILES['soporte']['error']
    === UPLOAD_ERR_NO_FILE
) {

    exit('Debe seleccionar un archivo.');

}


if (
    $_FILES['soporte']['error']
    !== UPLOAD_ERR_OK
) {

    exit(
        'Ocurrió un error al subir el archivo.'
    );

}


//==================================================
// TAMAÑO MÁXIMO
//==================================================

$tamanoMaximo =
    10 * 1024 * 1024;


if (
    $_FILES['soporte']['size']
    > $tamanoMaximo
) {

    exit(
        'El archivo no puede superar los 10 MB.'
    );

}


//==================================================
// OBTENER EXTENSIÓN
//==================================================

$nombreOriginal =
    basename(
        $_FILES['soporte']['name']
    );


$extension =
    strtolower(
        pathinfo(
            $nombreOriginal,
            PATHINFO_EXTENSION
        )
    );


$extensionesPermitidas = [
    'pdf',
    'jpg',
    'jpeg',
    'png'
];


if (
    !in_array(
        $extension,
        $extensionesPermitidas,
        true
    )
) {

    exit(
        'Tipo de archivo no permitido. '
        . 'Solo PDF, JPG, JPEG y PNG.'
    );

}


//==================================================
// VALIDAR MIME
//==================================================

$finfo =
    finfo_open(FILEINFO_MIME_TYPE);


if (!$finfo) {

    exit(
        'No fue posible validar el tipo '
        . 'del archivo.'
    );

}


$mime =
    finfo_file(
        $finfo,
        $_FILES['soporte']['tmp_name']
    );


finfo_close($finfo);


$mimesPermitidos = [

    'pdf'  => 'application/pdf',

    'jpg'  => 'image/jpeg',

    'jpeg' => 'image/jpeg',

    'png'  => 'image/png'

];


if (
    !isset(
        $mimesPermitidos[$extension]
    )
    ||
    $mime !==
    $mimesPermitidos[$extension]
) {

    exit(
        'El tipo real del archivo '
        . 'no coincide con su extensión.'
    );

}


//==================================================
// CARPETA
//==================================================

$carpeta =
    __DIR__
    . '/../../../uploads/soportes_facturas/';


if (!is_dir($carpeta)) {

    if (
        !mkdir(
            $carpeta,
            0755,
            true
        )
    ) {

        exit(
            'No fue posible crear '
            . 'la carpeta de soportes.'
        );

    }

}


//==================================================
// LIMPIAR NÚMERO DE CONTRATO
//==================================================

$numeroContrato =
    trim(
        $factura['numero_contrato']
    );


$numeroContrato =
    preg_replace(
        '/[^A-Za-z0-9_\-]/',
        '_',
        $numeroContrato
    );


//==================================================
// LIMPIAR NÚMERO DE FACTURA
//==================================================

$numeroFactura =
    trim(
        $factura['numero_factura']
    );


$numeroFacturaLimpio =
    preg_replace(
        '/[^A-Za-z0-9_\-]/',
        '_',
        $numeroFactura
    );


//==================================================
// NOMBRE BASE
//==================================================

$nombreBase =
    'Contrato_' .
    $numeroContrato .
    '_Factura_' .
    $numeroFacturaLimpio;


//==================================================
// NOMBRE DEL ARCHIVO
//==================================================

$archivo =
    $nombreBase .
    '.' .
    $extension;


$ruta =
    $carpeta .
    $archivo;


//==================================================
// EVITAR SOBRESCRIBIR
//==================================================

$contador = 2;


while (
    file_exists($ruta)
) {

    $archivo =
        $nombreBase .
        '_' .
        $contador .
        '.' .
        $extension;


    $ruta =
        $carpeta .
        $archivo;


    $contador++;

}


//==================================================
// MOVER ARCHIVO
//==================================================

if (
    !move_uploaded_file(
        $_FILES['soporte']['tmp_name'],
        $ruta
    )
) {

    exit(
        'No fue posible guardar '
        . 'el archivo en el servidor.'
    );

}


//==================================================
// GUARDAR EN BASE DE DATOS
//==================================================

$sqlSoporte = "
    INSERT INTO soportes_factura (
        factura_id,
        archivo,
        tipo_archivo
    )
    VALUES (?, ?, ?)
";


$stmtSoporte =
    $conexion->prepare($sqlSoporte);


if (!$stmtSoporte) {

    // Si falla BD, eliminamos archivo físico

    if (file_exists($ruta)) {
        unlink($ruta);
    }

    exit(
        'Error preparando soporte: '
        . $conexion->error
    );

}


$stmtSoporte->bind_param(
    "iss",
    $facturaId,
    $archivo,
    $mime
);


if (
    !$stmtSoporte->execute()
) {

    $error =
        $stmtSoporte->error;


    $stmtSoporte->close();


    // Eliminar archivo si falló BD

    if (file_exists($ruta)) {
        unlink($ruta);
    }


    exit(
        'Error guardando soporte: '
        . $error
    );

}


$stmtSoporte->close();


//==================================================
// VOLVER
//==================================================

header(
    "Location: /inventario/facturacion/editar-factura/"
    . $facturaId
);

exit;