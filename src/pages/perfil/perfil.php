<?php

// ==========================================================
// SESIÓN
// ==========================================================

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


// ==========================================================
// VALIDAR SESIÓN
// ==========================================================

if (!isset($_SESSION['usuario_id'])) {
    header('Location: /inventario/login?error=sesion');
    exit;
}


// ==========================================================
// EVITAR CACHÉ
// ==========================================================

header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Cache-Control: post-check=0, pre-check=0', false);
header('Pragma: no-cache');
header('Expires: 0');


// ==========================================================
// CONEXIÓN
// ==========================================================

require_once __DIR__ . '/../../config/conexion.php';


// ==========================================================
// ID DEL USUARIO
// ==========================================================

$usuarioId = (int) $_SESSION['usuario_id'];

if ($usuarioId <= 0) {
    session_destroy();

    header('Location: /inventario/login?error=sesion');
    exit;
}


// ==========================================================
// CONSULTAR USUARIO
// ==========================================================

$sql = "
    SELECT
        id,
        nombre,
        apellido,
        usuario,
        foto
    FROM usuarios
    WHERE id = ?
    LIMIT 1
";

$stmt = $conexion->prepare($sql);

if (!$stmt) {
    exit('Error al preparar la consulta.');
}

$stmt->bind_param('i', $usuarioId);
$stmt->execute();

$resultado = $stmt->get_result();
$usuario = $resultado->fetch_assoc();

$stmt->close();


// ==========================================================
// VALIDAR USUARIO
// ==========================================================

if (!$usuario) {

    $_SESSION = [];

    if (ini_get('session.use_cookies')) {

        $params = session_get_cookie_params();

        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params['path'],
            $params['domain'],
            $params['secure'],
            $params['httponly']
        );
    }

    session_destroy();

    header('Location: /inventario/login?error=sesion');
    exit;
}


// ==========================================================
// DATOS
// ==========================================================

$nombre = $usuario['nombre'] ?? '';
$apellido = $usuario['apellido'] ?? '';
$usuarioLogin = $usuario['usuario'] ?? '';
$foto = $usuario['foto'] ?? '';


// ==========================================================
// FOTO DE PERFIL
// ==========================================================

// Foto por defecto
$fotoPerfil = '/inventario/src/assets/images/faces/face.jpg';

// Si existe una foto personalizada, usarla
if (!empty($foto)) {

    $rutaFisicaFoto = __DIR__ . '/../../assets/images/usuarios/' . $foto;

    if (file_exists($rutaFisicaFoto)) {
        $fotoPerfil = '/inventario/src/assets/images/usuarios/' . rawurlencode($foto);
    }
}


// ==========================================================
// MENSAJES
// ==========================================================

$mensaje = $_GET['mensaje'] ?? '';
$error = $_GET['error'] ?? '';


// ==========================================================
// INCLUIR DISEÑO
// ==========================================================

require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/navbar.php';
require_once __DIR__ . '/../../includes/sidebar.php';

?>

<!-- ==========================================================
     CONTENIDO
========================================================== -->

<div class="main-panel">
    <div class="content-wrapper">

        <!-- ENCABEZADO -->

        <div class="row">
            <div class="col-md-12 grid-margin">

                <div class="d-flex justify-content-between align-items-center">

                    <div>
                        <h3 class="font-weight-bold mb-1">
                            Mi perfil
                        </h3>

                        <p class="text-muted mb-0">
                            Administra tus datos personales, contraseña y foto de perfil.
                        </p>
                    </div>

                </div>

            </div>
        </div>


        <!-- MENSAJE ACTUALIZADO -->

        <?php if ($mensaje === 'actualizado'): ?>

            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="mdi mdi-check-circle-outline me-2"></i>
                Perfil actualizado correctamente.
                <button type="button"
                        class="btn-close"
                        data-bs-dismiss="alert"
                        aria-label="Cerrar"></button>
            </div>

        <?php endif; ?>


        <!-- ERROR -->

        <?php if ($error === 'datos'): ?>

            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="mdi mdi-alert-circle-outline me-2"></i>
                Debes completar el nombre y el apellido.
                <button type="button"
                        class="btn-close"
                        data-bs-dismiss="alert"
                        aria-label="Cerrar"></button>
            </div>

        <?php elseif ($error === 'foto'): ?>

            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="mdi mdi-alert-circle-outline me-2"></i>
                La foto no es válida. Usa JPG, JPEG, PNG o WEBP.
                <button type="button"
                        class="btn-close"
                        data-bs-dismiss="alert"
                        aria-label="Cerrar"></button>
            </div>

        <?php elseif ($error === 'tamano'): ?>

            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="mdi mdi-alert-circle-outline me-2"></i>
                La foto no puede superar los 5 MB.
                <button type="button"
                        class="btn-close"
                        data-bs-dismiss="alert"
                        aria-label="Cerrar"></button>
            </div>

        <?php elseif ($error === 'actualizar'): ?>

            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="mdi mdi-alert-circle-outline me-2"></i>
                No fue posible actualizar el perfil.
                <button type="button"
                        class="btn-close"
                        data-bs-dismiss="alert"
                        aria-label="Cerrar"></button>
            </div>

        <?php endif; ?>


        <!-- PERFIL -->

        <div class="row">

            <!-- INFORMACIÓN PERSONAL -->

            <div class="col-md-8 grid-margin stretch-card">

                <div class="card">

                    <div class="card-body">

                        <h4 class="card-title">
                            Información personal
                        </h4>

                        <p class="card-description">
                            Actualiza tus datos personales.
                        </p>


                        <form
                            action="/inventario/perfil/actualizar"
                            method="POST"
                            enctype="multipart/form-data"
                        >

                            <!-- NOMBRE -->

                            <div class="form-group mb-3">

                                <label for="nombre">
                                    Nombre
                                </label>

                                <input
                                    type="text"
                                    class="form-control"
                                    id="nombre"
                                    name="nombre"
                                    value="<?= htmlspecialchars($nombre) ?>"
                                    maxlength="100"
                                    required
                                >

                            </div>


                            <!-- APELLIDO -->

                            <div class="form-group mb-3">

                                <label for="apellido">
                                    Apellido
                                </label>

                                <input
                                    type="text"
                                    class="form-control"
                                    id="apellido"
                                    name="apellido"
                                    value="<?= htmlspecialchars($apellido) ?>"
                                    maxlength="100"
                                    required
                                >

                            </div>


                            <!-- USUARIO -->

                            <div class="form-group mb-3">

                                <label for="usuario">
                                    Usuario
                                </label>

                                <input
                                    type="text"
                                    class="form-control"
                                    id="usuario"
                                    value="<?= htmlspecialchars($usuarioLogin) ?>"
                                    readonly
                                >

                                <small class="form-text text-muted">
                                    El nombre de usuario no se puede modificar.
                                </small>

                            </div>


                            <!-- CONTRASEÑA ACTUAL -->

                            <div class="form-group mb-3">

                                <label for="password_actual">
                                    Contraseña actual
                                </label>

                                <div class="input-group">

                                    <input
                                        type="password"
                                        class="form-control"
                                        id="password_actual"
                                        name="password_actual"
                                        autocomplete="current-password"
                                    >

                                    <button
                                        type="button"
                                        class="btn btn-outline-secondary"
                                        onclick="mostrarPassword('password_actual', this)"
                                    >
                                        <i class="mdi mdi-eye-outline"></i>
                                    </button>

                                </div>

                                <small class="form-text text-muted">
                                    Solo es necesaria si deseas cambiar tu contraseña.
                                </small>

                            </div>


                            <!-- NUEVA CONTRASEÑA -->

                            <div class="form-group mb-3">

                                <label for="password_nueva">
                                    Nueva contraseña
                                </label>

                                <div class="input-group">

                                    <input
                                        type="password"
                                        class="form-control"
                                        id="password_nueva"
                                        name="password_nueva"
                                        minlength="6"
                                        autocomplete="new-password"
                                    >

                                    <button
                                        type="button"
                                        class="btn btn-outline-secondary"
                                        onclick="mostrarPassword('password_nueva', this)"
                                    >
                                        <i class="mdi mdi-eye-outline"></i>
                                    </button>

                                </div>

                                <small class="form-text text-muted">
                                    Mínimo 6 caracteres.
                                </small>

                            </div>


                            <!-- CONFIRMAR CONTRASEÑA -->

                            <div class="form-group mb-4">

                                <label for="password_confirmar">
                                    Confirmar nueva contraseña
                                </label>

                                <div class="input-group">

                                    <input
                                        type="password"
                                        class="form-control"
                                        id="password_confirmar"
                                        name="password_confirmar"
                                        minlength="6"
                                        autocomplete="new-password"
                                    >

                                    <button
                                        type="button"
                                        class="btn btn-outline-secondary"
                                        onclick="mostrarPassword('password_confirmar', this)"
                                    >
                                        <i class="mdi mdi-eye-outline"></i>
                                    </button>

                                </div>

                            </div>


                            <!-- FOTO -->

                            <div class="form-group mb-4">

                                <label for="foto">
                                    Cambiar foto de perfil
                                </label>

                                <input
                                    type="file"
                                    class="form-control"
                                    id="foto"
                                    name="foto"
                                    accept=".jpg,.jpeg,.png,.webp"
                                >

                                <small class="form-text text-muted">
                                    JPG, JPEG, PNG o WEBP. Máximo 5 MB.
                                </small>

                            </div>


                            <!-- BOTONES -->

                            <div class="d-flex gap-2">

                                <button
                                    type="submit"
                                    class="btn btn-primary"
                                >
                                    <i class="mdi mdi-content-save me-1"></i>
                                    Guardar cambios
                                </button>

                                <a
                                    href="/inventario/calendario"
                                    class="btn btn-light"
                                >
                                    Cancelar
                                </a>

                            </div>

                        </form>

                    </div>

                </div>

            </div>


            <!-- FOTO DE PERFIL -->

            <div class="col-md-4 grid-margin stretch-card">

                <div class="card">

                    <div class="card-body text-center">

                        <h4 class="card-title">
                            Foto de perfil
                        </h4>

                        <p class="text-muted">
                            Esta imagen aparecerá en tu perfil.
                        </p>


                        <div class="mb-4">

                            <img
                                src="<?= htmlspecialchars($fotoPerfil) ?>?v=<?= time() ?>"
                                alt="Foto de perfil"
                                class="img-fluid rounded-circle"
                                style="
                                    width: 180px;
                                    height: 180px;
                                    object-fit: cover;
                                "
                            >

                        </div>


                        <h5 class="font-weight-bold mb-1">
                            <?= htmlspecialchars(trim($nombre . ' ' . $apellido)) ?>
                        </h5>

                        <p class="text-muted mb-0">
                            @<?= htmlspecialchars($usuarioLogin) ?>
                        </p>

                    </div>

                </div>

            </div>

        </div>

    </div>


    <!-- FOOTER -->

    <?php require_once __DIR__ . '/../../includes/footer.php'; ?>

</div>


<?php require_once __DIR__ . '/../../includes/scripts.php'; ?>


<script>

function mostrarPassword(id, boton) {

    const campo = document.getElementById(id);

    if (!campo) {
        return;
    }

    const icono = boton.querySelector('i');

    if (campo.type === 'password') {

        campo.type = 'text';

        if (icono) {
            icono.classList.remove('mdi-eye-outline');
            icono.classList.add('mdi-eye-off-outline');
        }

    } else {

        campo.type = 'password';

        if (icono) {
            icono.classList.remove('mdi-eye-off-outline');
            icono.classList.add('mdi-eye-outline');
        }
    }
}

</script>   