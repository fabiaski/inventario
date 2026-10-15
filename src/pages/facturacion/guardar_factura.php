<?php

require_once __DIR__ . '/../../config/conexion.php';

// ==================================================
// SESIÓN
// ==================================================

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ==================================================
// VALIDAR MÉTODO
// ==================================================

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    exit('Acceso no permitido.');
}

// ==================================================
// RECIBIR DATOS
// ==================================================

$contratoId = (int) ($_POST['contrato_id'] ?? 0);

$proveedor = trim($_POST['proveedor'] ?? '');

$tieneNumeroFactura = isset($_POST['tiene_numero_factura'])
    && $_POST['tiene_numero_factura'] == '1';

$numeroFactura = $tieneNumeroFactura
    ? trim($_POST['numero_factura'] ?? '')
    : null;

$fechaFactura = trim($_POST['fecha_factura'] ?? '');

$valor = trim($_POST['valor'] ?? '');

$observacion = trim($_POST['observacion'] ?? '');

// ==================================================
// VALIDAR CONTRATO
// ==================================================

if ($contratoId <= 0) {
    exit('Contrato no válido.');
}

// ==================================================
// VALIDAR PROVEEDOR
// ==================================================

if ($proveedor === '') {
    exit('El proveedor es obligatorio.');
}

// ==================================================
// VALIDAR NÚMERO DE FACTURA OPCIONAL
// ==================================================

if ($tieneNumeroFactura && $numeroFactura === '') {
    exit('Debe ingresar el número de factura.');
}

if (!$tieneNumeroFactura || $numeroFactura === '') {
    $numeroFactura = null;
}

// ==================================================
// VALIDAR FECHA DE FACTURA
// ==================================================

if ($fechaFactura === '') {
    exit('La fecha de factura es obligatoria.');
}

$fechaValidada = DateTime::createFromFormat('!Y-m-d', $fechaFactura);

if (
    !$fechaValidada ||
    $fechaValidada->format('Y-m-d') !== $fechaFactura
) {
    exit('La fecha de factura no es válida.');
}

// ==================================================
// LIMPIAR Y VALIDAR VALOR DE FACTURA
// ==================================================

$valor = str_replace('.', '', $valor);
$valor = str_replace(',', '.', $valor);

if ($valor === '' || !is_numeric($valor)) {
    exit('El valor de la factura no es válido.');
}

$valor = (float) $valor;

if ($valor < 0) {
    exit('El valor de la factura no puede ser negativo.');
}

$valor = number_format($valor, 2, '.', '');

// ==================================================
// INFORMACIÓN TRIBUTARIA
// ==================================================

$tieneIva = isset($_POST['tiene_iva']) ? 1 : 0;
$valorIva = null;

$tieneImpoconsumo = isset($_POST['tiene_impoconsumo']) ? 1 : 0;
$valorImpoconsumo = null;

$tieneRetencion = isset($_POST['tiene_retencion']) ? 1 : 0;
$valorRetencion = null;

// ==================================================
// VALIDAR IVA
// ==================================================

if ($tieneIva) {

    $valorIva = trim($_POST['valor_iva'] ?? '');

    $valorIva = str_replace('.', '', $valorIva);
    $valorIva = str_replace(',', '.', $valorIva);

    if ($valorIva === '' || !is_numeric($valorIva)) {
        exit('Debe ingresar un valor válido para el IVA.');
    }

    $valorIva = (float) $valorIva;

    if ($valorIva < 0) {
        exit('El valor del IVA no puede ser negativo.');
    }

    $valorIva = number_format($valorIva, 2, '.', '');
}

// ==================================================
// VALIDAR IMPOCONSUMO
// ==================================================

if ($tieneImpoconsumo) {

    $valorImpoconsumo = trim($_POST['valor_impoconsumo'] ?? '');

    $valorImpoconsumo = str_replace('.', '', $valorImpoconsumo);
    $valorImpoconsumo = str_replace(',', '.', $valorImpoconsumo);

    if ($valorImpoconsumo === '' || !is_numeric($valorImpoconsumo)) {
        exit('Debe ingresar un valor válido para el Impoconsumo.');
    }

    $valorImpoconsumo = (float) $valorImpoconsumo;

    if ($valorImpoconsumo < 0) {
        exit('El valor del Impoconsumo no puede ser negativo.');
    }

    $valorImpoconsumo = number_format($valorImpoconsumo, 2, '.', '');
}

// ==================================================
// VALIDAR RETENCIÓN
// ==================================================

if ($tieneRetencion) {

    $valorRetencion = trim($_POST['valor_retencion'] ?? '');

    $valorRetencion = str_replace('.', '', $valorRetencion);
    $valorRetencion = str_replace(',', '.', $valorRetencion);

    if ($valorRetencion === '' || !is_numeric($valorRetencion)) {
        exit('Debe ingresar un valor válido para la Retención.');
    }

    $valorRetencion = (float) $valorRetencion;

    if ($valorRetencion < 0) {
        exit('El valor de la Retención no puede ser negativo.');
    }

    $valorRetencion = number_format($valorRetencion, 2, '.', '');
}

// ==================================================
// VERIFICAR QUE EL CONTRATO EXISTA
// ==================================================

$sqlContrato = "
    SELECT id, numero_contrato
    FROM contratos
    WHERE id = ?
";

$stmtContrato = $conexion->prepare($sqlContrato);

if (!$stmtContrato) {
    exit(
        'Error preparando la consulta del contrato: ' .
        $conexion->error
    );
}

$stmtContrato->bind_param('i', $contratoId);
$stmtContrato->execute();

$resultadoContrato = $stmtContrato->get_result();
$datosContrato = $resultadoContrato->fetch_assoc();

$stmtContrato->close();

if (!$datosContrato) {
    exit('El contrato no existe.');
}

$numeroContrato = trim(
    (string) $datosContrato['numero_contrato']
);

// ==================================================
// GUARDAR FACTURA
// ==================================================

$sql = "
    INSERT INTO facturas (
        contrato_id,
        proveedor,
        numero_factura,
        fecha_factura,
        valor,
        tiene_iva,
        valor_iva,
        tiene_impoconsumo,
        valor_impoconsumo,
        tiene_retencion,
        valor_retencion,
        observacion
    )
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
";

$stmt = $conexion->prepare($sql);

if (!$stmt) {
    exit(
        'Error preparando el registro de factura: ' .
        $conexion->error
    );
}

// ==================================================
// ASIGNAR VALORES
// ==================================================

$stmt->bind_param(
    'isssdisisiss',
    $contratoId,
    $proveedor,
    $numeroFactura,
    $fechaFactura,
    $valor,
    $tieneIva,
    $valorIva,
    $tieneImpoconsumo,
    $valorImpoconsumo,
    $tieneRetencion,
    $valorRetencion,
    $observacion
);

// ==================================================
// EJECUTAR
// ==================================================

if (!$stmt->execute()) {

    $error = $stmt->error;

    $stmt->close();

    exit('Error al guardar la factura: ' . $error);
}

$facturaId = $conexion->insert_id;

$stmt->close();

// ==================================================
// GUARDAR SOPORTE
// ==================================================

if (
    isset($_FILES['soporte']) &&
    $_FILES['soporte']['error'] === UPLOAD_ERR_OK
) {

    $archivo = $_FILES['soporte'];

    $nombreOriginal = basename($archivo['name']);

    $tipoArchivo = $archivo['type']
        ?? 'application/octet-stream';

    $extension = strtolower(
        pathinfo($nombreOriginal, PATHINFO_EXTENSION)
    );

    $extensionesPermitidas = [
        'pdf',
        'jpg',
        'jpeg',
        'png'
    ];

    if (!in_array(
        $extension,
        $extensionesPermitidas,
        true
    )) {

        exit(
            'Tipo de archivo no permitido. ' .
            'Use PDF, JPG, JPEG o PNG.'
        );
    }

    // ==================================================
    // VERIFICAR TIPO REAL DEL ARCHIVO
    // ==================================================

    $tiposMimePermitidos = [
        'pdf' => [
            'application/pdf'
        ],
        'jpg' => [
            'image/jpeg'
        ],
        'jpeg' => [
            'image/jpeg'
        ],
        'png' => [
            'image/png'
        ]
    ];

    $finfo = new finfo(FILEINFO_MIME_TYPE);

    $mimeReal = $finfo->file(
        $archivo['tmp_name']
    );

    if (
        !in_array(
            $mimeReal,
            $tiposMimePermitidos[$extension],
            true
        )
    ) {

        exit(
            'El contenido del archivo no corresponde ' .
            'con su extensión.'
        );
    }

    // ==================================================
    // CARPETA DE SOPORTES
    // ==================================================

    $carpeta = __DIR__ .
        '/../../../uploads/soportes_facturas/';

    if (!is_dir($carpeta)) {

        if (
            !mkdir($carpeta, 0775, true) &&
            !is_dir($carpeta)
        ) {

            exit(
                'No fue posible crear la carpeta de soportes.'
            );
        }
    }

    // ==================================================
    // NOMBRE ÚNICO DEL ARCHIVO
    // ==================================================

    $nombreContrato = preg_replace(
        '/[^A-Za-z0-9_-]/',
        '_',
        $numeroContrato
    );

    $numeroFacturaLimpio = $numeroFactura !== null
        ? preg_replace(
            '/[^A-Za-z0-9_-]/',
            '_',
            $numeroFactura
        )
        : 'SinNumero';

    $nombreArchivo =
        'Contrato_' .
        $nombreContrato .
        '_Factura_' .
        $numeroFacturaLimpio .
        '_' .
        $facturaId .
        '.' .
        $extension;

    $rutaArchivo = $carpeta . $nombreArchivo;

    // ==================================================
    // MOVER ARCHIVO
    // ==================================================

    if (
        move_uploaded_file(
            $archivo['tmp_name'],
            $rutaArchivo
        )
    ) {

        $sqlSoporte = "
            INSERT INTO soportes_factura (
                factura_id,
                archivo,
                tipo_archivo
            )
            VALUES (?, ?, ?)
        ";

        $stmtSoporte = $conexion->prepare(
            $sqlSoporte
        );

        if ($stmtSoporte) {

            $stmtSoporte->bind_param(
                'iss',
                $facturaId,
                $nombreArchivo,
                $tipoArchivo
            );

            if (!$stmtSoporte->execute()) {

                $stmtSoporte->close();

                if (file_exists($rutaArchivo)) {
                    unlink($rutaArchivo);
                }

                exit(
                    'La factura se guardó, pero no fue posible ' .
                    'registrar su soporte.'
                );
            }

            $stmtSoporte->close();

        } else {

            if (file_exists($rutaArchivo)) {
                unlink($rutaArchivo);
            }

            exit(
                'La factura se guardó, pero no fue posible ' .
                'preparar el registro del soporte.'
            );
        }

    } else {

        exit(
            'La factura se guardó, pero no fue posible ' .
            'mover el archivo de soporte.'
        );
    }
}

// ==================================================
// REGISTRAR ACTIVIDAD
// ==================================================

$usuarioId = (int) (
    $_SESSION['usuario_id'] ?? 0
);

if ($usuarioId > 0) {

    $modulo = 'facturacion';

    $accion = 'crear';

    $descripcion =
        'Creó la factura #' .
        ($numeroFactura ?? 'Sin número') .
        ' (registro ' .
        $facturaId .
        ') del contrato ' .
        $numeroContrato;

    $ip = $_SERVER['REMOTE_ADDR'] ?? null;

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

    $stmtActividad = $conexion->prepare(
        $sqlActividad
    );

    if ($stmtActividad) {

        $stmtActividad->bind_param(
            'isssis',
            $usuarioId,
            $modulo,
            $accion,
            $descripcion,
            $facturaId,
            $ip
        );

        $stmtActividad->execute();

        $stmtActividad->close();
    }
}

// ==================================================
// VOLVER AL CONTRATO
// ==================================================

header(
    'Location: /inventario/facturacion/ver/' .
    $contratoId
);

exit;

?>
