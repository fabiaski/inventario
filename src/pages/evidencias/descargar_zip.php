
<?php

ob_start();

require_once __DIR__ . '/../../config/conexion.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ==========================================================
// VALIDAR SESIÓN
// ==========================================================

if (empty($_SESSION['usuario_id'])) {
    ob_end_clean();
    header('Location: /inventario/login');
    exit;
}

// ==========================================================
// VALIDAR CONTRATO
// ==========================================================

$contratoId = filter_input(INPUT_GET, 'contrato_id', FILTER_VALIDATE_INT);

if (!$contratoId || $contratoId <= 0) {
    http_response_code(400);
    exit('El contrato seleccionado no es válido.');
}

$archivoTemporalZip = null;
$zipAbierto = false;

try {

    // ======================================================
    // CONSULTAR CONTRATO
    // ======================================================

    $stmt = $conexion->prepare(
        'SELECT id, numero_contrato
         FROM contratos
         WHERE id = ?
         LIMIT 1'
    );

    if (!$stmt) {
        throw new RuntimeException('No se pudo consultar el contrato.');
    }

    $stmt->bind_param('i', $contratoId);
    $stmt->execute();

    $contrato = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$contrato) {
        throw new RuntimeException('No se encontró el contrato.');
    }

    $numeroContrato = trim((string) $contrato['numero_contrato']);

    if ($numeroContrato === '') {
        $numeroContrato = 'SIN_NUMERO';
    }

    // Nombre seguro para el archivo descargado.
    $numeroSeguro = preg_replace(
        '/[^a-zA-Z0-9_-]/',
        '_',
        $numeroContrato
    );

    if (!$numeroSeguro) {
        $numeroSeguro = 'SIN_NUMERO';
    }

    // ======================================================
    // LOCALIZAR LA CARPETA DE DOCUMENTOS
    // ======================================================

    $rutasPosibles = [
        'C:\\Users\\loshe\\Documentos\\myg',
        'C:\\Users\\loshe\\Documents\\myg',
        'C:\\Users\\USUARIO\\Documents\\myg',
        'C:\\Users\\USUARIO\\Documentos\\myg'
    ];

    $baseDocumentos = null;

    foreach ($rutasPosibles as $ruta) {
        if (is_dir($ruta)) {
            $baseDocumentos = $ruta;
            break;
        }
    }

    if ($baseDocumentos === null) {
        throw new RuntimeException(
            'No se encontró la carpeta de documentos. '
            . 'Verifica la ruta configurada en el servidor.'
        );
    }

    $directorioEvidencias = $baseDocumentos
        . DIRECTORY_SEPARATOR . 'evidencias_contratos';

    $directorioContrato = $directorioEvidencias
        . DIRECTORY_SEPARATOR . 'Contrato_' . $numeroSeguro;

    if (!is_dir($directorioContrato)) {
        throw new RuntimeException(
            'No se encontró la carpeta de evidencias del contrato: '
            . 'Contrato_' . $numeroSeguro
        );
    }

    // ======================================================
    // CONSULTAR EVIDENCIAS REGISTRADAS
    // ======================================================

    $stmt = $conexion->prepare(
        'SELECT nombre_archivo
         FROM evidencias_contratos
         WHERE contrato_id = ?
         ORDER BY id ASC'
    );

    if (!$stmt) {
        throw new RuntimeException(
            'No se pudo consultar la base de datos.'
        );
    }

    $stmt->bind_param('i', $contratoId);
    $stmt->execute();

    $resultado = $stmt->get_result();
    $fotografias = [];

    while ($fila = $resultado->fetch_assoc()) {

        $nombre = (string) $fila['nombre_archivo'];

        // Impedir nombres con rutas.
        if (
            $nombre === '' ||
            basename($nombre) !== $nombre ||
            strpos($nombre, '/') !== false ||
            strpos($nombre, '\\') !== false
        ) {
            continue;
        }

        $rutaArchivo = $directorioContrato
            . DIRECTORY_SEPARATOR . $nombre;

        if (
            is_file($rutaArchivo) &&
            is_readable($rutaArchivo) &&
            filesize($rutaArchivo) > 0
        ) {
            $fotografias[] = [
                'nombre' => $nombre,
                'ruta' => $rutaArchivo
            ];
        }
    }

    $stmt->close();

    if (count($fotografias) === 0) {
        throw new RuntimeException(
            'No se encontraron fotografías disponibles. '
            . 'Comprueba la carpeta y los nombres registrados en la base de datos.'
        );
    }

    // ======================================================
    // VERIFICAR EXTENSIÓN ZIP
    // ======================================================

    if (!class_exists('ZipArchive')) {
        throw new RuntimeException(
            'La extensión ZIP de PHP no está habilitada.'
        );
    }

    // ======================================================
    // CREAR ARCHIVO TEMPORAL
    // ======================================================

    $archivoTemporalZip = tempnam(
        sys_get_temp_dir(),
        'evidencias_'
    );

    if ($archivoTemporalZip === false) {
        throw new RuntimeException(
            'No se pudo crear el archivo temporal.'
        );
    }

    // ======================================================
    // COMPRIMIR EVIDENCIAS
    // ======================================================

    $zip = new ZipArchive();

    $resultadoZip = $zip->open(
        $archivoTemporalZip,
        ZipArchive::CREATE | ZipArchive::OVERWRITE
    );

    if ($resultadoZip !== true) {
        throw new RuntimeException(
            'No se pudo crear el ZIP. Código: ' . $resultadoZip
        );
    }

    $zipAbierto = true;
    $totalAgregadas = 0;

    foreach ($fotografias as $fotografia) {

        if (!$zip->addFile(
            $fotografia['ruta'],
            $fotografia['nombre']
        )) {
            throw new RuntimeException(
                'No se pudo agregar el archivo: '
                . $fotografia['nombre']
            );
        }

        $totalAgregadas++;
    }

    if (!$zip->close()) {
        $zipAbierto = false;

        throw new RuntimeException(
            'No se pudo finalizar la compresión ZIP.'
        );
    }

    $zipAbierto = false;

    // ======================================================
    // VERIFICAR INTEGRIDAD DEL ZIP
    // ======================================================

    clearstatcache(true, $archivoTemporalZip);

    if (
        !is_file($archivoTemporalZip) ||
        filesize($archivoTemporalZip) <= 0
    ) {
        throw new RuntimeException(
            'El ZIP está vacío o no se generó correctamente.'
        );
    }

    // Abrir de nuevo el ZIP y comprobar su contenido.
    $zipVerificacion = new ZipArchive();

    $resultadoVerificacion = $zipVerificacion->open(
        $archivoTemporalZip,
        ZipArchive::CHECKCONS
    );

    if ($resultadoVerificacion !== true) {
        throw new RuntimeException(
            'El archivo ZIP generado no superó la verificación de integridad.'
        );
    }

    $cantidadEnZip = $zipVerificacion->numFiles;
    $zipVerificacion->close();

    if ($cantidadEnZip !== $totalAgregadas) {
        throw new RuntimeException(
            'El número de archivos del ZIP no coincide con el esperado.'
        );
    }

    // ======================================================
    // ENVIAR DESCARGA SIN CONTENIDO ADICIONAL
    // ======================================================

    $nombreZip = 'Evidencias_Contrato_' . $numeroSeguro . '.zip';
    $tamanoZip = filesize($archivoTemporalZip);

    // Evitar que avisos o espacios de los archivos incluidos
    // se mezclen con los bytes del ZIP.
    while (ob_get_level() > 0) {
        ob_end_clean();
    }

    if (headers_sent()) {
        throw new RuntimeException(
            'Ya se enviaron encabezados antes de la descarga.'
        );
    }

    header('Content-Type: application/zip');
    header(
        'Content-Disposition: attachment; filename="'
        . $nombreZip . '"'
    );
    header('Content-Length: ' . $tamanoZip);
    header('X-Content-Type-Options: nosniff');
    header('Cache-Control: private, no-store, max-age=0');

    $bytesEnviados = readfile($archivoTemporalZip);

    @unlink($archivoTemporalZip);
    $archivoTemporalZip = null;

    exit;

} catch (Throwable $e) {

    if (isset($zipAbierto) && $zipAbierto && isset($zip)) {
        @$zip->close();
    }

    if (
        $archivoTemporalZip !== null &&
        is_file($archivoTemporalZip)
    ) {
        @unlink($archivoTemporalZip);
    }

    error_log(
        '[EVIDENCIAS ZIP] Contrato ' . $contratoId
        . ': ' . $e->getMessage()
    );

    while (ob_get_level() > 0) {
        ob_end_clean();
    }

    if (session_status() === PHP_SESSION_ACTIVE) {
        $_SESSION['mensaje_evidencias'] =
            'No se pudo descargar el ZIP. ' . $e->getMessage();

        $_SESSION['tipo_mensaje_evidencias'] = 'danger';
    }

    header(
        'Location: /inventario/evidencias/galeria?contrato_id='
        . $contratoId
    );
    exit;
}
