<div class="sidebar-backdrop" data-sidebar-close></div>

<aside class="admin-sidebar" id="adminSidebar" aria-label="Main navigation">
    <div class="sidebar-header">
        <a class="brand-mark" href="index.php" aria-label="adminHMD dashboard">
            <span class="brand-icon"><i class="bi bi-grid-1x2-fill" aria-hidden="true"></i></span>
            <span class="brand-copy">
                <span class="brand-title">adminHMD</span>
                <span class="brand-subtitle">Admin Template</span>
            </span>
        </a>
    </div>

    <nav class="sidebar-nav">

        <!-- Menú Inventario -->
        <a class="nav-link collapsed" data-bs-toggle="collapse" href="#menuInventario" role="button"
            aria-expanded="false">

            <span class="nav-icon">
                <i class="bi bi-box-seam"></i>
            </span>

            <span class="nav-text flex-grow-1">
                Inventario
            </span>

            <i class="bi bi-chevron-down small"></i>
        </a>

        <div class="collapse" id="menuInventario">

            <a class="nav-link ps-5" href="<?= BASE_URL ?>productos/index.php">
                <span class="nav-icon">
                    <i class="bi bi-list-ul"></i>
                </span>

                <span class="nav-text">
                    Ver productos
                </span>
            </a>

            <a class="nav-link ps-5" href="<?= BASE_URL ?>productos/agregar.php">
                <span class="nav-icon">
                    <i class="bi bi-plus-circle"></i>
                </span>

                <span class="nav-text">
                    Agregar producto
                </span>
            </a>

        </div>

         <a class="nav-link collapsed" data-bs-toggle="collapse" href="#menuInv" role="button"
            aria-expanded="false">

            <span class="nav-icon">
                <i class="bi bi-box-seam"></i>
            </span>

            <span class="nav-text flex-grow-1">
                Inventario
            </span>

            <i class="bi bi-chevron-down small"></i>
        </a>

        <div class="collapse" id="menuInv">

            <a class="nav-link ps-5" href="<?= BASE_URL ?>productos/index.php">
                <span class="nav-icon">
                    <i class="bi bi-list-ul"></i>
                </span>

                <span class="nav-text">
                    Ver productos
                </span>
            </a>

            <a class="nav-link ps-5" href="<?= BASE_URL ?>productos/agregar.php">
                <span class="nav-icon">
                    <i class="bi bi-plus-circle"></i>
                </span>

                <span class="nav-text">
                    Agregar producto
                </span>
            </a>

        </div>

    </nav>

</aside>