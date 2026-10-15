<?php

session_start();


// ==========================================================
// VALIDAR SESIÓN
// ==========================================================

if (!isset($_SESSION['usuario_id'])) {
    header('Location: /inventario/login');
    exit;
}


require_once __DIR__ . '/../../config/conexion.php';


// ==========================================================
// VALIDAR MÉTODO
// ==========================================================

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /inventario/permisos');
    exit;
}


// ==========================================================
// DATOS
// ==========================================================

$usuarioId = (int) ($_POST['usuario_id'] ?? 0);

$permisos = $_POST['permisos'] ?? [];


// ==========================================================
// VALIDAR USUARIO
// ==========================================================

if ($usuarioId <= 0 || $usuarioId === 1) {

    header('Location: /inventario/permisos?error=usuario');
    exit;

}


// ==========================================================
// CONSULTAR USUARIO
// ==========================================================

$sqlUsuario = "
    SELECT
        id,
        nombre,
        apellido
    FROM usuarios
    WHERE id = ?
      AND id <> 1
    LIMIT 1
";

$stmtUsuario = $conexion->prepare($sqlUsuario);

if (!$stmtUsuario) {
    header('Location: /inventario/permisos?error=datos');
    exit;
}

$stmtUsuario->bind_param('i', $usuarioId);

$stmtUsuario->execute();

$resultadoUsuario = $stmtUsuario->get_result();

$usuario = $resultadoUsuario->fetch_assoc();

$stmtUsuario->close();


if (!$usuario) {

    header('Location: /inventario/permisos?error=usuario');
    exit;

}


// ==========================================================
// LIMPIAR PERMISOS RECIBIDOS
// ==========================================================

$permisosLimpios = [];

if (is_array($permisos)) {

    foreach ($permisos as $permisoId) {

        $permisoId = (int) $permisoId;

        if ($permisoId > 0) {
            $permisosLimpios[$permisoId] = $permisoId;
        }

    }

}

$permisosLimpios = array_values($permisosLimpios);


// ==========================================================
// INICIAR TRANSACCIÓN
// ==========================================================

$conexion->begin_transaction();


try {


    // ======================================================
    // ELIMINAR PERMISOS ANTERIORES
    // ======================================================

    $sqlEliminar = "
        DELETE FROM usuario_permisos
        WHERE usuario_id = ?
    ";

    $stmtEliminar = $conexion->prepare($sqlEliminar);

    if (!$stmtEliminar) {
        throw new Exception('No se pudo preparar la eliminación.');
    }

    $stmtEliminar->bind_param('i', $usuarioId);

    if (!$stmtEliminar->execute()) {
        throw new Exception('No se pudieron eliminar los permisos anteriores.');
    }

    $stmtEliminar->close();


    // ======================================================
    // INSERTAR NUEVOS PERMISOS
    // ======================================================

    if (!empty($permisosLimpios)) {

        $sqlInsertar = "
            INSERT INTO usuario_permisos
            (
                usuario_id,
                permiso_id
            )
            VALUES (?, ?)
        ";

        $stmtInsertar = $conexion->prepare($sqlInsertar);

        if (!$stmtInsertar) {
            throw new Exception('No se pudo preparar la inserción.');
        }


        foreach ($permisosLimpios as $permisoId) {

            $stmtInsertar->bind_param(
                'ii',
                $usuarioId,
                $permisoId
            );

            if (!$stmtInsertar->execute()) {
                throw new Exception('No se pudo guardar uno de los permisos.');
            }

        }


        $stmtInsertar->close();

    }


    // ======================================================
    // REGISTRAR ACTIVIDAD
    // ======================================================

    $nombreCompleto = trim(
        $usuario['nombre'] . ' ' . $usuario['apellido']
    );

    $descripcion =
        'Actualizó los permisos del usuario "' .
        $nombreCompleto .
        '"';


    $usuarioActualId = (int) $_SESSION['usuario_id'];

    $ip = $_SERVER['REMOTE_ADDR'] ?? null;


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
            'permisos',
            'editar',
            ?,
            ?,
            ?
        )
    ";

    $stmtActividad = $conexion->prepare($sqlActividad);

    if (!$stmtActividad) {
        throw new Exception('No se pudo registrar la actividad.');
    }

    $stmtActividad->bind_param(
        'isis',
        $usuarioActualId,
        $descripcion,
        $usuarioId,
        $ip
    );

    if (!$stmtActividad->execute()) {
        throw new Exception('No se pudo registrar la actividad.');
    }

    $stmtActividad->close();


    // ======================================================
    // CONFIRMAR
    // ======================================================

    $conexion->commit();


    header('Location: /inventario/permisos?mensaje=guardado');
    exit;


} catch (Throwable $e) {


    // ======================================================
    // DESHACER CAMBIOS
    // ======================================================

    $conexion->rollback();


    header('Location: /inventario/permisos?error=datos');
    exit;

}