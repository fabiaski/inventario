<?php

require_once __DIR__ . '/../../config/conexion.php';


//==================================================
// AÑO
//==================================================

$anio = (int) ($_GET['anio'] ?? date('Y'));


//==================================================
// CONFIGURACIÓN CUATRIMESTRES
//==================================================

$cuatrimestres = [

    1 => [
        'nombre' => 'Enero - Abril',
        'inicio' => 1,
        'fin' => 4
    ],

    2 => [
        'nombre' => 'Mayo - Agosto',
        'inicio' => 5,
        'fin' => 8
    ],

    3 => [
        'nombre' => 'Septiembre - Diciembre',
        'inicio' => 9,
        'fin' => 12
    ]

];


//==================================================
// FUNCIÓN DINERO
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
// VARIABLES DEL RESUMEN ANUAL
//==================================================

$totalContratosAnual = 0;

$totalValorContratosAnual = 0;

$totalIvaContratosAnual = 0;

$totalImpoconsumoContratosAnual = 0;

$totalRetencionContratosAnual = 0;

$totalValorFacturasAnual = 0;

$totalIvaFacturadoAnual = 0;

$totalImpoconsumoFacturadoAnual = 0;

$totalRetencionFacturadaAnual = 0;


//==================================================
// DATOS DE CADA CUATRIMESTRE
//==================================================

$datosCuatrimestres = [];


//==================================================
// RECORRER CUATRIMESTRES
//==================================================

foreach ($cuatrimestres as $numero => $periodo) {


    $fechaInicio = sprintf(
        '%04d-%02d-01',
        $anio,
        $periodo['inicio']
    );


    $fechaFin = date(
        'Y-m-t',
        strtotime(
            sprintf(
                '%04d-%02d-01',
                $anio,
                $periodo['fin']
            )
        )
    );


    //==================================================
    // CONSULTAR CONTRATOS
    //==================================================

    $sql = "
        SELECT
            id,
            valor_contrato,
            tiene_iva,
            valor_iva,
            tiene_impoconsumo,
            valor_impoconsumo,
            tiene_retencion,
            valor_retencion
        FROM contratos
        WHERE fecha BETWEEN ? AND ?
        ORDER BY id ASC
    ";


    $stmt = $conexion->prepare($sql);


    if (!$stmt) {

        die(
            'Error preparando contratos: '
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


    //==================================================
    // VARIABLES DEL CUATRIMESTRE
    //==================================================

    $cantidadContratos = 0;

    $valorContratos = 0;

    $ivaContratos = 0;

    $impoconsumoContratos = 0;

    $retencionContratos = 0;

    $valorFacturas = 0;

    $ivaFacturado = 0;

    $impoconsumoFacturado = 0;

    $retencionFacturada = 0;


    //==================================================
    // RECORRER CONTRATOS
    //==================================================

    while ($contrato = $resultado->fetch_assoc()) {


        $cantidadContratos++;


        //==================================================
        // VALOR CONTRATO
        //==================================================

        $valorContrato =
            (float) $contrato['valor_contrato'];


        $valorContratos +=
            $valorContrato;


        //==================================================
        // IVA DEL CONTRATO
        //==================================================

        if ((int) $contrato['tiene_iva'] === 1) {

            $ivaContratos +=
                (float) ($contrato['valor_iva'] ?? 0);

        }


        //==================================================
        // IMPOCONSUMO DEL CONTRATO
        //==================================================

        if (
            (int) $contrato['tiene_impoconsumo'] === 1
        ) {

            $impoconsumoContratos +=
                (float) (
                    $contrato['valor_impoconsumo'] ?? 0
                );

        }


        //==================================================
        // RETENCIÓN DEL CONTRATO
        //==================================================

        if (
            (int) $contrato['tiene_retencion'] === 1
        ) {

            $retencionContratos +=
                (float) (
                    $contrato['valor_retencion'] ?? 0
                );

        }


        //==================================================
        // CONSULTAR FACTURAS
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
        ";


        $stmtFacturas =
            $conexion->prepare($sqlFacturas);


        if (!$stmtFacturas) {

            die(
                'Error preparando facturas: '
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
            // VALOR TOTAL FACTURA
            //==============================================

            $valorFacturas +=
                (float) $factura['valor'];


            //==============================================
            // IVA FACTURADO
            //==============================================

            if ((int) $factura['tiene_iva'] === 1) {

                $ivaFacturado +=
                    (float) (
                        $factura['valor_iva'] ?? 0
                    );

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

    }


    $stmt->close();


    //==================================================
    // SALDOS
    //==================================================

    $saldoIva =
        $ivaContratos -
        $ivaFacturado;


    $saldoImpoconsumo =
        $impoconsumoContratos -
        $impoconsumoFacturado;


    $saldoRetencion =
        $retencionContratos -
        $retencionFacturada;


    //==================================================
    // SALDO TOTAL DIAN
    //==================================================

    $saldoDian =
        $saldoIva +
        $saldoImpoconsumo +
        $saldoRetencion;


    //==================================================
    // GUARDAR CUATRIMESTRE
    //==================================================

    $datosCuatrimestres[$numero] = [

        'nombre' =>
            $periodo['nombre'],

        'cantidad_contratos' =>
            $cantidadContratos,

        'valor_contratos' =>
            $valorContratos,

        'iva_contratos' =>
            $ivaContratos,

        'impoconsumo_contratos' =>
            $impoconsumoContratos,

        'retencion_contratos' =>
            $retencionContratos,

        'valor_facturas' =>
            $valorFacturas,

        'iva_facturado' =>
            $ivaFacturado,

        'impoconsumo_facturado' =>
            $impoconsumoFacturado,

        'retencion_facturada' =>
            $retencionFacturada,

        'saldo_iva' =>
            $saldoIva,

        'saldo_impoconsumo' =>
            $saldoImpoconsumo,

        'saldo_retencion' =>
            $saldoRetencion,

        'saldo_dian' =>
            $saldoDian

    ];


    //==================================================
    // ACUMULAR RESUMEN ANUAL
    //==================================================

    $totalContratosAnual +=
        $cantidadContratos;


    $totalValorContratosAnual +=
        $valorContratos;


    $totalIvaContratosAnual +=
        $ivaContratos;


    $totalImpoconsumoContratosAnual +=
        $impoconsumoContratos;


    $totalRetencionContratosAnual +=
        $retencionContratos;


    $totalValorFacturasAnual +=
        $valorFacturas;


    $totalIvaFacturadoAnual +=
        $ivaFacturado;


    $totalImpoconsumoFacturadoAnual +=
        $impoconsumoFacturado;


    $totalRetencionFacturadaAnual +=
        $retencionFacturada;

}


//==================================================
// SALDOS ANUALES
//==================================================

$totalSaldoIvaAnual =
    $totalIvaContratosAnual -
    $totalIvaFacturadoAnual;


$totalSaldoImpoconsumoAnual =
    $totalImpoconsumoContratosAnual -
    $totalImpoconsumoFacturadoAnual;


$totalSaldoRetencionAnual =
    $totalRetencionContratosAnual -
    $totalRetencionFacturadaAnual;


$totalSaldoDianAnual =
    $totalSaldoIvaAnual +
    $totalSaldoImpoconsumoAnual +
    $totalSaldoRetencionAnual;


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

                                    <i class="bi bi-bar-chart"></i>

                                    Resumen de Cuatrimestres

                                </h2>

                                <p class="text-muted mb-0">

                                    Resumen de contratos y facturación
                                    del año <?= $anio ?>.

                                </p>

                            </div>


                            <!-- CAMBIAR AÑO -->

                            <form method="GET" class="d-flex gap-2">

                                <input
                                    type="number"
                                    name="anio"
                                    value="<?= $anio ?>"
                                    min="2000"
                                    max="2100"
                                    class="form-control"
                                    style="width: 110px;"
                                >

                                <button
                                    type="submit"
                                    class="btn btn-primary"
                                >

                                    <i class="bi bi-search"></i>

                                    Ver año

                                </button>

                            </form>

                        </div>


                        <hr>


                        <!--==================================================
                        CUATRIMESTRES
                        ==================================================-->

                        <h5 class="mb-3">

                            <i class="bi bi-calendar-range"></i>

                            Cuatrimestres

                        </h5>


                        <div class="row g-4">


                            <?php foreach ($datosCuatrimestres as $numero => $datos): ?>


                                <div class="col-xl-4 col-lg-6">

                                    <div class="card h-100 shadow-sm">


                                        <!-- CABECERA -->

                                        <div class="card-header bg-light">

                                            <div class="d-flex justify-content-between align-items-center">

                                                <div>

                                                    <strong>

                                                        <?= htmlspecialchars(
                                                            $datos['nombre']
                                                        ) ?>

                                                    </strong>

                                                    <div class="text-muted small">

                                                        <?= $anio ?>

                                                    </div>

                                                </div>


                                                <span class="badge bg-primary">

                                                    <?= $datos['cantidad_contratos'] ?>

                                                    contratos

                                                </span>

                                            </div>

                                        </div>


                                        <!-- CONTENIDO -->

                                        <div class="card-body">


                                            <!-- VALOR CONTRATOS -->

                                            <div class="d-flex justify-content-between mb-3">

                                                <span class="text-muted">
                                                    Valor contratos
                                                </span>

                                                <strong>
                                                    <?= dinero($datos['valor_contratos']) ?>
                                                </strong>

                                            </div>


                                            <!-- IVA CONTRATOS -->

                                            <div class="d-flex justify-content-between mb-3">

                                                <span class="text-muted">
                                                    IVA contratos
                                                </span>

                                                <strong>
                                                    <?= dinero($datos['iva_contratos']) ?>
                                                </strong>

                                            </div>


                                            <!-- IMPOCONSUMO CONTRATOS -->

                                            <div class="d-flex justify-content-between mb-3">

                                                <span class="text-muted">
                                                    Impoconsumo contratos
                                                </span>

                                                <strong>
                                                    <?= dinero($datos['impoconsumo_contratos']) ?>
                                                </strong>

                                            </div>


                                            <!-- RETENCIÓN CONTRATOS -->

                                            <div class="d-flex justify-content-between mb-3">

                                                <span class="text-muted">
                                                    Retención contratos
                                                </span>

                                                <strong>
                                                    <?= dinero($datos['retencion_contratos']) ?>
                                                </strong>

                                            </div>


                                            <hr>


                                            <!-- VALOR FACTURAS -->

                                            <div class="d-flex justify-content-between mb-3">

                                                <span class="text-muted">
                                                    Valor facturas
                                                </span>

                                                <strong>
                                                    <?= dinero($datos['valor_facturas']) ?>
                                                </strong>

                                            </div>


                                            <!-- IVA FACTURADO -->

                                            <div class="d-flex justify-content-between mb-3">

                                                <span class="text-muted">
                                                    IVA facturado
                                                </span>

                                                <strong>
                                                    <?= dinero($datos['iva_facturado']) ?>
                                                </strong>

                                            </div>


                                            <!-- IMPOCONSUMO FACTURADO -->

                                            <div class="d-flex justify-content-between mb-3">

                                                <span class="text-muted">
                                                    Impoconsumo facturado
                                                </span>

                                                <strong>
                                                    <?= dinero($datos['impoconsumo_facturado']) ?>
                                                </strong>

                                            </div>


                                            <!-- RETENCIÓN FACTURADA -->

                                            <div class="d-flex justify-content-between mb-3">

                                                <span class="text-muted">
                                                    Retención facturada
                                                </span>

                                                <strong>
                                                    <?= dinero($datos['retencion_facturada']) ?>
                                                </strong>

                                            </div>


                                            <hr>


                                            <!-- SALDO IVA -->

                                            <div class="d-flex justify-content-between mb-2">

                                                <span class="text-muted">
                                                    Saldo IVA
                                                </span>

                                                <strong>
                                                    <?= dinero($datos['saldo_iva']) ?>
                                                </strong>

                                            </div>


                                            <!-- SALDO IMPOCONSUMO -->

                                            <div class="d-flex justify-content-between mb-2">

                                                <span class="text-muted">
                                                    Saldo Impoconsumo
                                                </span>

                                                <strong>
                                                    <?= dinero($datos['saldo_impoconsumo']) ?>
                                                </strong>

                                            </div>


                                            <!-- SALDO RETENCIÓN -->

                                            <div class="d-flex justify-content-between mb-3">

                                                <span class="text-muted">
                                                    Saldo Retención
                                                </span>

                                                <strong>
                                                    <?= dinero($datos['saldo_retencion']) ?>
                                                </strong>

                                            </div>


                                            <hr>


                                            <!-- SALDO DIAN -->

                                            <div class="d-flex justify-content-between align-items-center mb-3">

                                                <span>

                                                    <strong>
                                                        Saldo DIAN
                                                    </strong>

                                                </span>

                                                <strong>

                                                    <?= dinero(
                                                        $datos['saldo_dian']
                                                    ) ?>

                                                </strong>

                                            </div>


                                            <!-- BOTÓN -->

                                            <a
                                                href="ver_cuatrimestre.php?anio=<?= $anio ?>&cuatrimestre=<?= $numero ?>"
                                                class="btn btn-primary w-100"
                                            >

                                                <i class="bi bi-eye"></i>

                                                Ver cuatrimestre

                                            </a>


                                        </div>

                                    </div>

                                </div>


                            <?php endforeach; ?>


                        </div>

                    </div>

                </div>

            </div>

        </div>

   


<?php

include __DIR__ . '/../../includes/footer.php';

include __DIR__ . '/../../includes/scripts.php';

?>