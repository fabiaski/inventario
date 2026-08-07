<?php
require_once __DIR__ . '/../config/conexion.php';

$productos = $conexion->query("
    SELECT id, producto, precio, unidad_medida
    FROM productos
    ORDER BY producto ASC
");

include __DIR__ . '/../includes/header.php';
?>

<div class="admin-main">

    <?php include __DIR__ . '/../includes/navbar.php'; ?>
    <?php include __DIR__ . '/../includes/sidebar.php'; ?>

    <section class="panel">

        <div class="panel-header">

            <div>

                <h2 class="h5 mb-1 section-title">

                    <i class="bi bi-file-earmark-text"></i>

                    Nueva Cotización

                </h2>

                <p class="text-muted mb-0">

                    Agrega los productos que harán parte de la cotización.

                </p>

            </div>

            <button class="btn btn-primary">

                Guardar Cotización

            </button>

        </div>

        <form id="formCotizacion">

    <div class="row g-3 mb-4">

        <div class="col-md-4">

            <label class="form-label">
                Cliente
            </label>

            <input
                type="text"
                id="cliente"
                class="form-control"
                placeholder="Nombre del cliente">

        </div>

        <div class="col-md-3">

            <label class="form-label">
                Fecha
            </label>

            <input
                type="date"
                id="fecha"
                class="form-control"
                value="<?= date('Y-m-d') ?>">

        </div>

        <div class="col-md-5">

            <label class="form-label">
                Observaciones
            </label>

            <input
                type="text"
                id="observaciones"
                class="form-control"
                placeholder="Observaciones de la cotización">

        </div>

    </div>

    <hr class="mb-4">

    <div class="row g-3 align-items-end">

        <div class="col-md-5">

            <label class="form-label">
                Producto
            </label>

            <select
                id="producto"
                class="form-select">

                <option value="">
                    Seleccione un producto...
                </option>

                <?php while($p = $productos->fetch_assoc()): ?>

                    <option
                        value="<?= $p['id'] ?>"
                        data-precio="<?= $p['precio'] ?>"
                        data-unidad="<?= $p['unidad_medida'] ?>">

                        <?= htmlspecialchars($p['producto']) ?>

                        | <?= htmlspecialchars($p['unidad_medida']) ?>

                        | $<?= number_format($p['precio'],0,',','.') ?>

                    </option>

                <?php endwhile; ?>

            </select>

        </div>

        <div class="col-md-1">

            <label class="form-label">
                Cantidad
            </label>

            <input
                type="number"
                id="cantidad"
                class="form-control"
                value="1"
                min="1">

        </div>

        <div class="col-md-2">

            <label class="form-label">
                Unidad
            </label>

            <input
                type="text"
                id="unidad"
                class="form-control"
                readonly>

        </div>

        <div class="col-md-2">

            <label class="form-label">
                Valor Unitario
            </label>

            <input
                type="text"
                id="precio"
                class="form-control"
                readonly>

        </div>

        <div class="col-md-1 text-center">

            <label class="form-label d-block">
                IVA
            </label>

            <div class="form-check form-switch d-flex justify-content-center">

                <input
                    class="form-check-input"
                    type="checkbox"
                    id="iva">

            </div>

        </div>

        <div class="col-md-1 d-grid">

            <button
                type="button"
                id="btnAgregar"
                class="btn btn-success">

                <i class="bi bi-plus-lg"></i>

            </button>

        </div>

    </div>

</form>

<hr class="my-4">

       <div class="table-responsive">

    <table class="table table-hover align-middle mb-0" id="tablaCotizacion">

        <thead>

            <tr>

                <th>#</th>

                <th>Producto</th>

                <th>Cantidad</th>

                <th>Unidad</th>

                <th>Valor Unitario</th>

                <th>Subtotal</th>

                <th>IVA</th>

                <th>Valor IVA</th>

                <th>Total</th>

                <th></th>

            </tr>

        </thead>

        <tbody>

            <tr id="sinProductos">

                <td colspan="10" class="text-center text-muted py-4">

                    <i class="bi bi-cart-x fs-3"></i>

                    <br>

                    No hay productos agregados.

                </td>

            </tr>

        </tbody>

    </table>

</div>

        <div class="row justify-content-end">

            <div class="col-md-4">

                <table class="table">

                    <tr>

                        <th>Subtotal</th>

                        <td class="text-end">$0</td>

                    </tr>

                    <tr>

                        <th>Total IVA</th>

                        <td class="text-end">$0</td>

                    </tr>

                    <tr class="table-primary">

                        <th>Total Cotización</th>

                        <th class="text-end">$0</th>

                    </tr>

                </table>

            </div>

        </div>

    </section>
<script src="<?= BASE_URL ?>assets/js/cotizaciones.js"></script>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
<?php include __DIR__ . '/../includes/scripts.php'; ?>