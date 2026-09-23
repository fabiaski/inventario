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
        tiene_iva,
        valor_iva,
        tiene_impoconsumo,
        valor_impoconsumo,
        tiene_retencion,
        valor_retencion,
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
        tiene_iva,
        valor_iva,
        tiene_impoconsumo,
        valor_impoconsumo,
        tiene_retencion,
        valor_retencion,
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


//==================================================
// CÁLCULOS
//==================================================

$valorContrato =
    (float) $contrato['valor_contrato'];


//==================================================
// VALORES TRIBUTARIOS DEL CONTRATO
//==================================================

$ivaContrato = 0;
$impoconsumoContrato = 0;
$retencionContrato = 0;

if ((int) $contrato['tiene_iva'] === 1) {

    $ivaContrato =
        (float) ($contrato['valor_iva'] ?? 0);
}

if ((int) $contrato['tiene_impoconsumo'] === 1) {

    $impoconsumoContrato =
        (float) ($contrato['valor_impoconsumo'] ?? 0);
}

if ((int) $contrato['tiene_retencion'] === 1) {

    $retencionContrato =
        (float) ($contrato['valor_retencion'] ?? 0);
}


//==================================================
// TOTALES DE FACTURAS
//==================================================

$totalFacturas = 0;
$totalIvaFacturado = 0;
$totalImpoconsumoFacturado = 0;
$totalRetencionFacturada = 0;

foreach ($facturas as $factura) {

    $totalFacturas +=
        (float) $factura['valor'];

    if ((int) $factura['tiene_iva'] === 1) {

        $totalIvaFacturado +=
            (float) ($factura['valor_iva'] ?? 0);
    }

    if ((int) $factura['tiene_impoconsumo'] === 1) {

        $totalImpoconsumoFacturado +=
            (float) ($factura['valor_impoconsumo'] ?? 0);
    }

    if ((int) $factura['tiene_retencion'] === 1) {

        $totalRetencionFacturada +=
            (float) ($factura['valor_retencion'] ?? 0);
    }
}


//==================================================
// SALDOS
//==================================================

$saldoIva =
    $ivaContrato - $totalIvaFacturado;

$saldoImpoconsumo =
    $impoconsumoContrato - $totalImpoconsumoFacturado;

$saldoRetencion =
    $retencionContrato - $totalRetencionFacturada;


//==================================================
// INCLUDES
//==================================================

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


                        <!--==================================================
                        ENCABEZADO
                        ==================================================-->

                        <div class="panel-header d-flex justify-content-between align-items-center">

                            <div>

                                <h2 class="mb-1 section-title">

                                    <i class="bi bi-receipt"></i>

                                    Facturación

                                </h2>

                                <p class="text-muted mb-0">

                                    Información y facturas del contrato.

                                </p>

                            </div>


                            <div class="d-flex gap-2">

                                <a href="facturacion.php"
                                    class="btn btn-secondary">

                                    <i class="bi bi-arrow-left"></i>

                                    Volver

                                </a>


                                <a href="agregar_factura.php?id=<?= $contratoId ?>"
                                    class="btn btn-success">

                                    <i class="bi bi-plus-circle"></i>

                                    Agregar Factura

                                </a>

                            </div>

                        </div>


                        <hr>


                        <!--==================================================
                        INFORMACIÓN DEL CONTRATO
                        ==================================================-->

                        <div class="card shadow-sm mb-4">

                            <div class="card-body">

                                <div class="row g-4">


                                    <!-- NÚMERO DE CONTRATO -->

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
                        INFORMACIÓN TRIBUTARIA DEL CONTRATO
                        ==================================================-->

                        <div class="card shadow-sm mb-4">

                            <div class="card-header">

                                <strong>

                                    <i class="bi bi-percent"></i>

                                    Información tributaria del contrato

                                </strong>

                            </div>


                            <div class="card-body">

                                <div class="row g-3">


                                    <!-- IVA -->

                                    <div class="col-md-4">

                                        <div class="border rounded p-3">

                                            <small class="text-muted">

                                                IVA

                                            </small>

                                            <h5 class="mb-0">

                                                <?= (int) $contrato['tiene_iva'] === 1
                                                    ? dinero($ivaContrato)
                                                    : 'No aplica' ?>

                                            </h5>

                                        </div>

                                    </div>


                                    <!-- IMPOCONSUMO -->

                                    <div class="col-md-4">

                                        <div class="border rounded p-3">

                                            <small class="text-muted">

                                                Impoconsumo

                                            </small>

                                            <h5 class="mb-0">

                                                <?= (int) $contrato['tiene_impoconsumo'] === 1
                                                    ? dinero($impoconsumoContrato)
                                                    : 'No aplica' ?>

                                            </h5>

                                        </div>

                                    </div>


                                    <!-- RETENCIÓN -->

                                    <div class="col-md-4">

                                        <div class="border rounded p-3">

                                            <small class="text-muted">

                                                Retención

                                            </small>

                                            <h5 class="mb-0">

                                                <?= (int) $contrato['tiene_retencion'] === 1
                                                    ? dinero($retencionContrato)
                                                    : 'No aplica' ?>

                                            </h5>

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


                                    <!-- SALDO CONTRATO -->

                                    <div class="col-md-4">

                                        <div class="border rounded p-3">

                                            <small class="text-muted">

                                                Saldo del Contrato

                                            </small>

                                            <h5 class="mb-0">

                                                <?= dinero(
                                                    $valorContrato - $totalFacturas
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

                                                <?= (int) $contrato['tiene_iva'] === 1
                                                    ? dinero($ivaContrato)
                                                    : 'No aplica' ?>

                                            </h5>

                                        </div>

                                    </div>


                                    <!-- IVA FACTURADO -->

                                    <div class="col-md-4">

                                        <div class="border rounded p-3">

                                            <small class="text-muted">

                                                IVA Facturado

                                            </small>

                                            <h5 class="mb-0">

                                                <?= dinero(
                                                    $totalIvaFacturado
                                                ) ?>

                                            </h5>

                                        </div>

                                    </div>


                                    <!-- SALDO IVA -->

                                    <div class="col-md-4">

                                        <div class="border rounded p-3">

                                            <small class="text-muted">

                                                Saldo IVA

                                            </small>

                                            <h5 class="mb-0">

                                                <?= dinero(
                                                    $saldoIva
                                                ) ?>

                                            </h5>

                                        </div>

                                    </div>


                                    <!-- IMPOCONSUMO CONTRATO -->

                                    <div class="col-md-4">

                                        <div class="border rounded p-3">

                                            <small class="text-muted">

                                                Impoconsumo del Contrato

                                            </small>

                                            <h5 class="mb-0">

                                                <?= (int) $contrato['tiene_impoconsumo'] === 1
                                                    ? dinero($impoconsumoContrato)
                                                    : 'No aplica' ?>

                                            </h5>

                                        </div>

                                    </div>


                                    <!-- IMPOCONSUMO FACTURADO -->

                                    <div class="col-md-4">

                                        <div class="border rounded p-3">

                                            <small class="text-muted">

                                                Impoconsumo Facturado

                                            </small>

                                            <h5 class="mb-0">

                                                <?= dinero(
                                                    $totalImpoconsumoFacturado
                                                ) ?>

                                            </h5>

                                        </div>

                                    </div>


                                    <!-- SALDO IMPOCONSUMO -->

                                    <div class="col-md-4">

                                        <div class="border rounded p-3">

                                            <small class="text-muted">

                                                Saldo Impoconsumo

                                            </small>

                                            <h5 class="mb-0">

                                                <?= dinero(
                                                    $saldoImpoconsumo
                                                ) ?>

                                            </h5>

                                        </div>

                                    </div>


                                    <!-- RETENCIÓN CONTRATO -->

                                    <div class="col-md-4">

                                        <div class="border rounded p-3">

                                            <small class="text-muted">

                                                Retención del Contrato

                                            </small>

                                            <h5 class="mb-0">

                                                <?= (int) $contrato['tiene_retencion'] === 1
                                                    ? dinero($retencionContrato)
                                                    : 'No aplica' ?>

                                            </h5>

                                        </div>

                                    </div>


                                    <!-- RETENCIÓN FACTURADA -->

                                    <div class="col-md-4">

                                        <div class="border rounded p-3">

                                            <small class="text-muted">

                                                Retención Facturada

                                            </small>

                                            <h5 class="mb-0">

                                                <?= dinero(
                                                    $totalRetencionFacturada
                                                ) ?>

                                            </h5>

                                        </div>

                                    </div>


                                    <!-- SALDO RETENCIÓN -->

                                    <div class="col-md-4">

                                        <div class="border rounded p-3">

                                            <small class="text-muted">

                                                Saldo Retención

                                            </small>

                                            <h5 class="mb-0">

                                                <?= dinero(
                                                    $saldoRetencion
                                                ) ?>

                                            </h5>

                                        </div>

                                    </div>


                                </div>

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
                                                    IVA
                                                </th>

                                                <th>
                                                    Impoconsumo
                                                </th>

                                                <th>
                                                    Retención
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
                                                        $factura['proveedor']
                                                    ) ?>

                                                </td>


                                                <!-- NÚMERO -->

                                                <td>

                                                    <?= htmlspecialchars(
                                                        $factura['numero_factura']
                                                    ) ?>

                                                </td>


                                                <!-- VALOR -->

                                                <td class="text-end">

                                                    <?= dinero(
                                                        $factura['valor']
                                                    ) ?>

                                                </td>


                                                <!-- IVA -->

                                                <td class="text-end">

                                                    <?= (int) $factura['tiene_iva'] === 1
                                                        ? dinero($factura['valor_iva'])
                                                        : 'No aplica' ?>

                                                </td>


                                                <!-- IMPOCONSUMO -->

                                                <td class="text-end">

                                                    <?= (int) $factura['tiene_impoconsumo'] === 1
                                                        ? dinero($factura['valor_impoconsumo'])
                                                        : 'No aplica' ?>

                                                </td>


                                                <!-- RETENCIÓN -->

                                                <td class="text-end">

                                                    <?= (int) $factura['tiene_retencion'] === 1
                                                        ? dinero($factura['valor_retencion'])
                                                        : 'No aplica' ?>

                                                </td>


                                                <!-- OBSERVACIÓN -->

                                                <td>

                                                    <?= !empty(
                                                        $factura['observacion']
                                                    )
                                                        ? htmlspecialchars(
                                                            $factura['observacion']
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
                                                        !empty($soportes)
                                                    ): ?>


                                                    <div class="d-flex flex-column gap-1">

                                                        <?php foreach (
                                                            $soportes
                                                            as $soporte
                                                        ): ?>


                                                        <?php

                                                        $extension =
                                                            strtolower(
                                                                pathinfo(
                                                                    $soporte['archivo'],
                                                                    PATHINFO_EXTENSION
                                                                )
                                                            );

                                                        ?>


                                                        <a href="../uploads/soportes_facturas/<?= rawurlencode(
                                                                $soporte['archivo']
                                                            ) ?>"
                                                            target="_blank"
                                                            class="text-decoration-none"
                                                            title="Abrir soporte">


                                                            <?php if (
                                                                $extension === 'pdf'
                                                            ): ?>

                                                            <i class="bi bi-file-earmark-pdf text-danger"></i>

                                                            <?php else: ?>

                                                            <i class="bi bi-file-earmark-image text-primary"></i>

                                                            <?php endif; ?>


                                                            <?= htmlspecialchars(
                                                                $soporte['archivo']
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
                                                            class="btn btn-warning btn-sm"
                                                            title="Editar factura">

                                                            <i class="bi bi-pencil"></i>

                                                        </a>


                                                        <!-- ELIMINAR -->

                                                        <a href="eliminar_factura.php?id=<?= $factura['id'] ?>"
                                                            class="btn btn-danger btn-sm"
                                                            title="Eliminar factura"
                                                            onclick="return confirm('¿Está seguro de eliminar esta factura?');">

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


                    </div>

                </div>

            </div>

        </div>

    </div>


    <?php

    include __DIR__ . '/../../includes/footer.php';

    include __DIR__ . '/../../includes/scripts.php';

    ?>

</div>