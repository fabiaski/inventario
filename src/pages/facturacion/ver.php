<?php

require_once __DIR__ . '/../../config/conexion.php';


//==================================================
// VALIDAR ID DEL CONTRATO
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
// CONSULTAR SOPORTES
//==================================================

$soportesPorFactura = [];


if (!empty($facturas)) {

    $idsFacturas = [];


    foreach ($facturas as $factura) {

        $idsFacturas[] =
            (int) $factura['id'];

    }


    $placeholders =
        implode(
            ',',
            array_fill(
                0,
                count($idsFacturas),
                '?'
            )
        );


    $tipos =
        str_repeat(
            'i',
            count($idsFacturas)
        );


    $sqlSoportes = "
        SELECT
            id,
            factura_id,
            archivo,
            tipo_archivo
        FROM soportes_factura
        WHERE factura_id IN ($placeholders)
        ORDER BY id ASC
    ";


    $stmtSoportes =
        $conexion->prepare(
            $sqlSoportes
        );


    if ($stmtSoportes) {

        $stmtSoportes->bind_param(
            $tipos,
            ...$idsFacturas
        );


        $stmtSoportes->execute();


        $resultadoSoportes =
            $stmtSoportes->get_result();


        while (
            $soporte =
            $resultadoSoportes->fetch_assoc()
        ) {

            $facturaSoporteId =
                (int) $soporte['factura_id'];


            if (
                !isset(
                    $soportesPorFactura[
                        $facturaSoporteId
                    ]
                )
            ) {

                $soportesPorFactura[
                    $facturaSoporteId
                ] = [];

            }


            $soportesPorFactura[
                $facturaSoporteId
            ][] = $soporte;

        }


        $stmtSoportes->close();

    }

}


//==================================================
// FUNCIONES
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


function porcentaje($valor)
{
    $valor = (float) $valor;

    if ($valor == floor($valor)) {

        return number_format(
            $valor,
            0,
            ',',
            '.'
        ) . '%';

    }

    return number_format(
        $valor,
        2,
        ',',
        '.'
    ) . '%';
}


//==================================================
// CÁLCULOS
//==================================================

$valorContrato =
    (float) $contrato['valor_contrato'];


// Valor del contrato con IVA 19%
// Según lo definido:
// valor contrato / 1.19

$valorContratoSinIva =
    $valorContrato / 1.19;


// IVA incluido en el contrato

$ivaContrato =
    $valorContrato
    - $valorContratoSinIva;


// Total de facturas

$totalFacturas = 0;


// IVA agrupado por porcentaje

$ivaPorcentaje = [];


foreach ($facturas as $factura) {

    $valorFactura =
        (float) $factura['valor'];

    $ivaFactura =
        (float) $factura['valor_iva'];

    $porcentajeIva =
        (int) $factura['porcentaje_iva'];


    $totalFacturas +=
        $valorFactura;


    if (
        !isset(
            $ivaPorcentaje[
                $porcentajeIva
            ]
        )
    ) {

        $ivaPorcentaje[
            $porcentajeIva
        ] = 0;

    }


    $ivaPorcentaje[
        $porcentajeIva
    ] += $ivaFactura;

}


// IVA TOTAL DE TODAS LAS FACTURAS

$totalIvaFacturado = array_sum(
    $ivaPorcentaje
);


// IVA 19%

$ivaFacturas19 =
    $ivaPorcentaje[19] ?? 0;


// SALDO DIAN
// Se mantiene únicamente con el IVA del 19%

$saldoDian =
    $ivaContrato
    - $totalIvaFacturado;
// Ordenar porcentajes

ksort($ivaPorcentaje);





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

                                <h2 class=" mb-1 section-title">
                                    <i class="bi bi-receipt"></i>
                                    Facturación
                                </h2>

                                <p class="text-muted mb-0">
                                    Información y facturas del contrato.
                                </p>


                            </div>


                            <div class="d-flex gap-2">

                                <a href="facturacion.php" class="btn btn-secondary">

                                    <i class="bi bi-arrow-left"></i>

                                    Volver

                                </a>


                                <a href="agregar_factura.php?id=<?= $contratoId ?>" class="btn btn-success">

                                    <i class="bi bi-plus-circle"></i>

                                    Agregar Factura

                                </a>

                            </div>

                        </div>


                        <hr>


                        <div class="card shadow-sm mb-4">

                            <div class="card-body">


                                <div class="row g-4">


                                    <div class="col-md-4">

                                        <div class="border rounded p-3">
                                            <small class="text-muted">

                                                Número de Contrato

                                            </small>

                                            <div class="fw-bold">

                                                <?= htmlspecialchars(
                                $contrato['numero_contrato']
                            ) ?>

                                            </div>

                                        </div>

                                    </div>

                                    <!-- FECHA -->

                                    <div class="col-md-4">
                                        <div class="border rounded p-3">

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
                                    </div>


                                    <!-- VALOR -->

                                    <div class="col-md-4">
                                        <div class="border rounded p-3">

                                        <small class="text-muted">

                                            Valor del Contrato

                                        </small>

                                        <div class="fw-bold">

                                            <?= dinero(
                                $valorContrato
                            ) ?>

                                        </div>

                                    </div>
                                    </div>


                                    <!-- OBJETO -->

                                    <div class="col-12">

                                                                            <div class="border rounded p-3">

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

                                                Valor del Contrato sin IVA 19%

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
                                    $totalFacturas
                                ) ?>

                                            </h5>

                                        </div>

                                    </div>

                                    <!-- TOTAL IVA FACTURADO -->

                                    <div class="col-md-4">

                                        <div class="border rounded p-3">

                                            <small class="text-muted">

                                                Total de IVA Facturado

                                            </small>

                                            <h5 class="mb-0">

                                                <?= dinero(
                $totalIvaFacturado
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

                        <div class="card">

                            <div class="card-header">

                                <strong>

                                    <i class="bi bi-receipt-cutoff"></i>

                                    Facturas

                                </strong>

                            </div>


                            <div class="card-body">


                                <?php if (!empty($facturas)): ?>


                                <div class="table-responsive">

                                    <table class="table table-bordered table-hover align-middle">


                                        <thead class="table-light">

                                            <tr>

                                                <th>
                                                    #
                                                </th>

                                                <th>
                                                    Proveedor
                                                </th>

                                                <th>
                                                    N° Factura
                                                </th>

                                                <th>
                                                    Valor
                                                </th>

                                                <th>
                                                    Valor sin IVA
                                                </th>

                                                <th>
                                                    % IVA
                                                </th>

                                                <th>
                                                    IVA
                                                </th>

                                                <th>
                                                    Observación
                                                </th>

                                                <th>
                                                    Soportes
                                                </th>

                                                <th>
                                                    Acciones
                                                </th>

                                            </tr>

                                        </thead>


                                        <tbody>


                                            <?php foreach (
                                    $facturas
                                    as $indice => $factura
                                ): ?>


                                            <tr>


                                                <!-- # -->

                                                <td>

                                                    <?= $indice + 1 ?>

                                                </td>


                                                <!-- PROVEEDOR -->

                                                <td>

                                                    <?= htmlspecialchars(
                                                $factura[
                                                    'proveedor'
                                                ]
                                            ) ?>

                                                </td>


                                                <!-- NÚMERO -->

                                                <td>

                                                    <?= htmlspecialchars(
                                                $factura[
                                                    'numero_factura'
                                                ]
                                            ) ?>

                                                </td>


                                                <!-- VALOR -->

                                                <td class="text-end">

                                                    <?= dinero(
                                                $factura[
                                                    'valor'
                                                ]
                                            ) ?>

                                                </td>


                                                <!-- SIN IVA -->

                                                <td class="text-end">

                                                    <?= dinero(
                                                $factura[
                                                    'valor_sin_iva'
                                                ]
                                            ) ?>

                                                </td>


                                                <!-- IVA -->

                                                <td class="text-center">

                                                    <?= porcentaje(
                                                $factura[
                                                    'porcentaje_iva'
                                                ]
                                            ) ?>

                                                </td>


                                                <!-- VALOR IVA -->

                                                <td class="text-end">

                                                    <?= dinero(
                                                $factura[
                                                    'valor_iva'
                                                ]
                                            ) ?>

                                                </td>


                                                <!-- OBSERVACIÓN -->

                                                <td>

                                                    <?= !empty(
                                                $factura[
                                                    'observacion'
                                                ]
                                            )
                                                ? htmlspecialchars(
                                                    $factura[
                                                        'observacion'
                                                    ]
                                                )
                                                : '<span class="text-muted">—</span>'
                                            ?>

                                                </td>


                                                <!-- SOPORTES -->

                                                <td>

                                                    <?php

                                            $idFactura =
                                                (int) $factura['id'];

                                            $soportes =
                                                $soportesPorFactura[
                                                    $idFactura
                                                ] ?? [];

                                            ?>


                                                    <?php if (
                                                !empty(
                                                    $soportes
                                                )
                                            ): ?>


                                                    <div class="d-flex flex-column gap-1">

                                                        <?php foreach (
                                                        $soportes
                                                        as $soporte
                                                    ): ?>


                                                        <a href="../uploads/soportes_facturas/<?= rawurlencode(
                                                                $soporte[
                                                                    'archivo'
                                                                ]
                                                            ) ?>" target="_blank" class="text-decoration-none"
                                                            title="Abrir soporte">

                                                            <?php

                                                            $extension =
                                                                strtolower(
                                                                    pathinfo(
                                                                        $soporte[
                                                                            'archivo'
                                                                        ],
                                                                        PATHINFO_EXTENSION
                                                                    )
                                                                );

                                                            ?>


                                                            <?php if (
                                                                $extension === 'pdf'
                                                            ): ?>

                                                            <i class="bi bi-file-earmark-pdf text-danger"></i>

                                                            <?php else: ?>

                                                            <i class="bi bi-file-earmark-image text-primary"></i>

                                                            <?php endif; ?>


                                                            <?= htmlspecialchars(
                                                                $soporte[
                                                                    'archivo'
                                                                ]
                                                            ) ?>

                                                        </a>


                                                        <?php endforeach; ?>

                                                    </div>


                                                    <?php else: ?>


                                                    <span class="text-muted">

                                                        Sin soporte

                                                    </span>


                                                    <?php endif; ?>


                                                </td>


                                                <!-- ACCIONES -->

                                                <td>

                                                    <div class="d-flex gap-1">


                                                        <!-- EDITAR -->

                                                        <a href="editar_factura.php?id=<?= $factura['id'] ?>"
                                                            class="btn btn-warning btn-sm" title="Editar factura">

                                                            <i class="bi bi-pencil"></i>

                                                        </a>


                                                        <!-- ELIMINAR -->

                                                        <a href="eliminar_factura.php?id=<?= $factura['id'] ?>"
                                                            class="btn btn-danger btn-sm" title="Eliminar factura"
                                                            onclick="
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


                                        </tbody>


                                    </table>

                                </div>


                                <?php else: ?>


                                <div class="text-center text-muted py-4">

                                    <i class="bi bi-receipt fs-3"></i>

                                    <p class="mb-0 mt-2">

                                        Este contrato todavía
                                        no tiene facturas.

                                    </p>

                                </div>


                                <?php endif; ?>


                            </div>

                        </div>


                        </section>

                    </div>


                    <?php

include __DIR__ . '/../../includes/footer.php';

include __DIR__ . '/../../includes/scripts.php';

?>