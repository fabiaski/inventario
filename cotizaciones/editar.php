<?php

require_once __DIR__ . '/../config/conexion.php';


//========================================
// OBTENER ID
//========================================

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;


if ($id <= 0) {

    exit('Cotización no válida.');

}


//========================================
// OBTENER COTIZACIÓN
//========================================

$stmt = $conexion->prepare("
    SELECT *
    FROM cotizaciones
    WHERE id = ?
");

$stmt->bind_param("i", $id);

$stmt->execute();

$resultado = $stmt->get_result();

$cotizacion = $resultado->fetch_assoc();

$stmt->close();


if (!$cotizacion) {

    exit('Cotización no encontrada.');

}


//========================================
// OBTENER PRODUCTOS DE LA COTIZACIÓN
//========================================

$stmt = $conexion->prepare("
    SELECT
        dc.id,
        dc.cotizacion_id,
        dc.producto_id,
        dc.cantidad,
        dc.valor_unidad,
        dc.porcentaje_incremento,
        dc.valor_incremento,
        dc.valor_unidad_incremento,
        dc.valor_total_unidad,
        dc.total_venta,

        p.producto,
        p.unidad_medida

    FROM detalle_cotizacion dc

    INNER JOIN productos p
        ON p.id = dc.producto_id

    WHERE dc.cotizacion_id = ?

    ORDER BY dc.id ASC
");

$stmt->bind_param("i", $id);

$stmt->execute();

$resultadoProductos = $stmt->get_result();

$productosCotizacion = [];

while ($fila = $resultadoProductos->fetch_assoc()) {

    $productosCotizacion[] = $fila;

}

$stmt->close();


//========================================
// OBTENER TODOS LOS PRODUCTOS
//========================================

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

while ($fila = $sql->fetch_assoc()) {

    $productos[] = $fila;

}


//========================================
// INCLUIR ESTRUCTURA
//========================================

include __DIR__ . '/../includes/header.php';

include __DIR__ . '/../includes/sidebar.php';

?>



<div class="admin-main">


    <?php include __DIR__ . '/../includes/navbar.php'; ?>


    <section class="panel">


        <div class="panel-header">


            <div>

                <h2 class="h5 mb-1 section-title">

                    <i class="bi bi-file-earmark-text"></i>

                    Editar Cotización #<?= $cotizacion['id'] ?>

                </h2>

                <p class="text-muted mb-0">

                    Modifique los datos de la cotización.

                </p>

            </div>


            <button type="button" class="btn btn-primary" id="btnActualizarCotizacion">

                <i class="bi bi-pencil-square"></i>

                Actualizar Cotización

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


                <input type="text" id="cliente" class="form-control"
                    value="<?= htmlspecialchars($cotizacion['cliente']) ?>">


            </div>


            <div class="col-md-3">


                <label class="form-label">

                    Fecha

                </label>


                <input type="date" id="fecha" class="form-control"
                    value="<?= htmlspecialchars($cotizacion['fecha']) ?>">


            </div>


            <div class="col-md-4">


                <label class="form-label">

                    Observaciones

                </label>


                <input type="text" id="observaciones" class="form-control"
                    value="<?= htmlspecialchars($cotizacion['observaciones'] ?? '') ?>">


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


                <tbody id="tbodyCotizacion">


                    <?php if (empty($productosCotizacion)): ?>


                    <tr id="sinProductos">

                        <td colspan="11" class="text-center text-muted">

                            No hay productos agregados.

                        </td>

                    </tr>


                    <?php else: ?>


                    <?php foreach ($productosCotizacion as $indice => $producto): ?>


                    <tr data-producto-id="<?= $producto['producto_id'] ?>">


                        <td>

                            <?= $indice + 1 ?>

                        </td>


                        <td>

                            <?= htmlspecialchars(
                                        $producto['producto']
                                    ) ?>

                        </td>


                        <td>

                            <?= $producto['cantidad'] ?>

                        </td>


                        <td>

                            <?= htmlspecialchars(
                                        $producto['unidad_medida']
                                    ) ?>

                        </td>


                        <td>

                            $<?= number_format(
                                        $producto['valor_unidad'],
                                        0,
                                        ',',
                                        '.'
                                    ) ?>

                        </td>


                        <td>

                            $<?= number_format(
                                        $producto['valor_total_unidad'],
                                        0,
                                        ',',
                                        '.'
                                    ) ?>

                        </td>


                        <td>

                            <?= rtrim(
                                        rtrim(
                                            number_format(
                                                $producto['porcentaje_incremento'],
                                                5,
                                                '.',
                                                ''
                                            ),
                                            '0'
                                        ),
                                        '.'
                                    ) ?>%

                        </td>


                        <td>

                            $<?= number_format(
                                        $producto['valor_incremento'],
                                        0,
                                        ',',
                                        '.'
                                    ) ?>

                        </td>


                        <td>

                            $<?= number_format(
                                        $producto['valor_unidad_incremento'],
                                        0,
                                        ',',
                                        '.'
                                    ) ?>

                        </td>


                        <td class="totalVenta" data-total="<?= $producto['total_venta'] ?>">

                            $<?= number_format(
                                        $producto['total_venta'],
                                        0,
                                        ',',
                                        '.'
                                    ) ?>

                        </td>



                        <td>
                            <button type="button" class="btn btn-warning btn-sm editar">

                                <i class="bi bi-pencil"></i>

                            </button>

                            <button type="button" class="btn btn-danger btn-sm btnEliminar">

                                <i class="bi bi-trash"></i>

                            </button>

                        </td>


                    </tr>


                    <?php endforeach; ?>


                    <?php endif; ?>


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

                                <td class="text-end" id="totalVenta">

                                    $<?= number_format(
                                        $cotizacion['total_venta'],
                                        0,
                                        ',',
                                        '.'
                                    ) ?>

                                </td>

                            </tr>


                            <tr>

                                <th>Retención (%)</th>

                                <td>


                                    <input type="number" id="retencion" class="form-control"
                                        value="<?= rtrim(rtrim(number_format($cotizacion['porcentaje_retencion'], 5, '.', ''), '0'), '.') ?>">

                                </td>

                            </tr>


                            <tr>

                                <th>Valor Retención</th>

                                <td class="text-end" id="valorRetencion">

                                    $<?= number_format(
                                        $cotizacion['valor_retencion'],
                                        0,
                                        ',',
                                        '.'
                                    ) ?>

                                </td>

                            </tr>


                            <tr>

                      

                                <th>Pago 1 (10%)</th>

                                <td>


                                    <div class="form-check form-switch">


                                        <input class="form-check-input" type="checkbox" id="chkPago1"
                                            <?= $cotizacion['aplica_pago1'] ? 'checked' : '' ?>>


                                    </div>


                                </td>

                            </tr>


                            <tr>

                                <th>Pago 2 (10%)</th>

                                <td>


                                    <div class="form-check form-switch">


                                        <input class="form-check-input" type="checkbox" id="chkPago2"
                                            <?= $cotizacion['aplica_pago2'] ? 'checked' : '' ?>>


                                    </div>


                                </td>

                            </tr>


                            <tr>

                                <th>Valor Pagos</th>

                                <td class="text-end" id="valorPagos">

                                    $<?= number_format(
                                        $cotizacion['valor_pagos'],
                                        0,
                                        ',',
                                        '.'
                                    ) ?>

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

                                <td class="text-end" id="valorTotalUnidad">

                                    $<?= number_format(
                                        array_sum(
                                            array_column(
                                                $productosCotizacion,
                                                'valor_total_unidad'
                                            )
                                        ),
                                        0,
                                        ',',
                                        '.'
                                    ) ?>

                                </td>

                            </tr>


                            <tr>

                                <th>Llega</th>

                                <td class="text-end" id="llega">

                                    $<?= number_format(
                                        $cotizacion['llega'],
                                        0,
                                        ',',
                                        '.'
                                    ) ?>

                                </td>

                            </tr>


                            <tr>

                                <th>Ganancia</th>

                                <td class="text-end" id="ganancia">

                                    $<?= number_format(
                                        $cotizacion['ganancia'],
                                        0,
                                        ',',
                                        '.'
                                    ) ?>

                                </td>

                            </tr>


                            <tr>

                                <th>Ganancia Ideal (20%)</th>

                                <td class="text-end" id="gananciaIdeal">

                                    $<?= number_format(
                                        $cotizacion['ganancia_ideal'],
                                        0,
                                        ',',
                                        '.'
                                    ) ?>

                                </td>

                            </tr>


                            <tr>

                                <th>Diferencia</th>

                                <td class="text-end" id="diferencia">

                                    $<?= number_format(
                                        $cotizacion['diferencia'],
                                        0,
                                        ',',
                                        '.'
                                    ) ?>

                                </td>

                            </tr>


                        </table>


                    </div>


                </div>


            </div>


        </div>


    </section>


</div>


<script>
//========================================
// PRODUCTOS DISPONIBLES
//========================================

const productos = <?= json_encode(
    $productos,
    JSON_UNESCAPED_UNICODE
) ?>;


//========================================
// COTIZACIÓN QUE ESTAMOS EDITANDO
//========================================

const cotizacionId = <?= (int) $cotizacion['id'] ?>;


//========================================
// PRODUCTOS YA GUARDADOS
//========================================

const productosEditar = <?= json_encode(
    $productosCotizacion,
    JSON_UNESCAPED_UNICODE
) ?>;
</script>


<?php include __DIR__ . '/../includes/footer.php'; ?>

<?php include __DIR__ . '/../includes/scripts.php'; ?>


<script src="<?= BASE_URL ?>assets/js/cotizaciones.js"></script>