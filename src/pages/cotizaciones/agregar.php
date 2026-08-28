<?php
require_once __DIR__ . '/../config/conexion.php';

include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/sidebar.php';
?>

<div class="admin-main">

    <?php include __DIR__ . '/../includes/navbar.php'; ?>

     <div class="container-fluid px-3 px-lg-4 py-4">

    <section class="panel">

        <div class="panel-header">

            <div>

                <h2 class="h5 mb-1 section-title">

                    <i class="bi bi-file-earmark-text"></i>

                    Nueva Cotización

                </h2>

                <p class="text-muted mb-0">

                    Agregue los productos que harán parte de la cotización.

                </p>

            </div>

            <button type="button" class="btn btn-success"     id="btnGuardarCotizacion"
>

                <i class="bi bi-floppy"></i>

                Guardar Cotización

            </button>
            

        </div>

        <hr>

        <!-- ========================= -->
        <!-- DATOS DE LA COTIZACIÓN -->
        <!-- ========================= -->

        <div class="row g-3 mb-4">

            <div class="col-md-5">

                <label class="form-label">

                    Cliente

                </label>

                <input type="text" id="cliente" class="form-control">

            </div>

            <div class="col-md-3">

                <label class="form-label">

                    Fecha

                </label>

                <input type="date" id="fecha" class="form-control" value="<?= date('Y-m-d') ?>">

            </div>

            <div class="col-md-4">

                <label class="form-label">

                    Observaciones

                </label>

                <input type="text" id="observaciones" class="form-control">

            </div>

        </div>

        <hr>

        <!-- ========================= -->
        <!-- AGREGAR PRODUCTO -->
        <!-- ========================= -->

        <h5 class="mb-3">

            Agregar Producto

        </h5>

        <div class="row g-3 align-items-end">

            <div class="col-md-4 position-relative">

                <label class="form-label">

                    Producto

                </label>

                <input type="text" id="buscarProducto" class="form-control" autocomplete="off"
                    placeholder="Buscar producto...">

                <input type="hidden" id="producto_id">

                <div id="listaProductos" class="list-group position-absolute w-100 shadow"
                    style="display:none; z-index:9999; max-height:250px; overflow:auto;">

                </div>

            </div>

            <div class="col-md-1">

                <label class="form-label">

                    Cantidad

                </label>

                <input type="number" id="cantidad" class="form-control" value="1" min="1">

            </div>

            <div class="col-md-2">

                <label class="form-label">

                    Valor UND

                </label>

                <input type="text" id="valorUnidad" class="form-control" readonly>

            </div>

            <div class="col-md-2">

                <label class="form-label">

                    % Incremento

                </label>

                <input type="number" id="incremento" class="form-control" value="0" min="0" step="0.01">

            </div>

            <div class="col-md-2">

                <button type="button" id="btnAgregar" class="btn btn-primary w-100">

                    <i class="bi bi-plus-circle"></i>

                    Agregar

                </button>

            </div>

        </div>

        <hr class="my-4">

        <!-- ========================= -->
        <!-- TABLA -->
        <!-- ========================= -->

        <div class="table-responsive">

            <table class="table table-bordered align-middle" id="tablaCotizacion">

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

                    <tr id="sinProductos">

                        <td colspan="11" class="text-center text-muted">

                            No hay productos agregados.

                        </td>

                    </tr>

                </tbody>

            </table>

        </div>

        <!-- ========================= -->
        <!-- RESUMEN -->
        <!-- ========================= -->

        <div class="row mt-4">

            <div class="col-lg-6">

                <div class="card shadow-sm">

                    <div class="card-header">

                        <strong>

                            Resumen

                        </strong>

                    </div>

                    <div class="card-body">

                        <table class="table table-borderless">

                            <tr>

                                <th>Total Venta</th>

                                <td class="text-end" id="totalVenta">$0</td>

                            </tr>

                            <tr>

                                <th>Retención (%)</th>

                                <td>

                                    <input type="number" id="retencion" class="form-control" value="19">

                                </td>

                            </tr>

                            <tr>

                                <th>Valor Retención</th>

                                <td class="text-end" id="valorRetencion">

                                    $0

                                </td>

                            </tr>

                            <tr>

                                <th>Pago 1 (10%)</th>

                                <td>

                                    <div class="form-check form-switch">

                                        <input class="form-check-input" type="checkbox" id="chkPago1" checked>

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

                                    $0

                                </td>

                            </tr>

                        </table>

                    </div>

                </div>

            </div>

            <div class="col-lg-6">

                <div class="card shadow-sm">

                    <div class="card-header">

                        <strong>

                            Resultado Final

                        </strong>

                    </div>

                    <div class="card-body">

                        <table class="table table-borderless">

                            <tr>
                                <th>Valor total unidad</th>
                                <td class="text-end" id="valorTotalUnidad">$0</td>
                            </tr>
                            <tr>

                                <th>Llega</th>

                                <td class="text-end" id="llega">$0</td>

                            </tr>

                            <tr>

                                <th>Ganancia</th>

                                <td class="text-end" id="ganancia">$0</td>

                            </tr>

                            <tr>

                                <th>Ganancia Ideal (20%)</th>

                                <td class="text-end" id="gananciaIdeal">

                                    $0

                                </td>

                            </tr>

                            <tr>

                                <th>Diferencia</th>

                                <td class="text-end" id="diferencia"> $0 </td>

                            </tr>

                        </table>

                    </div>

                </div>

            </div>

        </div>

    </section>

</div>

<?php

$productos = [];

$sql = $conexion->query("
    SELECT
        id,
        producto,
        unidad_medida,
        precio
    FROM productos
    ORDER BY producto
");

while($fila = $sql->fetch_assoc()){

    $productos[] = $fila;

}

?>

<script>
const productos = <?= json_encode($productos) ?>;
</script>
<?php include __DIR__ . '/../includes/footer.php'; ?>
<?php include __DIR__ . '/../includes/scripts.php'; ?>

<script src="<?= BASE_URL ?>assets/js/cotizaciones.js"></script>