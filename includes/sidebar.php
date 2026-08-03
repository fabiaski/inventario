<div class="bg-dark text-white p-3 sidebar">

    <h4 class="text-center mb-4">
        Inventario
    </h4>

    <a href="<?= BASE_URL ?>" class="menu-link">
        <i class="bi bi-house"></i>
        Inicio
    </a>

    <a class="menu-link"
       data-bs-toggle="collapse"
       href="#productosMenu">

        <i class="bi bi-box"></i>
        Productos
    </a>

    <div class="collapse" id="productosMenu">

        <a href="<?= BASE_URL ?>productos/agregar.php"
           class="submenu-link">
            Agregar
        </a>

        <a href="<?= BASE_URL ?>productos/editar.php"
           class="submenu-link">
            Editar
        </a>

        <a href="<?= BASE_URL ?>productos/eliminar.php"
           class="submenu-link">
            Eliminar
        </a>

        <a href="<?= BASE_URL ?>productos/index.php"
           class="submenu-link">
            CRUD
        </a>

    </div>

    <a class="menu-link"
       data-bs-toggle="collapse"
       href="#buscarMenu">

        <i class="bi bi-search"></i>
        Buscar
    </a>

    <div class="collapse" id="buscarMenu">

        <a href="<?= BASE_URL ?>productos/buscar.php"
           class="submenu-link">
            Buscar productos
        </a>

    </div>

</div>