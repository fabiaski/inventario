<div class="sidebar">

    <!-- Logo -->
    <div class="text-center py-4 border-bottom">

        <i class="bi bi-box-seam-fill text-primary fs-1"></i>

        <h5 class="mt-2 mb-0 fw-bold">
            Inventario
        </h5>

    </div>



    <!-- Productos -->
    <a
        class="menu-link"
        data-bs-toggle="collapse"
        href="#productosMenu"
        role="button"
        aria-expanded="true">

        <i class="bi bi-box-seam me-2"></i>

        Productos

        <i class="bi bi-chevron-down float-end"></i>

    </a>

    <div class="collapse show" id="productosMenu">

        <a href="<?= BASE_URL ?>productos/index.php" class="submenu-link">

            <i class="bi bi-list-ul me-2"></i>

            Ver Productos

        </a>

        <a href="<?= BASE_URL ?>productos/agregar.php" class="submenu-link">

            <i class="bi bi-plus-circle me-2"></i>

            Agregar Producto

        </a>

    </div>

    <!-- Proveedores -->
    <a
        class="menu-link"
        data-bs-toggle="collapse"
        href="#proveedoresMenu"
        role="button">

        <i class="bi bi-truck me-2"></i>

        Proveedores

        <i class="bi bi-chevron-down float-end"></i>

    </a>

    <div class="collapse" id="proveedoresMenu">

        <a href="#" class="submenu-link">

            <i class="bi bi-list-ul me-2"></i>

            Ver Proveedores

        </a>

        <a href="#" class="submenu-link">

            <i class="bi bi-plus-circle me-2"></i>

            Agregar Proveedor

        </a>

    </div>

    <!-- Reportes -->

    <a href="#" class="menu-link">

        <i class="bi bi-file-earmark-bar-graph me-2"></i>

        Reportes

    </a>

    <!-- Configuración -->

    <a href="#" class="menu-link">

        <i class="bi bi-gear me-2"></i>

        Configuración

    </a>

</div>