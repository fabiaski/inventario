<?php

// ==========================================================
// SESIÓN
// ==========================================================

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


// ==========================================================
// EVITAR CACHÉ
// ==========================================================

header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Cache-Control: post-check=0, pre-check=0', false);
header('Pragma: no-cache');
header('Expires: 0');


// ==========================================================
// VALIDAR SESIÓN
// ==========================================================

if (!isset($_SESSION['usuario_id'])) {
    header('Location: /inventario/login?error=sesion');
    exit;
}


// ==========================================================
// SOLO POST
// ==========================================================

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /inventario/perfil');
    exit;
}


// ==========================================================
// CONEXIÓN
// ==========================================================

require_once __DIR__ . '/../../config/conexion.php';


// ==========================================================
// ID DEL USUARIO
// ==========================================================

$usuarioId = (int) $_SESSION['usuario_id'];

if ($usuarioId <= 0) {
    header('Location: /inventario/login?error=sesion');
    exit;
}


// ==========================================================
// DATOS RECIBIDOS
// ==========================================================

$nombre = trim($_POST['nombre'] ?? '');
$apellido = trim($_POST['apellido'] ?? '');

$passwordActual = $_POST['password_actual'] ?? '';
$passwordNueva = $_POST['password_nueva'] ?? '';
$passwordConfirmar = $_POST['password_confirmar'] ?? '';


// ==========================================================
// VALIDAR NOMBRE Y APELLIDO
// ==========================================================

if ($nombre === '' || $apellido === '') {
    header('Location: /inventario/perfil?error=datos');
    exit;
}


// ==========================================================
// CONSULTAR USUARIO ACTUAL
// ==========================================================

$sqlUsuario = "
    SELECT
        id,
        nombre,
        apellido,
        password,
        foto
    FROM usuarios
    WHERE id = ?
    LIMIT 1
";

$stmtUsuario = $conexion->prepare($sqlUsuario);

if (!$stmtUsuario) {
    header('Location: /inventario/perfil?error=actualizar');
    exit;
}

$stmtUsuario->bind_param('i', $usuarioId);
$stmtUsuario->execute();

$resultadoUsuario = $stmtUsuario->get_result();
$usuario = $resultadoUsuario->fetch_assoc();

$stmtUsuario->close();


if (!$usuario) {
    header('Location: /inventario/login?error=sesion');
    exit;
}


// ==========================================================
// VARIABLES DE FOTO
// ==========================================================

$fotoAnterior = $usuario['foto'] ?? '';
$nuevaFotoNombre = null;
$guardarNuevaFoto = false;


// ==========================================================
// VALIDAR FOTO SI FUE SELECCIONADA
// ==========================================================

if (
    isset($_FILES['foto']) &&
    isset($_FILES['foto']['error']) &&
    $_FILES['foto']['error'] !== UPLOAD_ERR_NO_FILE
) {

    // ------------------------------------------------------
    // ERROR DE SUBIDA
    // ------------------------------------------------------

    if ($_FILES['foto']['error'] !== UPLOAD_ERR_OK) {

        if ($_FILES['foto']['error'] === UPLOAD_ERR_INI_SIZE ||
            $_FILES['foto']['error'] === UPLOAD_ERR_FORM_SIZE) {

            header('Location: /inventario/perfil?error=tamano');
            exit;
        }

        header('Location: /inventario/perfil?error=foto');
        exit;
    }


    // ------------------------------------------------------
    // TAMAÑO MÁXIMO: 5 MB
    // ------------------------------------------------------

    $tamanoMaximo = 5 * 1024 * 1024;

    if ((int) $_FILES['foto']['size'] > $tamanoMaximo) {
        header('Location: /inventario/perfil?error=tamano');
        exit;
    }


    // ------------------------------------------------------
    // VALIDAR MIME REAL
    // ------------------------------------------------------

    $finfo = new finfo(FILEINFO_MIME_TYPE);

    $mime = $finfo->file($_FILES['foto']['tmp_name']);

    $tiposPermitidos = [
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/webp' => 'webp'
    ];

    if (!isset($tiposPermitidos[$mime])) {
        header('Location: /inventario/perfil?error=foto');
        exit;
    }


    // ------------------------------------------------------
    // VALIDAR QUE REALMENTE SEA UNA IMAGEN
    // ------------------------------------------------------

    $imagenValida = getimagesize($_FILES['foto']['tmp_name']);

    if ($imagenValida === false) {
        header('Location: /inventario/perfil?error=foto');
        exit;
    }


    // ------------------------------------------------------
    // CARPETA DE FOTOS
    // ------------------------------------------------------

    $carpetaFotos = __DIR__ . '/../../assets/images/usuarios/';

    if (!is_dir($carpetaFotos)) {

        if (!mkdir($carpetaFotos, 0755, true)) {
            header('Location: /inventario/perfil?error=actualizar');
            exit;
        }
    }


    // ------------------------------------------------------
    // GENERAR NOMBRE
    // ------------------------------------------------------

    $extension = $tiposPermitidos[$mime];

    $nuevaFotoNombre = 'face-usuario-' . $usuarioId . '.' . $extension;

    $rutaNuevaFoto = $carpetaFotos . $nuevaFotoNombre;


    // ------------------------------------------------------
    // ELIMINAR OTRAS EXTENSIONES ANTERIORES
    // ------------------------------------------------------

    $extensionesAnteriores = [
        'jpg',
        'jpeg',
        'png',
        'webp'
    ];

    foreach ($extensionesAnteriores as $extensionAnterior) {

        $archivoAnterior = $carpetaFotos .
            'face-usuario-' .
            $usuarioId .
            '.' .
            $extensionAnterior;

        if (
            file_exists($archivoAnterior) &&
            realpath($archivoAnterior) !== realpath($rutaNuevaFoto)
        ) {
            @unlink($archivoAnterior);
        }
    }


    // ------------------------------------------------------
    // MOVER FOTO
    // ------------------------------------------------------

    if (!move_uploaded_file(
        $_FILES['foto']['tmp_name'],
        $rutaNuevaFoto
    )) {

        header('Location: /inventario/perfil?error=actualizar');
        exit;
    }

    $guardarNuevaFoto = true;
}


// ==========================================================
// CAMBIO DE CONTRASEÑA
// ==========================================================

$cambiarPassword = false;

$hayPassword = (
    $passwordActual !== '' ||
    $passwordNueva !== '' ||
    $passwordConfirmar !== ''
);


// ----------------------------------------------------------
// SI SE QUIERE CAMBIAR CONTRASEÑA
// ----------------------------------------------------------

if ($hayPassword) {

    // ------------------------------------------------------
    // TODOS LOS CAMPOS SON OBLIGATORIOS
    // ------------------------------------------------------

    if (
        $passwordActual === '' ||
        $passwordNueva === '' ||
        $passwordConfirmar === ''
    ) {

        // Si acabamos de subir una foto, eliminarla porque
        // la actualización completa no se realizará.
        if ($guardarNuevaFoto && $nuevaFotoNombre !== null) {

            $rutaFotoNueva = __DIR__ .
                '/../../assets/images/usuarios/' .
                $nuevaFotoNombre;

            if (file_exists($rutaFotoNueva)) {
                @unlink($rutaFotoNueva);
            }
        }

        header('Location: /inventario/perfil?error=password');
        exit;
    }


    // ------------------------------------------------------
    // MÍNIMO 6 CARACTERES
    // ------------------------------------------------------

    if (strlen($passwordNueva) < 6) {

        if ($guardarNuevaFoto && $nuevaFotoNombre !== null) {

            $rutaFotoNueva = __DIR__ .
                '/../../assets/images/usuarios/' .
                $nuevaFotoNombre;

            if (file_exists($rutaFotoNueva)) {
                @unlink($rutaFotoNueva);
            }
        }

        header('Location: /inventario/perfil?error=password_corta');
        exit;
    }


    // ------------------------------------------------------
    // CONFIRMAR CONTRASEÑA
    // ------------------------------------------------------

    if ($passwordNueva !== $passwordConfirmar) {

        if ($guardarNuevaFoto && $nuevaFotoNombre !== null) {

            $rutaFotoNueva = __DIR__ .
                '/../../assets/images/usuarios/' .
                $nuevaFotoNombre;

            if (file_exists($rutaFotoNueva)) {
                @unlink($rutaFotoNueva);
            }
        }

        header('Location: /inventario/perfil?error=password_diferente');
        exit;
    }


    // ------------------------------------------------------
    // VERIFICAR CONTRASEÑA ACTUAL
    // ------------------------------------------------------

    if (!password_verify(
        $passwordActual,
        $usuario['password']
    )) {

        if ($guardarNuevaFoto && $nuevaFotoNombre !== null) {

            $rutaFotoNueva = __DIR__ .
                '/../../assets/images/usuarios/' .
                $nuevaFotoNombre;

            if (file_exists($rutaFotoNueva)) {
                @unlink($rutaFotoNueva);
            }
        }

        header('Location: /inventario/perfil?error=password_actual');
        exit;
    }


    $cambiarPassword = true;
}


// ==========================================================
// PREPARAR ACTUALIZACIÓN
// ==========================================================

if ($guardarNuevaFoto && $cambiarPassword) {

    // ------------------------------------------------------
    // NOMBRE + APELLIDO + PASSWORD + FOTO
    // ------------------------------------------------------

    $passwordHash = password_hash(
        $passwordNueva,
        PASSWORD_DEFAULT
    );

    $sqlUpdate = "
        UPDATE usuarios
        SET
            nombre = ?,
            apellido = ?,
            password = ?,
            foto = ?
        WHERE id = ?
    ";

    $stmtUpdate = $conexion->prepare($sqlUpdate);

    if (!$stmtUpdate) {

        // Eliminar foto nueva si falla la consulta
        $rutaFotoNueva = __DIR__ .
            '/../../assets/images/usuarios/' .
            $nuevaFotoNombre;

        if (file_exists($rutaFotoNueva)) {
            @unlink($rutaFotoNueva);
        }

        header('Location: /inventario/perfil?error=actualizar');
        exit;
    }

    $stmtUpdate->bind_param(
        'ssssi',
        $nombre,
        $apellido,
        $passwordHash,
        $nuevaFotoNombre,
        $usuarioId
    );

    $actualizado = $stmtUpdate->execute();

    $stmtUpdate->close();


} elseif ($guardarNuevaFoto) {

    // ------------------------------------------------------
    // NOMBRE + APELLIDO + FOTO
    // ------------------------------------------------------

    $sqlUpdate = "
        UPDATE usuarios
        SET
            nombre = ?,
            apellido = ?,
            foto = ?
        WHERE id = ?
    ";

    $stmtUpdate = $conexion->prepare($sqlUpdate);

    if (!$stmtUpdate) {

        $rutaFotoNueva = __DIR__ .
            '/../../assets/images/usuarios/' .
            $nuevaFotoNombre;

        if (file_exists($rutaFotoNueva)) {
            @unlink($rutaFotoNueva);
        }

        header('Location: /inventario/perfil?error=actualizar');
        exit;
    }

    $stmtUpdate->bind_param(
        'sssi',
        $nombre,
        $apellido,
        $nuevaFotoNombre,
        $usuarioId
    );

    $actualizado = $stmtUpdate->execute();

    $stmtUpdate->close();


} elseif ($cambiarPassword) {

    // ------------------------------------------------------
    // NOMBRE + APELLIDO + PASSWORD
    // ------------------------------------------------------

    $passwordHash = password_hash(
        $passwordNueva,
        PASSWORD_DEFAULT
    );

    $sqlUpdate = "
        UPDATE usuarios
        SET
            nombre = ?,
            apellido = ?,
            password = ?
        WHERE id = ?
    ";

    $stmtUpdate = $conexion->prepare($sqlUpdate);

    if (!$stmtUpdate) {
        header('Location: /inventario/perfil?error=actualizar');
        exit;
    }

    $stmtUpdate->bind_param(
        'sssi',
        $nombre,
        $apellido,
        $passwordHash,
        $usuarioId
    );

    $actualizado = $stmtUpdate->execute();

    $stmtUpdate->close();


} else {

    // ------------------------------------------------------
    // SOLO NOMBRE + APELLIDO
    // ------------------------------------------------------

    $sqlUpdate = "
        UPDATE usuarios
        SET
            nombre = ?,
            apellido = ?
        WHERE id = ?
    ";

    $stmtUpdate = $conexion->prepare($sqlUpdate);

    if (!$stmtUpdate) {
        header('Location: /inventario/perfil?error=actualizar');
        exit;
    }

    $stmtUpdate->bind_param(
        'ssi',
        $nombre,
        $apellido,
        $usuarioId
    );

    $actualizado = $stmtUpdate->execute();

    $stmtUpdate->close();
}


// ==========================================================
// VALIDAR ACTUALIZACIÓN
// ==========================================================

if (!$actualizado) {

    // Si la BD falló y hay foto nueva, eliminarla
    if ($guardarNuevaFoto && $nuevaFotoNombre !== null) {

        $rutaFotoNueva = __DIR__ .
            '/../../assets/images/usuarios/' .
            $nuevaFotoNombre;

        if (file_exists($rutaFotoNueva)) {
            @unlink($rutaFotoNueva);
        }
    }

    header('Location: /inventario/perfil?error=actualizar');
    exit;
}


// ==========================================================
// ELIMINAR FOTO ANTERIOR
// ==========================================================

if (
    $guardarNuevaFoto &&
    !empty($fotoAnterior)
) {

    $carpetaFotos = __DIR__ .
        '/../../assets/images/usuarios/';

    $rutaFotoAnterior = $carpetaFotos . basename($fotoAnterior);

    if (
        file_exists($rutaFotoAnterior) &&
        $fotoAnterior !== $nuevaFotoNombre
    ) {
        @unlink($rutaFotoAnterior);
    }
}


// ==========================================================
// ACTUALIZAR SESIÓN
// ==========================================================

$_SESSION['usuario_nombre'] = $nombre;
$_SESSION['usuario_apellido'] = $apellido;


// ==========================================================
// ACTUALIZAR FOTO EN SESIÓN
// ==========================================================

if ($guardarNuevaFoto) {
    $_SESSION['usuario_foto'] = $nuevaFotoNombre;
} elseif (!isset($_SESSION['usuario_foto'])) {
    $_SESSION['usuario_foto'] = $fotoAnterior;
}


// ==========================================================
// SI CAMBIÓ CONTRASEÑA, REGENERAR SESIÓN
// ==========================================================

if ($cambiarPassword) {
    session_regenerate_id(true);
}


// ==========================================================
// REGISTRAR ACTIVIDAD
// ==========================================================

$accionActividad = $cambiarPassword
    ? 'actualizar_perfil_password'
    : 'actualizar_perfil';

$descripcionActividad = $guardarNuevaFoto
    ? 'Actualizó sus datos de perfil y foto.'
    : 'Actualizó sus datos de perfil.';

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
    (
        ?,
        'perfil',
        ?,
        ?,
        ?,
        ?
    )
";

$stmtActividad = $conexion->prepare($sqlActividad);

if ($stmtActividad) {

    $ip = $_SERVER['REMOTE_ADDR'] ?? '';

    $stmtActividad->bind_param(
        'issis',
        $usuarioId,
        $accionActividad,
        $descripcionActividad,
        $usuarioId,
        $ip
    );

    $stmtActividad->execute();
    $stmtActividad->close();
}


// ==========================================================
// FINALIZAR
// ==========================================================

header('Location: /inventario/perfil?mensaje=actualizado');
exit;