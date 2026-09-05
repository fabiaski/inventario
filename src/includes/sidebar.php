<div class="container-fluid page-body-wrapper">
    <!-- partial:partials/_sidebar.html -->
    <nav class="sidebar sidebar-offcanvas" id="sidebar">
        <ul class="nav">
            <li class="nav-item">
                <a class="nav-link" href="../../index.php">
                    <i class="mdi mdi-grid-large menu-icon"></i>
                    <span class="menu-title">Dashboard</span>
                </a>
            </li>


            <li class="nav-item nav-category">inventario</li>

            <li class="nav-item">
                <a class="nav-link" data-bs-toggle="collapse" href="#ui-productos" aria-expanded="false"
                    aria-controls="ui-productos">
                    <i class="menu-icon mdi mdi-package-variant"></i>
                    <span class="menu-title">Productos</span>
                    <i class="menu-arrow"></i>
                </a>
                <div class="collapse" id="ui-productos">
                    <ul class="nav flex-column sub-menu">
                        <li class="nav-item"> <a class="nav-link" href="../productos/productos.php">Ver
                                Productos</a>
                        </li>
                        <li class="nav-item"> <a class="nav-link" href="../productos/agregar-produ.php">Agregar
                                Producto</a>
                        </li>
                    </ul>
                </div>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="../productos/importar.php">
                    <i class="menu-icon fa fa-file-excel-o"></i>
                    <span class="menu-title">Importar Excel</span>
                </a>
            </li>

            <li class="nav-item nav-category">Cotizaciones</li>

            <li class="nav-item">
                <a class="nav-link" data-bs-toggle="collapse" href="#form-elements" aria-expanded="false"
                    aria-controls="form-elements">
                    <i class="menu-icon mdi mdi-file-document-edit"></i>
                    <span class="menu-title">Cotizaciones</span>
                    <i class="menu-arrow"></i>
                </a>
                <div class="collapse" id="form-elements">
                    <ul class="nav flex-column sub-menu">
                        <li class="nav-item"><a class="nav-link" href="../cotizaciones/cotizacion.php">Ver
                                cotizaciones</a></li>
                        <li class="nav-item"><a class="nav-link" href="../cotizaciones/agregar-coti.php">Nueva
                                cotización</a></li>
                    </ul>
                </div>
            </li>

            <li class="nav-item nav-category">Facturacion</li>

            <li class="nav-item">
                <a class="nav-link" data-bs-toggle="collapse" href="#form-Factura" aria-expanded="false"
                    aria-controls="form-Factura">
                    <i class="menu-icon mdi mdi-receipt-text"></i>
                    <span class="menu-title">Facturacion</span>
                    <i class="menu-arrow"></i>
                </a>
                <div class="collapse" id="form-Factura">
                    <ul class="nav flex-column sub-menu">
                        <li class="nav-item"><a class="nav-link" href="../facturacion/facturacion.php">
                                Ver cotizaciones</a></li>
                        <li class="nav-item"><a class="nav-link" href="../facturacion/agregar-fact.php">
                                Nueva cotización</a></li>
                    </ul>
                </div>
            </li>

         

            <li class="nav-item nav-category">Reportes</li>

            <li class="nav-item">
                <a class="nav-link" data-bs-toggle="collapse" href="#charts" aria-expanded="false"
                    aria-controls="charts">
                    <i class="menu-icon mdi mdi-chart-line"></i>
                    <span class="menu-title">Reportes</span>
                    <i class="menu-arrow"></i>
                </a>
                <div class="collapse" id="charts">
                    <ul class="nav flex-column sub-menu">
                        <li class="nav-item"> <a class="nav-link" href="../reportes/cuatrimestres.php">Cuatrimestres</a>
                        </li>
                    </ul>
                </div>
            </li>

            <li class="nav-item">
                <a class="nav-link" href="../smmlv/index.php">
                    <i class="menu-icon mdi mdi-file-document"></i>
                    <span class="menu-title">SMMLV</span>
                </a>
            </li>

        </ul>
    </nav>

