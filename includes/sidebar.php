<div class="sidebar">

   <!-- <div class="sidebar-title">
        INVENTARIO
    </div>
-->
    
    <a href="<?= BASE_URL ?>" class="menu-link">
        <i class="bi bi-house-door-fill me-2"></i>
        Dashboard
    </a>

    <a
        class="menu-link"
        data-bs-toggle="collapse"
        href="#productosMenu"
        role="button">

        <i class="bi bi-box-seam me-2"></i>

        Productos

        <i class="bi bi-chevron-down float-end"></i>

    </a>

    <div class="collapse" id="productosMenu">

        <a href="<?= BASE_URL ?>productos/agregar.php" class="submenu-link">
            Agregar
        </a>

        <a href="<?= BASE_URL ?>productos/buscar.php" class="submenu-link">
            Buscar
        </a>

        <a href="<?= BASE_URL ?>productos/index.php" class="submenu-link">
            Ver Todos
        </a>

    </div>

</div>