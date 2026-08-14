<div class="sidebar-backdrop" data-sidebar-close></div>

<aside
    class="admin-sidebar"
    id="adminSidebar"
    aria-label="Main navigation"
>

    <!--==================================================
    ENCABEZADO
    ==================================================-->

    <div class="sidebar-header">

        <a
            class="brand-mark"
            href="<?= BASE_URL ?>index.php"
            aria-label="adminHMD dashboard"
        >

            <span class="brand-icon">

                <i
                    class="bi bi-grid-1x2-fill"
                    aria-hidden="true"
                ></i>

            </span>


            <span class="brand-copy">

                <span class="brand-title">
                    adminHMD
                </span>

                <span class="brand-subtitle">
                    Sistema Administrativo
                </span>

            </span>

        </a>

    </div>


    <!--==================================================
    NAVEGACIÓN
    ==================================================-->

    <nav class="sidebar-nav">


        <!--==================================================
        DASHBOARD
        ==================================================-->

        <a
            class="nav-link"
            href="<?= BASE_URL ?>index.php"
        >

            <span class="nav-icon">

                <i class="bi bi-speedometer2"></i>

            </span>

            <span class="nav-text">

                Dashboard

            </span>

        </a>


        <!--==================================================
        INVENTARIO
        ==================================================-->

        <a
            class="nav-link collapsed"
            data-bs-toggle="collapse"
            href="#menuInventario"
            role="button"
            aria-expanded="false"
            aria-controls="menuInventario"
        >

            <span class="nav-icon">

                <i class="bi bi-box-seam"></i>

            </span>

            <span class="nav-text flex-grow-1">

                Inventario

            </span>

            <i class="bi bi-chevron-down small"></i>

        </a>


        <div
            class="collapse"
            id="menuInventario"
        >


            <!-- VER PRODUCTOS -->

            <a
                class="nav-link ps-5"
                href="<?= BASE_URL ?>productos/index.php"
            >

                <span class="nav-icon">

                    <i class="bi bi-list-ul"></i>

                </span>

                <span class="nav-text">

                    Ver productos

                </span>

            </a>


            <!-- AGREGAR PRODUCTO -->

            <a
                class="nav-link ps-5"
                href="<?= BASE_URL ?>productos/agregar.php"
            >

                <span class="nav-icon">

                    <i class="bi bi-plus-circle"></i>

                </span>

                <span class="nav-text">

                    Agregar producto

                </span>

            </a>


            <!-- IMPORTAR PRODUCTOS -->

            <a
                class="nav-link ps-5"
                href="<?= BASE_URL ?>productos/importar.php"
            >

                <span class="nav-icon">

                    <i class="bi bi-file-earmark-excel"></i>

                </span>

                <span class="nav-text">

                    Importar Excel

                </span>

            </a>


        </div>


        <!--==================================================
        COTIZACIONES
        ==================================================-->

        <a
            class="nav-link collapsed"
            data-bs-toggle="collapse"
            href="#menuCotizaciones"
            role="button"
            aria-expanded="false"
            aria-controls="menuCotizaciones"
        >

            <span class="nav-icon">

                <i class="bi bi-file-earmark-text"></i>

            </span>

            <span class="nav-text flex-grow-1">

                Cotizaciones

            </span>

            <i class="bi bi-chevron-down small"></i>

        </a>


        <div
            class="collapse"
            id="menuCotizaciones"
        >


            <!-- VER COTIZACIONES -->

            <a
                class="nav-link ps-5"
                href="<?= BASE_URL ?>cotizaciones/index.php"
            >

                <span class="nav-icon">

                    <i class="bi bi-list-ul"></i>

                </span>

                <span class="nav-text">

                    Ver cotizaciones

                </span>

            </a>


            <!-- NUEVA COTIZACIÓN -->

            <a
                class="nav-link ps-5"
                href="<?= BASE_URL ?>cotizaciones/agregar.php"
            >

                <span class="nav-icon">

                    <i class="bi bi-plus-circle"></i>

                </span>

                <span class="nav-text">

                    Nueva cotización

                </span>

            </a>


        </div>


        <!--==================================================
        FACTURACIÓN
        ==================================================-->

        <a
            class="nav-link collapsed"
            data-bs-toggle="collapse"
            href="#menuFacturacion"
            role="button"
            aria-expanded="false"
            aria-controls="menuFacturacion"
        >

            <span class="nav-icon">

                <i class="bi bi-receipt"></i>

            </span>

            <span class="nav-text flex-grow-1">

                Facturación

            </span>

            <i class="bi bi-chevron-down small"></i>

        </a>


        <div
            class="collapse"
            id="menuFacturacion"
        >


            <!-- VER CONTRATOS -->

            <a
                class="nav-link ps-5"
                href="<?= BASE_URL ?>facturacion/index.php"
            >

                <span class="nav-icon">

                    <i class="bi bi-list-ul"></i>

                </span>

                <span class="nav-text">

                    Ver contratos

                </span>

            </a>


            <!-- NUEVO CONTRATO -->

            <a
                class="nav-link ps-5"
                href="<?= BASE_URL ?>facturacion/agregar.php"
            >

                <span class="nav-icon">

                    <i class="bi bi-plus-circle"></i>

                </span>

                <span class="nav-text">

                    Nuevo contrato

                </span>

            </a>


        </div>


        <!--==================================================
        REPORTES
        ==================================================-->

        <a
            class="nav-link collapsed"
            data-bs-toggle="collapse"
            href="#menuReportes"
            role="button"
            aria-expanded="false"
            aria-controls="menuReportes"
        >

            <span class="nav-icon">

                <i class="bi bi-bar-chart-line"></i>

            </span>

            <span class="nav-text flex-grow-1">

                Reportes

            </span>

            <i class="bi bi-chevron-down small"></i>

        </a>


        <div
            class="collapse"
            id="menuReportes"
        >


            <!-- CUATRIMESTRES -->

            <a
                class="nav-link ps-5"
                href="<?= BASE_URL ?>reportes/cuatrimestres.php"
            >

                <span class="nav-icon">

                    <i class="bi bi-calendar3"></i>

                </span>

                <span class="nav-text">

                    Cuatrimestres

                </span>

            </a>


        </div>


    </nav>

</aside>