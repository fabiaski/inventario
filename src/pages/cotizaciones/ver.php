<?php

require_once __DIR__ . '/../../config/conexion.php';

$id = $_GET['id'] ?? 0;

$id = (int) $id;

if ($id <= 0) {
    exit('Cotización no válida.');
}


//========================================
// CONSULTAR COTIZACIÓN
//========================================

$sql = "
    SELECT *
    FROM cotizaciones
    WHERE id = ?
";

$stmt = $conexion->prepare($sql);

$stmt->bind_param("i", $id);

$stmt->execute();

$resultado = $stmt->get_result();

$cotizacion = $resultado->fetch_assoc();

if (!$cotizacion) {
    exit('Cotización no encontrada.');
}


//========================================
// CONSULTAR PRODUCTOS
//========================================

$sqlProductos = "
    SELECT
        dc.*,
        p.producto,
        p.unidad_medida
    FROM detalle_cotizacion dc
    INNER JOIN productos p
        ON p.id = dc.producto_id
    WHERE dc.cotizacion_id = ?
    ORDER BY dc.id ASC
";

$stmtProductos = $conexion->prepare($sqlProductos);

$stmtProductos->bind_param("i", $id);

$stmtProductos->execute();

$productosCotizacion = $stmtProductos->get_result();


//========================================
// CALCULAR VALOR TOTAL UNIDAD
//========================================

$valorTotalUnidad = 0;

while ($producto = $productosCotizacion->fetch_assoc()) {

    $valorTotalUnidad += (float) $producto['valor_total_unidad'];

}

// Volver a consultar los productos para poder mostrarlos en la tabla

$stmtProductos->execute();

$productosCotizacion = $stmtProductos->get_result();



require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';
require_once __DIR__ . '/../includes/sidebar.php';

?>


 <div class="container-fluid px-3 px-lg-4 py-4">
        
    <section class="panel">
    <div class="container-fluid">

        <!-- ========================================
         DATOS DE LA COTIZACIÓN
    ========================================= -->

        <div class="card mb-4">

            <div class="card-header d-flex justify-content-between align-items-center">

                <strong>
                    Cotización #<?= $cotizacion['id'] ?>
                </strong>




                <a href="editar.php?id=<?= $cotizacion['id'] ?>" class="btn btn-warning btn-sm">

                    <i class="bi bi-pencil"></i>

                    Editar

                </a>

            </div>

            <div class="card-body">

                <div class="row">

                    <div class="col-md-6">

                        <strong>Cliente:</strong>

                        <?= htmlspecialchars($cotizacion['cliente']) ?>

                    </div>

                    <div class="col-md-6">

                        <strong>Fecha:</strong>

                        <?= htmlspecialchars($cotizacion['fecha']) ?>

                    </div>

                </div>

                <?php if (!empty($cotizacion['observaciones'])): ?>

                <div class="mt-3">

                    <strong>Observaciones:</strong>

                    <?= nl2br(
                        htmlspecialchars(
                            $cotizacion['observaciones']
                        )
                    ) ?>

                </div>

                <?php endif; ?>

            </div>

        </div>


        <!-- ========================================
         PRODUCTOS
    ========================================= -->

        <div class="card mb-4">

            <div class="card-header">

                <strong>Productos</strong>

            </div>

            <div class="card-body">

                <div class="table-responsive">

                    <table class="table table-bordered">

                        <thead>

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

                            </tr>

                        </thead>

                        <tbody>

                            <?php

                        $numero = 1;

                        while ($producto = $productosCotizacion->fetch_assoc()):

                        ?>

                            <tr>

                                <td>
                                    <?= $numero++ ?>
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
                                    <?= rtrim(rtrim(number_format($producto['porcentaje_incremento'], 5, '.', ''), '0'), '.') ?>%
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

                                <td>
                                    $<?= number_format(
                                        $producto['total_venta'],
                                        0,
                                        ',',
                                        '.'
                                    ) ?>
                                </td>

                            </tr>

                            <?php endwhile; ?>

                        </tbody>

                    </table>

                </div>

            </div>

        </div>


        <!-- ========================================
         RESUMEN
    ========================================= -->

        <div class="card mb-4">

            <div class="card-header">

                <strong>Resumen</strong>

            </div>

            <div class="card-body">

                <table class="table table-borderless">

                    <tr>

                        <th>Total Venta</th>

                        <td class="text-end">

                            $<?= number_format(
                            $cotizacion['total_venta'],
                            0,
                            ',',
                            '.'
                        ) ?>

                        </td>

                    </tr>

                    <tr>

                        <th>
                            Retención
                            (<?= rtrim(rtrim(number_format($cotizacion['porcentaje_retencion'], 5, '.', ''), '0'), '.') ?>%)
                        </th>

                        <td class="text-end">

                            $<?= number_format(
                            $cotizacion['valor_retencion'],
                            0,
                            ',',
                            '.'
                        ) ?>

                        </td>

                    </tr>

                    <tr>

                        <th>Valor Pagos</th>

                        <td class="text-end">

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


        <!-- ========================================
         RESULTADO FINAL
    ========================================= -->

        <div class="card mb-4">

            <div class="card-header">

                <strong>Resultado Final</strong>

            </div>

            <div class="card-body">

                <table class="table table-borderless">

                    <tr>

                        <th>Valor total unidad</th>

                        <td class="text-end">

                            $<?= number_format(
        $valorTotalUnidad,
        0,
        ',',
        '.'
                        ) ?>

                        </td>

                    </tr>

                    <tr>

                        <th>Llega</th>

                        <td class="text-end">

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

                        <td class="text-end">

                            $<?= number_format(
                            $cotizacion['ganancia'],
                            0,
                            ',',
                            '.'
                        ) ?>

                        </td>

                    </tr>

                    <tr>

                        <th>
                            Ganancia Ideal 20%
                        </th>

                        <td class="text-end">

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

                        <td class="text-end">

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
        <?php if ($cotizacion['estado'] === 'Borrador'): ?>

        <form action="finalizar.php" method="POST" class="d-inline"
            onsubmit="return confirm('¿Desea finalizar esta cotización?');">

            <input type="hidden" name="cotizacion_id" value="<?= $cotizacion['id'] ?>">

            <button type="submit" class="btn btn-success">
                <i class="bi bi-check-circle"></i>
                Finalizar Cotización
            </button>

        </form>

        <?php endif; ?>


        <?php if ($cotizacion['estado'] === 'Finalizada'): ?>

        <a href="pdf.php?id=<?= $cotizacion['id'] ?>" class="btn btn-danger" target="_blank">
            <i class="bi bi-file-earmark-pdf"></i>
            Descargar PDF
        </a>

        <a href="excel.php?id=<?= $cotizacion['id'] ?>" class="btn btn-success">
            <i class="bi bi-file-earmark-excel"></i>
            Descargar Excel
        </a>

        <?php endif; ?>
    </div>



</div>

    <?php include __DIR__ . '/../includes/footer.php'; ?>v
<?php include __DIR__ . '/../includes/scripts.php'; ?>

