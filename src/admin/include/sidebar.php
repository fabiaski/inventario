<?php

// ==========================================================
// URL ACTUAL
// ==========================================================

$urlActual = $_SERVER['REQUEST_URI'] ?? '';


// ==========================================================
// OPCIÓN ACTIVA
// ==========================================================

$dashboardActivo = (
    $urlActual === '/inventario/admin' ||
    $urlActual === '/inventario/admin/'
);

$usuariosActivo = (
    strpos($urlActual, '/inventario/admin/usuarios') === 0
);

$permisosActivo = (
    strpos($urlActual, '/inventario/admin/permisos') === 0
);

$actividadActivo = (
    strpos($urlActual, '/inventario/admin/actividad') === 0
);

?>

<!-- ======================================================
     SIDEBAR ADMINISTRADOR
====================================================== -->
<div class="container-fluid page-body-wrapper">

    <nav class="sidebar sidebar-offcanvas" id="sidebar">

        <ul class="nav">


            <!-- ==================================================
                 DASHBOARD ADMIN
            ================================================== -->

            <li class="nav-item">

                <a class="nav-link" href="/inventario/admin">

                    <i class="mdi mdi-grid-large menu-icon"></i>

                    <span class="menu-title">
                        Dashboard
                    </span>

                </a>

            </li>


            <!-- ==================================================
                 ADMINISTRACIÓN
            ================================================== -->

            <li class="nav-item nav-category">
                Administración
            </li>


            <!-- USUARIOS -->

            <li class="nav-item">

                <a class="nav-link" href="/inventario/usuarios">

                    <i class="menu-icon mdi mdi-account-multiple"></i>

                    <span class="menu-title">
                        Usuarios
                    </span>

                </a>

            </li>


            <!-- PERMISOS -->

            <li class="nav-item">

                <a class="nav-link" href="/inventario/permisos">

                    <i class="menu-icon mdi mdi-shield-check"></i>

                    <span class="menu-title">
                        Permisos
                    </span>

                </a>

            </li>


            <!-- ACTIVIDAD -->

            <li class="nav-item">

                <a class="nav-link" href="/inventario/actividad">

                    <i class="menu-icon mdi mdi-history"></i>

                    <span class="menu-title">
                        Actividad
                    </span>

                </a>

            </li>


            <!-- ==================================================
                 VOLVER AL SISTEMA
            ================================================== -->

            <li class="nav-item nav-category">
                Sistema
            </li>


            <li class="nav-item">

                <a class="nav-link" href="/inventario/calendario">

                    <i class="menu-icon mdi mdi-arrow-left"></i>

                    <span class="menu-title">
                        Volver al sistema
                    </span>

                </a>

            </li>


            <!-- ==================================================
                 CERRAR SESIÓN
            ================================================== -->

            <li class="nav-item">

                <a class="nav-link" href="/inventario/login/cerrar">

                    <i class="menu-icon mdi mdi-logout"></i>

                    <span class="menu-title">
                        Cerrar sesión
                    </span>

                </a>

            </li>


        </ul>

    </nav>