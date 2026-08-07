<?php include __DIR__ . '/../includes/header.php'; ?>

<?php include __DIR__ . '/../includes/sidebar.php'; ?>

<div class="admin-main">

    <?php include __DIR__ . '/../includes/navbar.php'; ?>

    <section class="panel">

        <div class="panel-header">

            <div>

                <h2 class="h5 mb-1 section-title">

                    <i class="bi bi-file-earmark-text"></i>

                    <span>Nueva Cotización</span>

                </h2>

                <p class="text-muted mb-0">

                    Agrega los productos que harán parte de la cotización.

                </p>

            </div>

            <button type="button" class="btn btn-success">

                <i class="bi bi-floppy"></i>

                Guardar Cotización

            </button>

        </div>

        <!-- Información general -->

        <div class="row g-3 mb-4">

            <div class="col-md-5">

                <label class="form-label">
                    Cliente
                </label>

                <input type="text" class="form-control" placeholder="Nombre del cliente">

            </div>

            <div class="col-md-3">

                <label class="form-label">
                    Fecha
                </label>

                <input type="date" class="form-control" value="<?= date('Y-m-d') ?>">

            </div>

            <div class="col-md-4">

                <label class="form-label">
                    Observaciones
                </label>

                <input type="text" class="form-control">

            </div>

        </div>

        <hr>

        <!-- Agregar productos -->

        <h5 class="mb-3">

            Agregar Producto

        </h5>

        <div class="row g-3 align-items-end">

            <div class="col-md-4 position-relative">

                <label class="form-label">
                    Buscar Producto
                </label>

                <input type="text" id="buscarProducto" class="form-control"
                    placeholder="Escriba el nombre del producto...">

                <input type="hidden" id="producto_id">

                <div id="listaProductos" class="list-group position-absolute w-100 shadow"
                    style="z-index:1000; display:none; max-height:250px; overflow:auto;">
                </div>

            </div>
            <div class="col-md-1">

                <label class="form-label">

                    Cantidad

                </label>

                <input type="number" class="form-control" value="1">

            </div>

            <div class="col-md-2">

                <label class="form-label">

                    Valor Unidad

                </label>

                <input type="text" class="form-control" readonly>

            </div>

            <div class="col-md-2">

                <label class="form-label">

                    % Incremento

                </label>

                <input type="number" class="form-control" placeholder="0">

            </div>

            <div class="col-md-2">

                <button class="btn btn-primary w-100">

                    <i class="bi bi-plus-circle"></i>

                    Agregar

                </button>

            </div>

        </div>

        <hr class="my-4">

        <!-- Tabla -->

        <div class="table-responsive">

            <table class="table table-bordered align-middle">

                <thead class="table-light">

                    <tr>

                        <th>#</th>

                        <th>Producto</th>

                        <th>Cantidad</th>

                        <th>UND</th>

                        <th>Valor UND</th>

                        <th>Total UND</th>

                        <th>% Inc.</th>

                        <th>Valor Inc.</th>

                        <th>UND + Inc.</th>

                        <th>Total Venta</th>

                        <th></th>

                    </tr>

                </thead>

                <tbody>

                    <tr>

                        <td colspan="11" class="text-center text-muted">

                            No hay productos agregados.

                        </td>

                    </tr>

                </tbody>

            </table>

        </div>

        <div class="row mt-4">

            <!-- Resumen de descuentos -->
            <div class="col-lg-6">

                <div class="card shadow-sm border-0 h-100">

                    <div class="card-header bg-white">

                        <h6 class="mb-0 fw-bold">

                            <i class="bi bi-calculator"></i>

                            Resumen de la Cotización

                        </h6>

                    </div>

                    <div class="card-body">

                        <table class="table table-borderless align-middle mb-0">

                            <tr>

                                <th width="45%">Total Venta</th>

                                <td class="text-end fw-bold" id="totalVenta">

                                    $ 0

                                </td>

                            </tr>

                            <tr>

                                <th>Retención (%)</th>

                                <td>

                                    <input type="number" class="form-control form-control-sm" id="retencion" value="0"
                                        min="0" step="0.01">

                                </td>

                            </tr>

                            <tr>

                                <th>Valor Retención</th>

                                <td class="text-end" id="valorRetencion">

                                    $ 0

                                </td>

                            </tr>

                            <tr>

                                <th>Pago 1 (10%)</th>

                                <td>

                                    <div class="form-check form-switch">

                                        <input class="form-check-input" type="checkbox" id="chkPago1">

                                    </div>

                                </td>

                            </tr>

                            <tr>

                                <th>Pago 2 (10%)</th>

                                <td>

                                    <div class="form-check form-switch">

                                        <input class="form-check-input" type="checkbox" id="chkPago2">

                                    </div>

                                </td>

                            </tr>

                            <tr>

                                <th>Valor Pagos</th>

                                <td class="text-end" id="valorPagos">

                                    $ 0

                                </td>

                            </tr>

                        </table>

                    </div>

                </div>

            </div>

            <!-- Resultados -->
            <div class="col-lg-6">

                <div class="card shadow-sm border-0 h-100">

                    <div class="card-header bg-white">

                        <h6 class="mb-0 fw-bold">

                            <i class="bi bi-graph-up-arrow"></i>

                            Resultado Final

                        </h6>

                    </div>

                    <div class="card-body">

                        <table class="table table-borderless align-middle mb-0">

                            <tr>

                                <th width="45%">Llega</th>

                                <td class="text-end fw-bold text-primary" id="llega">

                                    $ 0

                                </td>

                            </tr>

                            <tr>

                                <th>Ganancia</th>

                                <td class="text-end text-success fw-bold" id="ganancia">

                                    $ 0

                                </td>

                            </tr>

                            <tr>

                                <th>Ganancia Ideal (20%)</th>

                                <td class="text-end" id="gananciaIdeal">

                                    $ 0

                                </td>

                            </tr>

                            <tr class="table-light">

                                <th>Diferencia</th>

                                <td class="text-end fw-bold" id="diferencia">

                                    $ 0

                                </td>

                            </tr>

                        </table>

                    </div>

                </div>

            </div>

        </div>

    </section>
    <script src="<?= BASE_URL ?>assets/js/cotizaciones.js"></script>

    <?php include __DIR__ . '/../includes/footer.php'; ?>

</div>

<?php include __DIR__ . '/../includes/scripts.php'; ?>