<?php

require_once __DIR__ . '/../config/conexion.php';


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

$cuatrimestre =
    (int) ($_GET['cuatrimestre'] ?? 0);


if (
    !in_array(
        $cuatrimestre,
        [1, 2, 3],
        true
    )
) {

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


$periodo =
    $cuatrimestres[$cuatrimestre];


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
        valor_contrato
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


$resultado =
    $stmt->get_result();


$contratos = [];


//==================================================
// RECORRER CONTRATOS
//==================================================

while (
    $contrato =
    $resultado->fetch_assoc()
) {


    //==================================================
    // VALOR DEL CONTRATO
    //==================================================

    $valorContrato =
        (float) $contrato['valor_contrato'];


    //==================================================
    // VALOR DEL CONTRATO SIN IVA
    //==================================================

    $valorSinIva =
        $valorContrato / 1.19;


    //==================================================
    // IVA DEL CONTRATO
    //==================================================

    $ivaContrato =
        $valorContrato - $valorSinIva;


    //==================================================
    // INICIALIZAR VALORES DE FACTURAS
    //==================================================

    $valorFacturas = 0;

$ivaFacturado = 0;


    //==================================================
    // CONSULTAR FACTURAS DEL CONTRATO
    //==================================================

    $sqlFacturas = "
        SELECT
            porcentaje_iva,
            valor,
            valor_sin_iva,
            valor_iva
        FROM facturas
        WHERE contrato_id = ?
        ORDER BY id ASC
    ";


    $stmtFacturas =
        $conexion->prepare(
            $sqlFacturas
        );


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

    while (
        $factura =
        $resultadoFacturas->fetch_assoc()
    ) {


        //==============================================
        // VALOR TOTAL DE LA FACTURA
        //==============================================

        $valorFacturas +=
            (float) $factura['valor'];


       //==============================================
// TOTAL IVA FACTURADO
//==============================================

$ivaFacturado +=
    (float) $factura['valor_iva'];

    }


    $stmtFacturas->close();


    //==================================================
    // GUARDAR DATOS CALCULADOS
    //==================================================

    $contrato['valor_sin_iva'] =
        $valorSinIva;


    $contrato['iva_contrato'] =
        $ivaContrato;


    $contrato['valor_facturas'] =
        $valorFacturas;


    $contrato['iva_facturado'] =
    $ivaFacturado;


    $contratos[] =
        $contrato;

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

$totalValorFacturas = 0;

$totalIvaFacturado = 0;

foreach (
    $contratos
    as $contrato
) {

    $totalValorContratos +=
        (float) $contrato[
            'valor_contrato'
        ];


    $totalIvaContratos +=
        (float) $contrato[
            'iva_contrato'
        ];


    $totalValorFacturas +=
        (float) $contrato[
            'valor_facturas'
        ];


    $totalIvaFacturado +=
    (float) $contrato[
        'iva_facturado'
    ];

    
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
    $totalIvaContratos - $totalIvaFacturado;


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
// GANANCIAS
//==================================================

$ganancias =
    $totalValorContratos
    - $totalValorFacturas;


//==================================================
// INCLUDES
//==================================================

include __DIR__ . '/../includes/header.php';

include __DIR__ . '/../includes/sidebar.php';

?>


<div class="admin-main">

    <?php
    include __DIR__ . '/../includes/navbar.php';
    ?>

    <div class="container-fluid px-3 px-lg-4 py-4">

        <section class="panel">


            <!--==================================================
        ENCABEZADO
        ==================================================-->

            <div class="panel-header">


                <div>

                    <h2 class="h5 mb-1 section-title">

                        <i class="bi bi-calendar3"></i>

                        Contratos del cuatrimestre

                    </h2>


                    <p class="text-muted mb-0">

                        <?= htmlspecialchars(
                        $periodo['nombre']
                    ) ?>

                        -

                        <?= $anio ?>

                    </p>

                </div>


                <div class="d-flex gap-2">

                    <!-- VOLVER -->

                    <a href="cuatrimestres.php?anio=<?= $anio ?>" class="btn btn-secondary">

                        <i class="bi bi-arrow-left"></i>

                        Volver

                    </a>


                    <!-- EXPORTAR PDF -->

                    <a href="exportar_cuatrimestre.php?anio=<?= $anio ?>&cuatrimestre=<?= $cuatrimestre ?>"
                        class="btn btn-danger" target="_blank">

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


                <!--==================================================
    TOTAL CONTRATOS
    ==================================================-->

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


                <!--==================================================
    VALOR CONTRATOS
    ==================================================-->

                <div class="col-md">

                    <div class="card h-100 shadow-sm">

                        <div class="card-body">

                            <small class="text-muted">

                                Valor de contratos

                            </small>

                            <h5 class="mb-0">

                                <?= dinero(
                        $totalValorContratos
                    ) ?>

                            </h5>

                        </div>

                    </div>

                </div>


                <!--==================================================
    IVA CONTRATOS
    ==================================================-->

                <div class="col-md">

                    <div class="card h-100 shadow-sm">

                        <div class="card-body">

                            <small class="text-muted">

                                IVA de contratos

                            </small>

                            <h5 class="mb-0">

                                <?= dinero(
                        $totalIvaContratos
                    ) ?>

                            </h5>

                        </div>

                    </div>

                </div>


                <!--==================================================
    VALOR FACTURAS
    ==================================================-->

                <div class="col-md">

                    <div class="card h-100 shadow-sm">

                        <div class="card-body">

                            <small class="text-muted">

                                Valor Facturas

                            </small>

                            <h5 class="mb-0">

                                <?= dinero(
                        $totalValorFacturas
                    ) ?>

                            </h5>

                        </div>

                    </div>

                </div>


                <!--==================================================
    TOTAL IVA FACTURADO
    ==================================================-->

                <div class="col-md">

                    <div class="card h-100 shadow-sm">

                        <div class="card-body">

                            <small class="text-muted">

                                Total IVA Facturado

                            </small>

                            <h5 class="mb-0">

                                <?= dinero(
                        $totalIvaFacturado
                    ) ?>

                            </h5>

                        </div>

                    </div>

                </div>


            </div>


            <!--==================================================
INDICADORES
==================================================-->

            <div class="row g-3 mb-4">


                <!--==================================================
    IVA IDEAL
    ==================================================-->

                <div class="col-md">

                    <div class="card h-100 shadow-sm">

                        <div class="card-body">
                            <i class="bi bi-percent text-primary"></i>

                            <small class="text-muted">

                                IVA Ideal

                            </small>

                            <h5 class="mb-0">

                                <?= dinero(
                        $ivaIdeal
                    ) ?>

                            </h5>

                            <small class="text-muted">

                                IVA del contrato × 2%

                            </small>

                        </div>

                    </div>

                </div>


                <!--==================================================
    DIFERENCIA DE IVA
    ==================================================-->

                <div class="col-md">

                    <div class="card h-100 shadow-sm">

                        <div class="card-body">

                            <i class="bi bi-arrow-down-up text-warning"></i>

                            <small class="text-muted">

                                Diferencia de IVA

                            </small>

                            <h5 class="mb-0 <?= $colorDiferenciaIva ?>">

                                <?= dinero(
        $diferenciaIva
    ) ?>

                            </h5>

                            <small class="text-muted">

                                IVA del contrato − Total IVA Facturado

                            </small>

                        </div>

                    </div>

                </div>


                <!--==================================================
    GANANCIAS
    ==================================================-->

                <div class="col-md">

                    <div class="card h-100 shadow-sm">

                        <div class="card-body">

                            <i class="bi bi-graph-up-arrow text-success"></i>

                            <small class="text-muted">

                                Ganancias

                            </small>

                            <h5 class="mb-0">

                                <?= dinero(
                        $ganancias
                    ) ?>

                            </h5>

                            <small class="text-muted">

                                Valor del contrato − Valor Facturas

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


                        <th>
                            N° Contrato
                        </th>

                        <th>
                            Valor del Contrato
                        </th>

                        <th>
                            IVA del Contrato
                        </th>

                        <th>
                            Valor Facturas
                        </th>

                        <th>
                            Total IVA Facturado
                        </th>

                        <th>
                            Acciones
                        </th>

                        </tr>


                    </thead>


                    <tbody>


                        <?php if (
                count($contratos) > 0
            ): ?>


                        <?php foreach (
                    $contratos
                    as $contrato
                ): ?>


                        <tr>


                            <!--========================================
                NÚMERO CONTRATO
                ========================================-->

                            <td>

                                <?= htmlspecialchars(
                        $contrato[
                            'numero_contrato'
                        ]
                    ) ?>

                            </td>


                            <!--========================================
                VALOR CONTRATO
                ========================================-->

                            <td class="text-end">

                                <?= dinero(
                        $contrato[
                            'valor_contrato'
                        ]
                    ) ?>

                            </td>


                            <!--========================================
                IVA CONTRATO
                ========================================-->

                            <td class="text-end">

                                <?= dinero(
                        $contrato[
                            'iva_contrato'
                        ]
                    ) ?>

                            </td>


                            <!--========================================
                VALOR FACTURAS
                ========================================-->

                            <td class="text-end">

                                <?= dinero(
                        $contrato[
                            'valor_facturas'
                        ]
                    ) ?>

                            </td>


                            <!--========================================
                TOTAL IVA FACTURADO
                ========================================-->

                            <td class="text-end">

                                <?= dinero(
                        $contrato[
                            'iva_facturado'
                        ]
                    ) ?>

                            </td>


                            <!--========================================
                ACCIONES
                ========================================-->

                            <td>

                                <a href="<?= BASE_URL ?>facturacion/ver.php?id=<?= $contrato['id'] ?>"
                                    class="btn btn-primary btn-sm d-inline-flex align-items-center gap-1"
                                    title="Ver facturas del contrato">

                                    <i class="bi bi-eye"></i>

                                    <span>Ver facturas</span>

                                </a>

                            </td>


                        </tr>


                        <?php endforeach; ?>


                        <?php else: ?>


                        <tr>

                            <td colspan="6" class="text-center text-muted py-4">

                                No hay contratos registrados
                                en este cuatrimestre.

                            </td>

                        </tr>


                        <?php endif; ?>


                    </tbody>


                    <!--==================================================
        TOTALES
        ==================================================-->

                    <?php if (
            count($contratos) > 0
        ): ?>


                    <tfoot class="table-light">


                        <tr>


                            <th>

                                TOTAL

                            </th>


                            <!-- TOTAL VALOR CONTRATOS -->

                            <th class="text-end">

                                <?= dinero(
                        $totalValorContratos
                    ) ?>

                            </th>


                            <!-- TOTAL IVA CONTRATOS -->

                            <th class="text-end">

                                <?= dinero(
                        $totalIvaContratos
                    ) ?>

                            </th>


                            <!-- TOTAL VALOR FACTURAS -->

                            <th class="text-end">

                                <?= dinero(
                        $totalValorFacturas
                    ) ?>

                            </th>


                            <!-- TOTAL IVA FACTURADO -->

                            <th class="text-end">

                                <?= dinero(
                        $totalIvaFacturado
                    ) ?>

                            </th>


                            <!-- ACCIONES -->

                            <th></th>


                        </tr>


                    </tfoot>


                    <?php endif; ?>


                </table>


            </div>


        </section>


    </div>


    <?php

include __DIR__ . '/../includes/footer.php';

include __DIR__ . '/../includes/scripts.php';

?>