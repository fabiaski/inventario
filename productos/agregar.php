<?php

require_once __DIR__ . '/../config/conexion.php';

include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/navbar.php';

?>

<div class="container-fluid">

    <div class="row">

        <div class="col-md-2 p-0">
            <?php include __DIR__ . '/../includes/sidebar.php'; ?>
        </div>

        <div class="col-md-10 p-4">

            <div class="card">

                <div class="card-header">

                    <h4 class="mb-0">
                        Agregar Producto
                    </h4>

                </div>

                <div class="card-body">

                    <?php if (isset($_GET['mensaje'])): ?>

                        <?php if ($_GET['mensaje'] == 'error'): ?>

                            <div class="alert alert-danger alert-dismissible fade show" role="alert">

                                <strong>Error.</strong> No fue posible guardar el producto.

                                <button
                                    type="button"
                                    class="btn-close"
                                    data-bs-dismiss="alert">
                                </button>

                            </div>

                        <?php endif; ?>

                    <?php endif; ?>

                    <form action="guardar.php" method="POST">

                        <div class="row">

                            <!-- Nombre -->
                            <div class="col-md-6 mb-3">

                                <label class="form-label">
                                    Nombre del producto
                                </label>

                                <input
                                    type="text"
                                    name="nombre_producto"
                                    class="form-control"
                                    maxlength="150"
                                    required>

                            </div>

                            <!-- Proveedor -->
                            <div class="col-md-6 mb-3">

                                <label class="form-label">
                                    Proveedor
                                </label>

                                <input
                                    type="text"
                                    name="proveedor"
                                    class="form-control"
                                    maxlength="150"
                                    required>

                            </div>

                            <!-- Especificaciones -->
                            <div class="col-12 mb-3">

                                <label class="form-label">
                                    Especificaciones
                                </label>

                                <textarea
                                    name="especificaciones"
                                    rows="3"
                                    class="form-control"
                                    placeholder="Color, referencia, marca, observaciones..."></textarea>

                            </div>

                            <!-- Cantidad -->
                            <div class="col-md-3 mb-3">

                                <label class="form-label">
                                    Cantidad
                                </label>

                                <input
                                    type="number"
                                    name="cantidad"
                                    class="form-control"
                                    step="0.01"
                                    min="0"
                                    required>

                            </div>

                            <!-- Unidad -->
                            <div class="col-md-3 mb-3">

                                <label class="form-label">
                                    Unidad
                                </label>

                                <select
                                    name="unidad_medida"
                                    class="form-select"
                                    required>

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

                            <!-- Precio -->
                            <div class="col-md-3 mb-3">

                                <label class="form-label">
                                    Precio
                                </label>

                                <input
                                    type="number"
                                    name="precio"
                                    class="form-control"
                                    step="0.01"
                                    min="0"
                                    required>

                            </div>

                            <!-- Fecha -->
                            <div class="col-md-3 mb-3">

                                <label class="form-label">
                                    Fecha de cotización
                                </label>

                                <input
                                    type="date"
                                    name="fecha_cotizacion"
                                    class="form-control"
                                    value="<?= date('Y-m-d') ?>"
                                    required>

                            </div>

                        </div>

                        <hr>

                        <button
                            type="submit"
                            class="btn btn-success">

                            Guardar Producto

                        </button>

                        <a
                            href="index.php"
                            class="btn btn-secondary">

                            Cancelar

                        </a>

                    </form>

                </div>

            </div>

        </div>

    </div>

</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>