<div class="container-fluid page-body-wrapper">
    <!-- partial:partials/_sidebar.html -->
    <nav class="sidebar sidebar-offcanvas" id="sidebar">
        <ul class="nav">

            <!-- Dashboard -->
            <li class="nav-item">
                <a class="nav-link" href="/inventario/calendario">
                    <i class="mdi mdi-grid-large menu-icon"></i>
                    <span class="menu-title">Dashboard</span>
                </a>
            </li>


            <!-- INVENTARIO -->
            <li class="nav-item nav-category">Inventario</li>

            <li class="nav-item">
    <a class="nav-link" href="/inventario/productos">
        <i class="menu-icon mdi mdi-package-variant"></i>
        <span class="menu-title">Productos</span>
    </a>
</li>

<li class="nav-item">
    <a class="nav-link" href="/inventario/importarprodu">
        <i class="menu-icon fa fa-file-excel-o"></i>
        <span class="menu-title">Importar Excel</span>
    </a>
</li>


            <!-- COTIZACIONES -->
            <li class="nav-item nav-category">Cotizaciones</li>

            <li class="nav-item">
                <a class="nav-link"
                   data-bs-toggle="collapse"
                   href="#form-elements"
                   aria-expanded="false"
                   aria-controls="form-elements">

                    <i class="menu-icon mdi mdi-file-document-edit"></i>
                    <span class="menu-title">Cotizaciones</span>
                    <i class="menu-arrow"></i>
                </a>

                <div class="collapse" id="form-elements">
                    <ul class="nav flex-column sub-menu">

                        <li class="nav-item">
                            <a class="nav-link" href="/inventario/cotizaciones">
                                Ver cotizaciones
                            </a>
                        </li>

                        <li class="nav-item">
                            <a class="nav-link" href="/inventario/cotizaciones-agregar">
                                Nueva cotización
                            </a>
                        </li>

                    </ul>
                </div>
            </li>


            <!-- FACTURACIÓN -->
            <li class="nav-item nav-category">Facturación</li>

            <li class="nav-item">
                <a class="nav-link"
                   data-bs-toggle="collapse"
                   href="#form-Factura"
                   aria-expanded="false"
                   aria-controls="form-Factura">

                    <i class="menu-icon mdi mdi-receipt-text"></i>
                    <span class="menu-title">Facturación</span>
                    <i class="menu-arrow"></i>
                </a>

                <div class="collapse" id="form-Factura">
                    <ul class="nav flex-column sub-menu">

                        <li class="nav-item">
                            <a class="nav-link" href="/inventario/facturacion">
                                Ver facturación
                            </a>
                        </li>

                        <li class="nav-item">
                            <a class="nav-link" href="/inventario/facturacion-agregar">
                                Nueva factura
                            </a>
                        </li>

                    </ul>
                </div>
            </li>


            <!-- REPORTES -->
            <li class="nav-item nav-category">Reportes</li>

            <li class="nav-item">
                <a class="nav-link" href="/inventario/reportes">
                    <i class="menu-icon mdi mdi-chart-line"></i>
                    <span class="menu-title">Cuatrimestres</span>
                </a>
            </li>


            <!-- SMMLV -->
            <li class="nav-item">
                <a class="nav-link" href="/inventario/smmlv">
                    <i class="menu-icon mdi mdi-file-document"></i>
                    <span class="menu-title">SMMLV</span>
                </a>
            </li>


            <!-- FAVORES -->
            <li class="nav-item">
                <a class="nav-link" href="/inventario/favores">
                    <i class="menu-icon mdi mdi-handshake"></i>
                    <span class="menu-title">Favores</span>
                </a>
            </li>

        </ul>
    </nav>