<?php

require_once __DIR__ . '/../../config/conexion.php';


//==================================================
// VALIDAR AÑO
//==================================================

$anio = (int) ($_GET['anio'] ?? date('Y'));

if ($anio < 2000 || $anio > 2100) {
    exit('Año no válido.');
}


//==================================================
// VALIDAR CUATRIMESTRE
//==================================================

$cuatrimestre = (int) ($_GET['cuatrimestre'] ?? 0);

if (!in_array($cuatrimestre, [1, 2, 3], true)) {
    exit('Cuatrimestre no válido.');
}


//==================================================
// CONFIGURACIÓN DE CUATRIMESTRES
//==================================================

$cuatrimestres = [

    1 => [
        'nombre' => 'Enero - Abril',
        'mes_inicio' => 1,
        'mes_fin' => 4
    ],

    2 => [
        'nombre' => 'Mayo - Agosto',
        'mes_inicio' => 5,
        'mes_fin' => 8
    ],

    3 => [
        'nombre' => 'Septiembre - Diciembre',
        'mes_inicio' => 9,
        'mes_fin' => 12
    ]

];

$periodo = $cuatrimestres[$cuatrimestre];


//==================================================
// FECHAS DEL CUATRIMESTRE
//==================================================

$fechaInicio = sprintf(
    '%04d-%02d-01',
    $anio,
    $periodo['mes_inicio']
);

$fechaFin = date(
    'Y-m-t',
    strtotime(
        sprintf(
            '%04d-%02d-01',
            $anio,
            $periodo['mes_fin']
        )
    )
);


//==================================================
// CONSULTAR CONTRATOS
//==================================================

$sql = "
    SELECT
        id,
        numero_contrato,
        fecha,
        objeto_contrato,
        valor_contrato,
        tiene_iva,
        valor_iva,
        tiene_impoconsumo,
        valor_impoconsumo,
        tiene_retencion,
        valor_retencion
    FROM contratos
    WHERE fecha BETWEEN ? AND ?
    ORDER BY fecha ASC, id ASC
";

$stmt = $conexion->prepare($sql);

if (!$stmt) {
    die(
        'Error preparando la consulta de contratos: '
        . $conexion->error
    );
}

$stmt->bind_param(
    "ss",
    $fechaInicio,
    $fechaFin
);

$stmt->execute();

$resultado = $stmt->get_result();

$contratos = [];


//==================================================
// RECORRER CONTRATOS
//==================================================

while ($contrato = $resultado->fetch_assoc()) {

    //==================================================
    // VALOR DEL CONTRATO
    //==================================================

    $valorContrato =
        (float) $contrato['valor_contrato'];


    //==================================================
    // IVA DEL CONTRATO
    //==================================================

    $ivaContrato = 0;

    if ((int) $contrato['tiene_iva'] === 1) {

        $ivaContrato =
            (float) ($contrato['valor_iva'] ?? 0);

    }


    //==================================================
    // IMPOCONSUMO DEL CONTRATO
    //==================================================

    $impoconsumoContrato = 0;

    if (
        (int) $contrato['tiene_impoconsumo'] === 1
    ) {

        $impoconsumoContrato =
            (float) ($contrato['valor_impoconsumo'] ?? 0);

    }


    //==================================================
    // RETENCIÓN DEL CONTRATO
    //==================================================

    $retencionContrato = 0;

    if (
        (int) $contrato['tiene_retencion'] === 1
    ) {

        $retencionContrato =
            (float) ($contrato['valor_retencion'] ?? 0);

    }


    //==================================================
    // INICIALIZAR VALORES DE FACTURAS
    //==================================================

    $valorFacturas = 0;

    $ivaFacturado = 0;

    $impoconsumoFacturado = 0;

    $retencionFacturada = 0;


    //==================================================
    // CONSULTAR FACTURAS DEL CONTRATO
    //==================================================

    $sqlFacturas = "
        SELECT
            valor,
            tiene_iva,
            valor_iva,
            tiene_impoconsumo,
            valor_impoconsumo,
            tiene_retencion,
            valor_retencion
        FROM facturas
        WHERE contrato_id = ?
        ORDER BY id ASC
    ";

    $stmtFacturas = $conexion->prepare($sqlFacturas);

    if (!$stmtFacturas) {
        die(
            'Error preparando la consulta de facturas: '
            . $conexion->error
        );
    }

    $stmtFacturas->bind_param(
        "i",
        $contrato['id']
    );

    $stmtFacturas->execute();

    $resultadoFacturas =
        $stmtFacturas->get_result();


    //==================================================
    // RECORRER FACTURAS
    //==================================================

    while ($factura = $resultadoFacturas->fetch_assoc()) {

        //==============================================
        // VALOR TOTAL DE LA FACTURA
        //==============================================

        $valorFacturas +=
            (float) $factura['valor'];


        //==============================================
        // IVA FACTURADO
        //==============================================

        if ((int) $factura['tiene_iva'] === 1) {

            $ivaFacturado +=
                (float) ($factura['valor_iva'] ?? 0);

        }


        //==============================================
        // IMPOCONSUMO FACTURADO
        //==============================================

        if (
            (int) $factura['tiene_impoconsumo'] === 1
        ) {

            $impoconsumoFacturado +=
                (float) (
                    $factura['valor_impoconsumo'] ?? 0
                );

        }


        //==============================================
        // RETENCIÓN FACTURADA
        //==============================================

        if (
            (int) $factura['tiene_retencion'] === 1
        ) {

            $retencionFacturada +=
                (float) (
                    $factura['valor_retencion'] ?? 0
                );

        }

    }

    $stmtFacturas->close();


    //==================================================
    // GUARDAR DATOS CALCULADOS
    //==================================================

    $contrato['iva_contrato'] =
        $ivaContrato;

    $contrato['impoconsumo_contrato'] =
        $impoconsumoContrato;

    $contrato['retencion_contrato'] =
        $retencionContrato;

    $contrato['valor_facturas'] =
        $valorFacturas;

    $contrato['iva_facturado'] =
        $ivaFacturado;

    $contrato['impoconsumo_facturado'] =
        $impoconsumoFacturado;

    $contrato['retencion_facturada'] =
        $retencionFacturada;


    //==================================================
    // SALDOS POR CONTRATO
    //==================================================

    $contrato['saldo_iva'] =
        $ivaContrato - $ivaFacturado;

    $contrato['saldo_impoconsumo'] =
        $impoconsumoContrato - $impoconsumoFacturado;

    $contrato['saldo_retencion'] =
        $retencionContrato - $retencionFacturada;


    $contratos[] = $contrato;

}

$stmt->close();


//==================================================
// FUNCIÓN PARA DINERO
//==================================================

function dinero($valor)
{
    return '$' . number_format(
        (float) $valor,
        0,
        ',',
        '.'
    );
}


//==================================================
// TOTALES
//==================================================

$totalContratos =
    count($contratos);

$totalValorContratos = 0;

$totalIvaContratos = 0;

$totalImpoconsumoContratos = 0;

$totalRetencionContratos = 0;

$totalValorFacturas = 0;

$totalIvaFacturado = 0;

$totalImpoconsumoFacturado = 0;

$totalRetencionFacturada = 0;


foreach ($contratos as $contrato) {

    $totalValorContratos +=
        (float) $contrato['valor_contrato'];

    $totalIvaContratos +=
        (float) $contrato['iva_contrato'];

    $totalImpoconsumoContratos +=
        (float) $contrato['impoconsumo_contrato'];

    $totalRetencionContratos +=
        (float) $contrato['retencion_contrato'];

    $totalValorFacturas +=
        (float) $contrato['valor_facturas'];

    $totalIvaFacturado +=
        (float) $contrato['iva_facturado'];

    $totalImpoconsumoFacturado +=
        (float) $contrato['impoconsumo_facturado'];

    $totalRetencionFacturada +=
        (float) $contrato['retencion_facturada'];

}


//==================================================
// IVA IDEAL
//==================================================

$ivaIdeal =
    $totalIvaContratos * 0.02;


//==================================================
// DIFERENCIA DE IVA
//==================================================

$diferenciaIva =
    $totalIvaContratos -
    $totalIvaFacturado;


//==================================================
// LÍMITE DEL 2% DEL IVA IDEAL
//==================================================

$limiteIva =
    $ivaIdeal;


//==================================================
// COLOR DE LA DIFERENCIA DE IVA
//==================================================

if (
    abs($diferenciaIva) > $limiteIva
) {

    $colorDiferenciaIva =
        'text-danger';

} else {

    $colorDiferenciaIva =
        'text-success';

}


//==================================================
// SALDOS DE IMPUESTOS
//==================================================

$saldoIva =
    $totalIvaContratos -
    $totalIvaFacturado;

$saldoImpoconsumo =
    $totalImpoconsumoContratos -
    $totalImpoconsumoFacturado;

$saldoRetencion =
    $totalRetencionContratos -
    $totalRetencionFacturada;


//==================================================
// SALDO TOTAL
//==================================================

$saldoTotalImpuestos =
    $saldoIva +
    $saldoImpoconsumo +
    $saldoRetencion;


//==================================================
// GANANCIAS
//==================================================

$ganancias =
    $totalValorContratos
    - $totalValorFacturas;


require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/navbar.php';
require_once __DIR__ . '/../../includes/sidebar.php';

?>


<div class="main-panel">

    <div class="content-wrapper">

        <div class="row">

            <div class="col-lg-12 grid-margin stretch-card">

                <div class="card">

                    <div class="card-body">

                        <div class="panel-header d-flex justify-content-between align-items-center">

                            <div>

                                <h2 class="h5 mb-1 section-title">

                                    <i class="bi bi-calendar3"></i>

                                    Contratos del cuatrimestre

                                </h2>

                                <p class="text-muted mb-0">

                                    <?= htmlspecialchars($periodo['nombre']) ?>

                                    -

                                    <?= $anio ?>

                                </p>

                            </div>


                            <div class="d-flex gap-2">

                                <a
                                    href="cuatrimestres.php?anio=<?= $anio ?>"
                                    class="btn btn-secondary"
                                >

                                    <i class="bi bi-arrow-left"></i>

                                    Volver

                                </a>


                                <a
                                    href="exportar_cuatrimestre.php?anio=<?= $anio ?>&cuatrimestre=<?= $cuatrimestre ?>"
                                    class="btn btn-danger"
                                    target="_blank"
                                >

                                    <i class="bi bi-file-earmark-pdf"></i>

                                    Exportar PDF

                                </a>

                            </div>

                        </div>


                        <hr>


                        <!--==================================================
                        RESUMEN
                        ==================================================-->

                        <div class="row g-3 mb-4">


                            <!-- TOTAL CONTRATOS -->

                            <div class="col-md">

                                <div class="card h-100 shadow-sm">

                                    <div class="card-body">

                                        <small class="text-muted">
                                            Total contratos
                                        </small>

                                        <h4 class="mb-0">
                                            <?= $totalContratos ?>
                                        </h4>

                                    </div>

                                </div>

                            </div>


                            <!-- VALOR CONTRATOS -->

                            <div class="col-md">

                                <div class="card h-100 shadow-sm">

                                    <div class="card-body">

                                        <small class="text-muted">
                                            Valor de contratos
                                        </small>

                                        <h5 class="mb-0">
                                            <?= dinero($totalValorContratos) ?>
                                        </h5>

                                    </div>

                                </div>

                            </div>


                            <!-- IVA CONTRATOS -->

                            <div class="col-md">

                                <div class="card h-100 shadow-sm">

                                    <div class="card-body">

                                        <small class="text-muted">
                                            IVA de contratos
                                        </small>

                                        <h5 class="mb-0">
                                            <?= dinero($totalIvaContratos) ?>
                                        </h5>

                                    </div>

                                </div>

                            </div>


                            <!-- IMPOCONSUMO CONTRATOS -->

                            <div class="col-md">

                                <div class="card h-100 shadow-sm">

                                    <div class="card-body">

                                        <small class="text-muted">
                                            Impoconsumo contratos
                                        </small>

                                        <h5 class="mb-0">
                                            <?= dinero($totalImpoconsumoContratos) ?>
                                        </h5>

                                    </div>

                                </div>

                            </div>


                            <!-- RETENCIÓN CONTRATOS -->

                            <div class="col-md">

                                <div class="card h-100 shadow-sm">

                                    <div class="card-body">

                                        <small class="text-muted">
                                            Retención contratos
                                        </small>

                                        <h5 class="mb-0">
                                            <?= dinero($totalRetencionContratos) ?>
                                        </h5>

                                    </div>

                                </div>

                            </div>


                        </div>


                        <!--==================================================
                        RESUMEN FACTURACIÓN
                        ==================================================-->

                        <div class="row g-3 mb-4">


                            <!-- VALOR FACTURAS -->

                            <div class="col-md">

                                <div class="card h-100 shadow-sm">

                                    <div class="card-body">

                                        <small class="text-muted">
                                            Valor Facturas
                                        </small>

                                        <h5 class="mb-0">
                                            <?= dinero($totalValorFacturas) ?>
                                        </h5>

                                    </div>

                                </div>

                            </div>


                            <!-- IVA FACTURADO -->

                            <div class="col-md">

                                <div class="card h-100 shadow-sm">

                                    <div class="card-body">

                                        <small class="text-muted">
                                            IVA Facturado
                                        </small>

                                        <h5 class="mb-0">
                                            <?= dinero($totalIvaFacturado) ?>
                                        </h5>

                                    </div>

                                </div>

                            </div>


                            <!-- IMPOCONSUMO FACTURADO -->

                            <div class="col-md">

                                <div class="card h-100 shadow-sm">

                                    <div class="card-body">

                                        <small class="text-muted">
                                            Impoconsumo Facturado
                                        </small>

                                        <h5 class="mb-0">
                                            <?= dinero($totalImpoconsumoFacturado) ?>
                                        </h5>

                                    </div>

                                </div>

                            </div>


                            <!-- RETENCIÓN FACTURADA -->

                            <div class="col-md">

                                <div class="card h-100 shadow-sm">

                                    <div class="card-body">

                                        <small class="text-muted">
                                            Retención Facturada
                                        </small>

                                        <h5 class="mb-0">
                                            <?= dinero($totalRetencionFacturada) ?>
                                        </h5>

                                    </div>

                                </div>

                            </div>


                        </div>


                        <!--==================================================
                        INDICADORES
                        ==================================================-->

                        <div class="row g-3 mb-4">


                            <!-- IVA IDEAL -->

                            <div class="col-md">

                                <div class="card h-100 shadow-sm">

                                    <div class="card-body">

                                        <i class="bi bi-percent text-primary"></i>

                                        <small class="text-muted">
                                            IVA Ideal
                                        </small>

                                        <h5 class="mb-0">
                                            <?= dinero($ivaIdeal) ?>
                                        </h5>

                                        <small class="text-muted">
                                            IVA del contrato × 2%
                                        </small>

                                    </div>

                                </div>

                            </div>


                            <!-- DIFERENCIA IVA -->

                            <div class="col-md">

                                <div class="card h-100 shadow-sm">

                                    <div class="card-body">

                                        <i class="bi bi-arrow-down-up text-warning"></i>

                                        <small class="text-muted">
                                            Diferencia de IVA
                                        </small>

                                        <h5 class="mb-0 <?= $colorDiferenciaIva ?>">

                                            <?= dinero($diferenciaIva) ?>

                                        </h5>

                                        <small class="text-muted">
                                            IVA del contrato − IVA Facturado
                                        </small>

                                    </div>

                                </div>

                            </div>


                            <!-- SALDO IMPOCONSUMO -->

                            <div class="col-md">

                                <div class="card h-100 shadow-sm">

                                    <div class="card-body">

                                        <small class="text-muted">
                                            Saldo Impoconsumo
                                        </small>

                                        <h5 class="mb-0">

                                            <?= dinero($saldoImpoconsumo) ?>

                                        </h5>

                                        <small class="text-muted">
                                            Impoconsumo contrato − facturado
                                        </small>

                                    </div>

                                </div>

                            </div>


                            <!-- SALDO RETENCIÓN -->

                            <div class="col-md">

                                <div class="card h-100 shadow-sm">

                                    <div class="card-body">

                                        <small class="text-muted">
                                            Saldo Retención
                                        </small>

                                        <h5 class="mb-0">

                                            <?= dinero($saldoRetencion) ?>

                                        </h5>

                                        <small class="text-muted">
                                            Retención contrato − facturada
                                        </small>

                                    </div>

                                </div>

                            </div>


                            <!-- GANANCIAS -->

                            <div class="col-md">

                                <div class="card h-100 shadow-sm">

                                    <div class="card-body">

                                        <i class="bi bi-graph-up-arrow text-success"></i>

                                        <small class="text-muted">
                                            Ganancias
                                        </small>

                                        <h5 class="mb-0">

                                            <?= dinero($ganancias) ?>

                                        </h5>

                                        <small class="text-muted">
                                            Valor del contrato − Valor Facturas
                                        </small>

                                    </div>

                                </div>

                            </div>


                        </div>


                        <!--==================================================
                        SALDO TOTAL IMPUESTOS
                        ==================================================-->

                        <div class="row g-3 mb-4">

                            <div class="col-md-4">

                                <div class="card shadow-sm">

                                    <div class="card-body">

                                        <small class="text-muted">
                                            Saldo total impuestos
                                        </small>

                                        <h5 class="mb-0">
                                            <?= dinero($saldoTotalImpuestos) ?>
                                        </h5>

                                        <small class="text-muted">
                                            IVA + Impoconsumo + Retención
                                        </small>

                                    </div>

                                </div>

                            </div>

                        </div>


                        <!--==================================================
                        TABLA
                        ==================================================-->

                        <div class="table-responsive">

                            <table class="table table-bordered table-hover align-middle">

                                <thead class="table-light">

                                    <tr>

                                        <th>
                                            N° Contrato
                                        </th>

                                        <th>
                                            Valor del Contrato
                                        </th>

                                        <th>
                                            IVA Contrato
                                        </th>

                                        <th>
                                            Impoconsumo Contrato
                                        </th>

                                        <th>
                                            Retención Contrato
                                        </th>

                                        <th>
                                            Valor Facturas
                                        </th>

                                        <th>
                                            IVA Facturado
                                        </th>

                                        <th>
                                            Impoconsumo Facturado
                                        </th>

                                        <th>
                                            Retención Facturada
                                        </th>

                                        <th>
                                            Acciones
                                        </th>

                                    </tr>

                                </thead>


                                <tbody>

                                    <?php if (count($contratos) > 0): ?>

                                        <?php foreach ($contratos as $contrato): ?>

                                            <tr>

                                                <!-- NÚMERO CONTRATO -->

                                                <td>

                                                    <?= htmlspecialchars(
                                                        $contrato['numero_contrato']
                                                    ) ?>

                                                </td>


                                                <!-- VALOR CONTRATO -->

                                                <td class="text-end">

                                                    <?= dinero(
                                                        $contrato['valor_contrato']
                                                    ) ?>

                                                </td>


                                                <!-- IVA CONTRATO -->

                                                <td class="text-end">

                                                    <?= dinero(
                                                        $contrato['iva_contrato']
                                                    ) ?>

                                                </td>


                                                <!-- IMPOCONSUMO CONTRATO -->

                                                <td class="text-end">

                                                    <?= dinero(
                                                        $contrato['impoconsumo_contrato']
                                                    ) ?>

                                                </td>


                                                <!-- RETENCIÓN CONTRATO -->

                                                <td class="text-end">

                                                    <?= dinero(
                                                        $contrato['retencion_contrato']
                                                    ) ?>

                                                </td>


                                                <!-- VALOR FACTURAS -->

                                                <td class="text-end">

                                                    <?= dinero(
                                                        $contrato['valor_facturas']
                                                    ) ?>

                                                </td>


                                                <!-- IVA FACTURADO -->

                                                <td class="text-end">

                                                    <?= dinero(
                                                        $contrato['iva_facturado']
                                                    ) ?>

                                                </td>


                                                <!-- IMPOCONSUMO FACTURADO -->

                                                <td class="text-end">

                                                    <?= dinero(
                                                        $contrato['impoconsumo_facturado']
                                                    ) ?>

                                                </td>


                                                <!-- RETENCIÓN FACTURADA -->

                                                <td class="text-end">

                                                    <?= dinero(
                                                        $contrato['retencion_facturada']
                                                    ) ?>

                                                </td>


                                                <!-- ACCIONES -->

                                                <td>

                                                    <a
                                                        href="<?= BASE_URL ?>facturacion/ver.php?id=<?= $contrato['id'] ?>"
                                                        class="btn btn-primary btn-sm d-inline-flex align-items-center gap-1"
                                                        title="Ver facturas del contrato"
                                                    >

                                                        <i class="bi bi-eye"></i>

                                                        <span>
                                                            Ver facturas
                                                        </span>

                                                    </a>

                                                </td>

                                            </tr>

                                        <?php endforeach; ?>

                                    <?php else: ?>

                                        <tr>

                                            <td
                                                colspan="10"
                                                class="text-center text-muted py-4"
                                            >

                                                No hay contratos registrados
                                                en este cuatrimestre.

                                            </td>

                                        </tr>

                                    <?php endif; ?>

                                </tbody>


                                <!--==================================================
                                TOTALES
                                ==================================================-->

                                <?php if (count($contratos) > 0): ?>

                                    <tfoot class="table-light">

                                        <tr>

                                            <th>
                                                TOTAL
                                            </th>


                                            <th class="text-end">

                                                <?= dinero(
                                                    $totalValorContratos
                                                ) ?>

                                            </th>


                                            <th class="text-end">

                                                <?= dinero(
                                                    $totalIvaContratos
                                                ) ?>

                                            </th>


                                            <th class="text-end">

                                                <?= dinero(
                                                    $totalImpoconsumoContratos
                                                ) ?>

                                            </th>


                                            <th class="text-end">

                                                <?= dinero(
                                                    $totalRetencionContratos
                                                ) ?>

                                            </th>


                                            <th class="text-end">

                                                <?= dinero(
                                                    $totalValorFacturas
                                                ) ?>

                                            </th>


                                            <th class="text-end">

                                                <?= dinero(
                                                    $totalIvaFacturado
                                                ) ?>

                                            </th>


                                            <th class="text-end">

                                                <?= dinero(
                                                    $totalImpoconsumoFacturado
                                                ) ?>

                                            </th>


                                            <th class="text-end">

                                                <?= dinero(
                                                    $totalRetencionFacturada
                                                ) ?>

                                            </th>


                                            <th></th>

                                        </tr>

                                    </tfoot>

                                <?php endif; ?>

                            </table>

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>

</div>


<?php

include __DIR__ . '/../../includes/footer.php';

include __DIR__ . '/../../includes/scripts.php';

?>