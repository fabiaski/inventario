<?php

require_once __DIR__ . '/../config/conexion.php';


//==================================================
// VALIDAR ID
//==================================================

$contratoId = (int) ($_GET['id'] ?? 0);

if ($contratoId <= 0) {

    exit('Contrato no válido.');

}


//==================================================
// CONSULTAR CONTRATO
//==================================================

$sqlContrato = "
    SELECT
        id,
        numero_contrato,
        objeto_contrato,
        valor_contrato,
        fecha
    FROM contratos
    WHERE id = ?
";


$stmtContrato = $conexion->prepare($sqlContrato);


if (!$stmtContrato) {

    exit(
        'Error preparando consulta del contrato: '
        . $conexion->error
    );

}


$stmtContrato->bind_param(
    "i",
    $contratoId
);


$stmtContrato->execute();


$resultadoContrato =
    $stmtContrato->get_result();


$contrato =
    $resultadoContrato->fetch_assoc();


$stmtContrato->close();


if (!$contrato) {

    exit('El contrato no existe.');

}


//==================================================
// CONSULTAR FACTURAS
//==================================================

$sqlFacturas = "
    SELECT
        id,
        proveedor,
        numero_factura,
        valor,
        valor_sin_iva,
        porcentaje_iva,
        valor_iva,
        observacion
    FROM facturas
    WHERE contrato_id = ?
    ORDER BY id ASC
";


$stmtFacturas =
    $conexion->prepare($sqlFacturas);


if (!$stmtFacturas) {

    exit(
        'Error preparando consulta de facturas: '
        . $conexion->error
    );

}


$stmtFacturas->bind_param(
    "i",
    $contratoId
);


$stmtFacturas->execute();


$resultadoFacturas =
    $stmtFacturas->get_result();


$facturas = [];


while (
    $factura =
    $resultadoFacturas->fetch_assoc()
) {

    $facturas[] = $factura;

}


$stmtFacturas->close();


//==================================================
// CÁLCULOS DEL CONTRATO
//==================================================

$valorContrato =
    (float) $contrato['valor_contrato'];


// Valor del contrato sin IVA suponiendo 19%

$valorContratoSinIva =
    $valorContrato / 1.19;


// IVA del contrato

$ivaContrato =
    $valorContrato -
    $valorContratoSinIva;


//==================================================
// CÁLCULOS DE FACTURAS
//==================================================

$valorFacturas = 0;

$ivaFacturas19 = 0;


// IVA agrupado por porcentaje

$ivaPorcentaje = [];


foreach ($facturas as $factura) {


    $valorFacturas +=
        (float) $factura['valor'];


    $porcentaje =
        (int) $factura['porcentaje_iva'];


    $valorIva =
        (float) $factura['valor_iva'];


    //==============================================
    // IVA 19%
    //==============================================

    if ($porcentaje === 19) {

        $ivaFacturas19 += $valorIva;

    }


    //==============================================
    // AGRUPAR IVA
    //==============================================

    if (
        !isset(
            $ivaPorcentaje[$porcentaje]
        )
    ) {

        $ivaPorcentaje[$porcentaje] = 0;

    }


    $ivaPorcentaje[$porcentaje] +=
        $valorIva;

}


//==================================================
// SALDO DIAN
//==================================================

$saldoDian =
    $ivaContrato -
    $ivaFacturas19;


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


include __DIR__ . '/../includes/header.php';

include __DIR__ . '/../includes/sidebar.php';

?>


<div class="admin-main">

    <?php include __DIR__ . '/../includes/navbar.php'; ?>


    <section class="panel">


        <!--==================================================
        ENCABEZADO
        ==================================================-->

        <div class="panel-header">

            <div>

                <h2 class="h5 mb-1 section-title">

                    <i class="bi bi-receipt"></i>

                    Contrato #<?= htmlspecialchars(
                        $contrato['numero_contrato']
                    ) ?>

                </h2>

                <p class="text-muted mb-0">

                    Información y facturación del contrato.

                </p>

            </div>


            <div class="d-flex gap-2">


                <a href="agregar_factura.php?id=<?= $contratoId ?>" class="btn btn-success">

                    <i class="bi bi-plus-circle"></i>

                    Agregar Factura

                </a>


                <a href="index.php" class="btn btn-secondary">

                    <i class="bi bi-arrow-left"></i>

                    Volver

                </a>


            </div>

        </div>


        <hr>


        <!--==================================================
        INFORMACIÓN DEL CONTRATO
        ==================================================-->

        <div class="card shadow-sm mb-4">

            <div class="card-body">


                <div class="row g-3">


                    <!-- NUMERO -->

                    <div class="col-md-4">

                        <small class="text-muted">

                            Número de Contrato

                        </small>

                        <div class="fw-bold">

                            <?= htmlspecialchars(
                                $contrato['numero_contrato']
                            ) ?>

                        </div>

                    </div>


                    <!-- FECHA -->

                    <div class="col-md-4">

                        <small class="text-muted">

                            Fecha

                        </small>

                        <div class="fw-bold">

                            <?= date(
                                'd/m/Y',
                                strtotime(
                                    $contrato['fecha']
                                )
                            ) ?>

                        </div>

                    </div>


                    <!-- VALOR -->

                    <div class="col-md-4">

                        <small class="text-muted">

                            Valor del Contrato

                        </small>

                        <div class="fw-bold">

                            <?= dinero(
                                $valorContrato
                            ) ?>

                        </div>

                    </div>


                    <!-- OBJETO -->

                    <div class="col-12">

                        <small class="text-muted">

                            Objeto del Contrato

                        </small>

                        <div>

                            <?= nl2br(
                                htmlspecialchars(
                                    $contrato['objeto_contrato']
                                )
                            ) ?>

                        </div>

                    </div>


                </div>


            </div>

        </div>



        <!--==================================================
        RESUMEN
        ==================================================-->

        <div class="card shadow-sm mb-4">

            <div class="card-header">

                <strong>

                    <i class="bi bi-calculator"></i>

                    Resumen

                </strong>

            </div>


            <div class="card-body">


                <div class="row g-3">


                    <!-- VALOR CONTRATO -->

                    <div class="col-md-4">

                        <div class="border rounded p-3">

                            <small class="text-muted">

                                Valor del Contrato

                            </small>

                            <h5 class="mb-0">

                                <?= dinero(
                                    $valorContrato
                                ) ?>

                            </h5>

                        </div>

                    </div>


                    <!-- CONTRATO SIN IVA -->

                    <div class="col-md-4">

                        <div class="border rounded p-3">

                            <small class="text-muted">

                                Valor del Contrato con 19%

                            </small>

                            <h5 class="mb-0">

                                <?= dinero(
                                    $valorContratoSinIva
                                ) ?>

                            </h5>

                        </div>

                    </div>


                    <!-- IVA CONTRATO -->

                    <div class="col-md-4">

                        <div class="border rounded p-3">

                            <small class="text-muted">

                                IVA del Contrato

                            </small>

                            <h5 class="mb-0">

                                <?= dinero(
                                    $ivaContrato
                                ) ?>

                            </h5>

                        </div>

                    </div>


                    <!-- VALOR FACTURAS -->

                    <div class="col-md-4">

                        <div class="border rounded p-3">

                            <small class="text-muted">

                                Valor Facturas

                            </small>

                            <h5 class="mb-0">

                                <?= dinero(
                                    $valorFacturas
                                ) ?>

                            </h5>

                        </div>

                    </div>


                    <!-- IVA 19 -->

                    <div class="col-md-4">

                        <div class="border rounded p-3">

                            <small class="text-muted">

                                IVA de Facturas 19%

                            </small>

                            <h5 class="mb-0">

                                <?= dinero(
                                    $ivaFacturas19
                                ) ?>

                            </h5>

                        </div>

                    </div>


                    <!-- SALDO DIAN -->

                    <div class="col-md-4">

                        <div class="border rounded p-3">

                            <small class="text-muted">

                                Saldo DIAN

                            </small>

                            <h5 class="mb-0">

                                <?= dinero(
                                    $saldoDian
                                ) ?>

                            </h5>

                        </div>

                    </div>


                </div>


                <!--==================================================
                IVA POR PORCENTAJE
                ==================================================-->

                <?php if (!empty($ivaPorcentaje)): ?>

                <hr>

                <h6>

                    IVA de Facturas

                </h6>


                <div class="row g-3">


                    <?php

                        ksort(
                            $ivaPorcentaje,
                            SORT_NUMERIC
                        );

                        ?>


                    <?php foreach (
                            $ivaPorcentaje
                            as $porcentaje => $valor
                        ): ?>


                    <div class="col-md-4">

                        <div class="border rounded p-3">

                            <small class="text-muted">

                                IVA de Facturas
                                <?= $porcentaje ?>%

                            </small>

                            <h5 class="mb-0">

                                <?= dinero(
                                            $valor
                                        ) ?>

                            </h5>

                        </div>

                    </div>


                    <?php endforeach; ?>


                </div>

                <?php endif; ?>


            </div>

        </div>


        <!--==================================================
        FACTURAS
        ==================================================-->

        <div class="card shadow-sm">


            <div class="card-header">

                <strong>

                    <i class="bi bi-file-earmark-text"></i>

                    Facturas

                </strong>

            </div>


            <div class="card-body">


                <div class="table-responsive">


                    <table class="table table-bordered table-hover align-middle">


                        <thead class="table-light">

                            <tr>

                                <th>#</th>

                                <th>Proveedor</th>

                                <th>N° de Factura</th>

                                <th>Valor</th>

                                <th>Valor sin IVA</th>

                                <th>% IVA</th>

                                <th>IVA</th>

                                <th>Observación</th>

                                <th>Acciones</th>

                            </tr>

                        </thead>


                        <tbody>


                            <?php if (!empty($facturas)): ?>


                            <?php

                                $numero = 1;

                                ?>


                            <?php foreach (
                                    $facturas
                                    as $factura
                                ): ?>


                            <tr>


                                <td>

                                    <?= $numero++ ?>

                                </td>


                                <td>

                                    <?= htmlspecialchars(
                                                $factura['proveedor']
                                            ) ?>

                                </td>


                                <td>

                                    <?= htmlspecialchars(
                                                $factura['numero_factura']
                                            ) ?>

                                </td>


                                <td class="text-end">

                                    <?= dinero(
                                                $factura['valor']
                                            ) ?>

                                </td>


                                <td class="text-end">

                                    <?= dinero(
                                                $factura['valor_sin_iva']
                                            ) ?>

                                </td>


                                <td class="text-center">

                                    <?= (int)
                                                $factura[
                                                    'porcentaje_iva'
                                                ] ?>%

                                </td>


                                <td class="text-end">

                                    <?= dinero(
                                                $factura['valor_iva']
                                            ) ?>

                                </td>


                                <td>

                                    <?= htmlspecialchars(
                                                $factura['observacion'] ?? ''
                                            ) ?>

                                </td>


                                <td>

                                    <div class="d-flex gap-1">


                                        <a href="editar_factura.php?id=<?= $factura['id'] ?>"
                                            class="btn btn-warning btn-sm" title="Editar">

                                            <i class="bi bi-pencil"></i>

                                        </a>


                                        <a href="eliminar_factura.php?id=<?= $factura['id'] ?>"
                                            class="btn btn-danger btn-sm" title="Eliminar" onclick="
                                                        return confirm(
                                                            '¿Está seguro de eliminar esta factura?'
                                                        );
                                                    ">

                                            <i class="bi bi-trash"></i>

                                        </a>


                                    </div>

                                </td>


                            </tr>


                            <?php endforeach; ?>


                            <?php else: ?>


                            <tr>

                                <td colspan="9" class="text-center text-muted py-4">

                                    <i class="bi bi-file-earmark-x fs-3 d-block mb-2"></i>

                                    Este contrato todavía no tiene facturas.

                                </td>

                            </tr>


                            <?php endif; ?>


                        </tbody>


                    </table>

                </div>


            </div>

        </div>


    </section>

</div>


<?php

include __DIR__ . '/../includes/footer.php';

include __DIR__ . '/../includes/scripts.php';

?>