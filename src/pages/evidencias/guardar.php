<?php

session_start();

require_once __DIR__ . '/../../config/conexion.php';

// ==========================================================
// FUNCIONES AUXILIARES
// ==========================================================

function redirigirGaleria(int $contratoId): never
{
    header(
        'Location: /inventario/evidencias/galeria?contrato_id='
        . $contratoId
    );
    exit;
}

function mensajeEvidencias(
    string $mensaje,
    string $tipo = 'danger'
): void {
    $_SESSION['mensaje_evidencias'] = $mensaje;
    $_SESSION['tipo_mensaje_evidencias'] = $tipo;
}

function limpiarNombreArchivo(string $nombre): string
{
    $nombre = trim($nombre);
    $nombre = preg_replace('/[^a-zA-Z0-9_-]/', '_', $nombre);

    return $nombre !== '' ? $nombre : 'SIN_NUMERO';
}

/**
 * Busca el siguiente consecutivo libre para un contrato.
 * Comprueba todas las extensiones para evitar duplicados.
 */
function siguienteNumeroEvidencia(
    string $directorio,
    string $numeroContrato
): int {
    $maximo = 0;

    $patron = $directorio
        . DIRECTORY_SEPARATOR
        . 'Contrato_' . $numeroContrato
        . '_Evidencia_*';

    $archivos = glob($patron);

    if ($archivos !== false) {
        foreach ($archivos as $ruta) {
            $nombre = basename($ruta);

            if (
                preg_match(
                    '/^Contrato_.*_Evidencia_(\d+)\.[a-zA-Z0-9]+$/',
                    $nombre,
                    $coincidencias
                )
            ) {
                $numero = (int) $coincidencias[1];
                $maximo = max($maximo, $numero);
            }
        }
    }

    return $maximo + 1;
}

// ==========================================================
// VALIDAR SESIÓN
// ==========================================================

if (empty($_SESSION['usuario_id'])) {
    header('Location: /inventario/login');
    exit;
}

// ==========================================================
// VALIDAR MÉTODO POST
// ==========================================================

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /inventario/evidencias');
    exit;
}

// ==========================================================
// VALIDAR CONTRATO
// ==========================================================

$contratoId = filter_input(
    INPUT_POST,
    'contrato_id',
    FILTER_VALIDATE_INT
);

if (!$contratoId || $contratoId <= 0) {
    mensajeEvidencias('El contrato seleccionado no es válido.');
    header('Location: /inventario/evidencias');
    exit;
}

try {
    $stmtContrato = $conexion->prepare(
        'SELECT id, numero_contrato
         FROM contratos
         WHERE id = ?
         LIMIT 1'
    );

    if (!$stmtContrato) {
        throw new RuntimeException(
            'No se pudo preparar la consulta del contrato.'
        );
    }

    $stmtContrato->bind_param('i', $contratoId);
    $stmtContrato->execute();

    $contrato = $stmtContrato->get_result()->fetch_assoc();
    $stmtContrato->close();

    if (!$contrato) {
        mensajeEvidencias('No se encontró el contrato seleccionado.');
        header('Location: /inventario/evidencias');
        exit;
    }

    // ======================================================
    // VALIDAR ARCHIVOS RECIBIDOS
    // ======================================================

    if (
        !isset($_FILES['fotografias']) ||
        !is_array($_FILES['fotografias']['name'])
    ) {
        mensajeEvidencias('Selecciona al menos una fotografía.');
        redirigirGaleria($contratoId);
    }

    $archivos = $_FILES['fotografias'];
    $cantidadArchivos = count($archivos['name']);

    if ($cantidadArchivos === 0) {
        mensajeEvidencias('Selecciona al menos una fotografía.');
        redirigirGaleria($contratoId);
    }

    if ($cantidadArchivos > 30) {
        mensajeEvidencias(
            'Puedes subir un máximo de 30 fotografías por envío.'
        );
        redirigirGaleria($contratoId);
    }

    // ======================================================
    // CARPETAS DE DOCUMENTOS
    // ======================================================

    $baseDocumentos = 'C:\\Users\\loshe\\Documents\\MYG';

    if (!is_dir($baseDocumentos)) {
        throw new RuntimeException(
            'No existe la carpeta principal: ' . $baseDocumentos
        );
    }

    if (!is_writable($baseDocumentos)) {
        throw new RuntimeException(
            'PHP no tiene permiso para escribir en: ' . $baseDocumentos
        );
    }

    $directorioBase = $baseDocumentos
        . DIRECTORY_SEPARATOR
        . 'evidencias_contratos';

    if (
        !is_dir($directorioBase) &&
        !mkdir($directorioBase, 0775, true) &&
        !is_dir($directorioBase)
    ) {
        throw new RuntimeException(
            'No se pudo crear la carpeta de evidencias.'
        );
    }

    $numeroContrato = limpiarNombreArchivo(
        (string) $contrato['numero_contrato']
    );

    $nombreCarpeta = 'Contrato_' . $numeroContrato;

    $directorioContrato = $directorioBase
        . DIRECTORY_SEPARATOR
        . $nombreCarpeta;

    if (
        !is_dir($directorioContrato) &&
        !mkdir($directorioContrato, 0775, true) &&
        !is_dir($directorioContrato)
    ) {
        throw new RuntimeException(
            'No se pudo crear la carpeta del contrato.'
        );
    }

    if (!is_writable($directorioContrato)) {
        throw new RuntimeException(
            'PHP no tiene permiso para escribir en la carpeta del contrato.'
        );
    }

    // ======================================================
    // VALIDAR FORMATOS DE IMAGEN
    // ======================================================

    if (!extension_loaded('fileinfo')) {
        throw new RuntimeException(
            'La extensión fileinfo de PHP no está habilitada.'
        );
    }

    $tiposPermitidos = [
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/webp' => 'webp',
        'image/gif'  => 'gif'
    ];

    $finfo = new finfo(FILEINFO_MIME_TYPE);

    // ======================================================
    // PREPARAR INSERCIÓN EN LA BASE DE DATOS
    // ======================================================

    $stmtInsertar = $conexion->prepare(
        'INSERT INTO evidencias_contratos
            (contrato_id, nombre_archivo)
         VALUES (?, ?)'
    );

    if (!$stmtInsertar) {
        throw new RuntimeException(
            'No se pudo preparar el registro de la fotografía.'
        );
    }

    $stmtInsertar->bind_param(
        'is',
        $contratoId,
        $nombreArchivo
    );

    // ======================================================
    // INICIAR CONSECUTIVO ÚNICO
    // ======================================================

    $numeroEvidencia = siguienteNumeroEvidencia(
        $directorioContrato,
        $numeroContrato
    );

    // ======================================================
    // PROCESAR FOTOGRAFÍAS
    // ======================================================

    $guardadas = 0;
    $errores = [];

    for ($i = 0; $i < $cantidadArchivos; $i++) {
        $nombreOriginal = (string) ($archivos['name'][$i] ?? '');
        $archivoTemporal = (string) ($archivos['tmp_name'][$i] ?? '');
        $errorSubida = (int) ($archivos['error'][$i] ?? UPLOAD_ERR_NO_FILE);
        $tamano = (int) ($archivos['size'][$i] ?? 0);

        if (
            $errorSubida === UPLOAD_ERR_NO_FILE &&
            $nombreOriginal === ''
        ) {
            continue;
        }

        if ($errorSubida !== UPLOAD_ERR_OK) {
            $errores[] = $nombreOriginal
                . ': error al subir el archivo (código '
                . $errorSubida . ').';
            continue;
        }

        if (
            $archivoTemporal === '' ||
            !is_uploaded_file($archivoTemporal)
        ) {
            $errores[] = $nombreOriginal
                . ': el archivo temporal no es válido.';
            continue;
        }

        // Máximo 10 MB por fotografía.
        if ($tamano <= 0 || $tamano > 10 * 1024 * 1024) {
            $errores[] = $nombreOriginal
                . ': debe pesar como máximo 10 MB.';
            continue;
        }

        $mime = $finfo->file($archivoTemporal);

        if (!isset($tiposPermitidos[$mime])) {
            $errores[] = $nombreOriginal
                . ': formato no permitido. Usa JPG, PNG, WEBP o GIF.';
            continue;
        }

        $extension = $tiposPermitidos[$mime];

        // ==================================================
        // GENERAR NOMBRE ÚNICO ENTRE TODAS LAS EXTENSIONES
        // ==================================================

        do {
            $nombreArchivo = 'Contrato_'
                . $numeroContrato
                . '_Evidencia_'
                . str_pad(
                    (string) $numeroEvidencia,
                    3,
                    '0',
                    STR_PAD_LEFT
                )
                . '.'
                . $extension;

            $rutaDestino = $directorioContrato
                . DIRECTORY_SEPARATOR
                . $nombreArchivo;

            $numeroEvidencia++;
        } while (file_exists($rutaDestino));

        // Guardar fotografía en la carpeta del contrato.
        if (!move_uploaded_file($archivoTemporal, $rutaDestino)) {
            $errores[] = $nombreOriginal
                . ': no se pudo guardar en la carpeta del contrato.';
            continue;
        }

        // Registrar fotografía en MySQL.
        if (!$stmtInsertar->execute()) {
            if (is_file($rutaDestino)) {
                unlink($rutaDestino);
            }

            $errores[] = $nombreOriginal
                . ': no se pudo registrar en la base de datos.';
            continue;
        }

        $guardadas++;
    }

    $stmtInsertar->close();

    // ======================================================
    // RESULTADO
    // ======================================================

    if ($guardadas > 0 && empty($errores)) {
        mensajeEvidencias(
            'Se guardaron correctamente ' . $guardadas
            . ' fotografía(s).',
            'success'
        );
    } elseif ($guardadas > 0) {
        mensajeEvidencias(
            'Se guardaron ' . $guardadas
            . ' fotografía(s). Algunos archivos presentaron errores: '
            . implode(' | ', $errores),
            'warning'
        );
    } else {
        $mensajeError = empty($errores)
            ? 'No se guardó ninguna fotografía. Selecciona archivos válidos.'
            : implode(' | ', $errores);

        mensajeEvidencias($mensajeError, 'danger');
    }

    redirigirGaleria($contratoId);

} catch (Throwable $e) {
    error_log(
        '[EVIDENCIAS] Error al guardar fotografías: '
        . $e->getMessage()
    );

    mensajeEvidencias(
        'No se pudieron guardar las fotografías. Detalle: '
        . $e->getMessage(),
        'danger'
    );

    redirigirGaleria($contratoId);
}
