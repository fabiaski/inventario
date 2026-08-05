<?php

require_once __DIR__ . '/../config/conexion.php';

include __DIR__ . '/../includes/header.php';

?>

<div class="admin-main">

    <?php include __DIR__ . '/../includes/navbar.php'; ?>

    <?php include __DIR__ . '/../includes/sidebar.php'; ?>

    <!-- ALERTAS -->

    <?php if (isset($_GET['mensaje'])): ?>

    <?php if ($_GET['mensaje'] == 'error'): ?>

    <div class="alert alert-danger alert-dismissible fade show" role="alert">

        <strong>Error.</strong> No fue posible guardar el producto.

        <button type="button" class="btn-close" data-bs-dismiss="alert">
        </button>

    </div>

    <?php endif; ?>

    <?php endif; ?>

    <!-- FIN ALERTAS -->

    <section class="panel">

        <div class="panel-header">

            <div>

                <h2 class="h5 mb-1 section-title">

                    <i class="bi bi-plus-circle"></i>

                    <span>Agregar Producto</span>

                </h2>

                <p class="text-muted mb-0">

                    Registra un nuevo producto en el inventario.

                </p>

            </div>

        </div>

        <form action="guardar.php" method="POST">

            <div class="row g-3">

                <div class="col-md-8">

                    <label class="form-label">
                        Producto / Descripción
                    </label>

                    <textarea name="producto" class="form-control" rows="3" maxlength="255"
                        placeholder="Ej: Pintura tipo 1 color verde, marca Pintuco" required></textarea>

                </div>

                <div class="col-md-4">

                    <label class="form-label">
                        Proveedor
                    </label>

                    <input type="text" name="proveedor" class="form-control" maxlength="150" placeholder="Opcional">
                </div>

                <div class="col-md-3">

                    <label class="form-label">
                        Cantidad
                    </label>

                    <input type="number" name="cantidad" class="form-control" step="0.01" min="0" required>
                </div>

                <div class="col-md-3">

                    <label class="form-label">
                        Unidad
                    </label>

                    <select name="unidad_medida" class="form-select" required>
                        <option value="">Seleccione...</option>
                        <option>Unidad</option>
                        <option>kg</option>
                        <option>g</option>
                        <option>lb</option>
                        <option>m</option>
                        <option>cm</option>
                        <option>mm</option>
                        <option>m²</option>
                        <option>m³</option>
                        <option>L</option>
                        <option>ml</option>
                        <option>Galón</option>
                        <option>Caja</option>
                        <option>Bulto</option>
                        <option>Rollo</option>

                    </select>

                </div>

                                <div class="col-md-3">

                    <label class="form-label">
                        Precio
                    </label>

                    <input type="number" name="precio" class="form-control" step="0.01" min="0" required>

                </div>

                <div class="col-md-3">

                    <label class="form-label">
                        Fecha de cotización
                    </label>

                    <input type="date" name="fecha_cotizacion" class="form-control" value="<?= date('Y-m-d') ?>"
                        required>

                </div>

            </div>

            <div class="d-flex justify-content-end gap-2 mt-4">

                <a href="index.php" class="btn btn-outline-secondary">

                    Cancelar

                </a>

                <button type="submit" class="btn btn-primary">

                    <i class="bi bi-check-circle"></i>

                    Guardar Producto

                </button>

            </div>

        </form>

    </section>


<?php include __DIR__ . '/../includes/footer.php'; ?>

</div>



<script src="../assets/js/bootstrap.bundle.min.js"></script>
<script src="../assets/js/main.js"></script>
</body>
