<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$nombreUsuario = trim(
    ($_SESSION['usuario_nombre'] ?? '') . ' ' .
    ($_SESSION['usuario_apellido'] ?? '')
);

$nombreUsuario = $nombreUsuario !== ''
    ? $nombreUsuario
    : 'Usuario';

$usuarioLogin = $_SESSION['usuario'] ?? '';

?>

<body class="with-welcome-text">

    <div class="container-scroller">

        <!-- partial:partials/_navbar.html -->

        <nav class="navbar default-layout col-lg-12 col-12 p-0 fixed-top d-flex align-items-top flex-row">

            <div class="text-center navbar-brand-wrapper d-flex align-items-center justify-content-start">

                <div class="me-3">
                    <button class="navbar-toggler navbar-toggler align-self-center" type="button"
                        data-bs-toggle="minimize">
                        <span class="icon-menu"></span>
                    </button>
                </div>

                <div>
                    <a class="navbar-brand brand-logo">
                        <img src="/inventario/src/assets/images/myglogo.svg" alt="logo" />
                    </a>

                    <a class="navbar-brand brand-logo-mini">
                        <img src="/inventario/src/assets/images/myglogo.svg" alt="logo" />
                    </a>
                </div>

            </div>


            <div class="navbar-menu-wrapper d-flex align-items-top">

                <ul class="navbar-nav">

                    <li class="nav-item fw-semibold d-none d-lg-block ms-0">

                        <h1 class="welcome-text">
                            Bienvenido,
                            <span class="text-black fw-bold">
                                <?= htmlspecialchars($nombreUsuario) ?>
                            </span>
                        </h1>

                    </li>

                </ul>


                <ul class="navbar-nav ms-auto">


                    <!-- ==================================================
                         NOTIFICACIONES
                    ================================================== -->

                    <li class="nav-item dropdown">

                        <a class="nav-link count-indicator" id="countDropdown" href="#" data-bs-toggle="dropdown"
                            aria-expanded="false">
                            <i class="icon-mail icon-lg"></i>
                        </a>

                        <div class="dropdown-menu dropdown-menu-right navbar-dropdown preview-list pb-0"
                            aria-labelledby="countDropdown">

                            <a class="dropdown-item py-3">

                                <p class="mb-0 fw-medium float-start">
                                    You have 7 unread mails
                                </p>

                                <span class="badge badge-pill badge-primary float-end">
                                    View all
                                </span>

                            </a>

                            <div class="dropdown-divider"></div>

                            <a class="dropdown-item preview-item">

                                <div class="preview-item-content flex-grow py-2">

                                    <p class="preview-subject ellipsis fw-medium text-dark">
                                        Marian Garner
                                    </p>

                                    <p class="fw-light small-text mb-0">
                                        The meeting is cancelled
                                    </p>

                                </div>

                            </a>

                            <a class="dropdown-item preview-item">

                                <div class="preview-item-content flex-grow py-2">

                                    <p class="preview-subject ellipsis fw-medium text-dark">
                                        David Grey
                                    </p>

                                    <p class="fw-light small-text mb-0">
                                        The meeting is cancelled
                                    </p>

                                </div>

                            </a>

                            <a class="dropdown-item preview-item">

                                <div class="preview-item-content flex-grow py-2">

                                    <p class="preview-subject ellipsis fw-medium text-dark">
                                        Travis Jenkins
                                    </p>

                                    <p class="fw-light small-text mb-0">
                                        The meeting is cancelled
                                    </p>

                                </div>

                            </a>

                        </div>

                    </li>


                    <!-- ==================================================
                         PERFIL
                    ================================================== -->
<li class="nav-item dropdown">

    <?php
    $fotoUsuario = $_SESSION['usuario_foto'] ?? '';

    if (!empty($fotoUsuario)) {
        $rutaFotoUsuario = '/inventario/src/assets/images/usuarios/' . rawurlencode($fotoUsuario);
    } else {
        $rutaFotoUsuario = '/inventario/src/assets/images/faces/face.jpg';
    }
    ?>

    <a
        class="nav-link"
        id="UserDropdown"
        href="#"
        data-bs-toggle="dropdown"
        aria-expanded="false"
    >

        <img
            class="img-xs rounded-circle"
            src="<?= htmlspecialchars($rutaFotoUsuario) ?>?v=<?= time() ?>"
            alt="Profile image"
            style="width: 35px; height: 35px; object-fit: cover;"
        />

    </a>


                        <!-- ==================================================
                         PERFIL
                    ================================================== -->

                        <div class="dropdown-menu dropdown-menu-right navbar-dropdown" aria-labelledby="UserDropdown">

                            <!-- ENCABEZADO DEL PERFIL -->

                     <div class="dropdown-header text-center">

    <?php
    $fotoUsuario = $_SESSION['usuario_foto'] ?? '';

    if (!empty($fotoUsuario)) {
        $rutaFotoUsuario = '/inventario/src/assets/images/usuarios/' . rawurlencode($fotoUsuario);
    } else {
        $rutaFotoUsuario = '/inventario/src/assets/images/faces/face.jpg';
    }
    ?>

    <img
        class="img-md rounded-circle"
        src="<?= htmlspecialchars($rutaFotoUsuario) ?>?v=<?= time() ?>"
        alt="Profile image"
        style="width: 70px; height: 70px; object-fit: cover;"
    />

    <p class="mb-1 mt-3 fw-semibold">
        <?= htmlspecialchars($nombreUsuario) ?>
    </p>

    <?php if ($usuarioLogin !== ''): ?>

        <p class="fw-light text-muted mb-0">
            @<?= htmlspecialchars($usuarioLogin) ?>
        </p>

    <?php endif; ?>

</div>

                               <!-- MI PERFIL -->

    <a
        class="dropdown-item"
        href="/inventario/perfil"
    >

        <i class="dropdown-item-icon mdi mdi-account-outline text-primary me-2"></i>

        Mi perfil

    </a>
    <?php if (
        isset($_SESSION['rol_nombre']) &&
        $_SESSION['rol_nombre'] === 'Administrador'
    ): ?>

        <a
            class="dropdown-item"
            href="/inventario/admin"
        >

            <i class="dropdown-item-icon mdi mdi-shield-account text-primary me-2"></i>

            Panel administrador

        </a>

    <?php endif; ?>

                            <!-- ==================================================
                                 CERRAR SESIÓN
                            ================================================== -->

                            <a class="dropdown-item" href="/inventario/login/cerrar">

                                <i class="dropdown-item-icon mdi mdi-power text-primary me-2"></i>

                                Cerrar sesión

                            </a>

                        </div>

                    </li>

                </ul>


                <!-- MENÚ MÓVIL -->

                <button class="navbar-toggler navbar-toggler-right d-lg-none align-self-center" type="button"
                    data-bs-toggle="offcanvas">
                    <span class="mdi mdi-menu"></span>
                </button>

            </div>

        </nav>