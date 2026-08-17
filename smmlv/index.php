<?php

require_once __DIR__ . '/../config/conexion.php';


//==================================================
// CONFIGURACIÓN
//==================================================

$titulo = "SMMLV - Contratos";


//==================================================
// OBTENER AÑO ACTUAL
//==================================================

$anioActual = (int) date('Y');


//==================================================
// OBTENER SALARIO MÍNIMO DEL AÑO ACTUAL
//==================================================

$salarioActualValor = 0;

$sqlSalarioActual = "
    SELECT salario
    FROM salarios_minimos
    WHERE anio = ?
    LIMIT 1
";

$stmtSalarioActual =
    $conexion->prepare($sqlSalarioActual);

if (!$stmtSalarioActual) {

    die(
        'Error preparando consulta de salario mínimo: '
        . $conexion->error
    );

}

$stmtSalarioActual->bind_param(
    "i",
    $anioActual
);

$stmtSalarioActual->execute();

$resultadoSalarioActual =
    $stmtSalarioActual->get_result();

$salarioActual =
    $resultadoSalarioActual->fetch_assoc();

if ($salarioActual) {

    $salarioActualValor =
        (float) $salarioActual['salario'];

}

$stmtSalarioActual->close();


//==================================================
// CONSULTAR CONTRATOS
//==================================================

$buscar = trim($_GET['buscar'] ?? '');


$sqlContratos = "
    SELECT
        c.id,
        c.numero_contrato,
        c.entidad,
        c.objeto,
        c.valor,
        c.anio,
        s.salario
    FROM contratos_smlmv c

    LEFT JOIN salarios_minimos s
        ON s.anio = c.anio
";


if ($buscar !== '') {

    $sqlContratos .= "
        WHERE c.objeto LIKE ?
    ";

}


$sqlContratos .= "
    ORDER BY
        c.anio DESC,
        c.id DESC
";

if ($buscar !== '') {

    $stmtContratos =
        $conexion->prepare($sqlContratos);


    if (!$stmtContratos) {

        die(
            'Error preparando consulta de contratos: '
            . $conexion->error
        );

    }


    $terminoBusqueda =
        '%' . $buscar . '%';


    $stmtContratos->bind_param(
        "s",
        $terminoBusqueda
    );


    $stmtContratos->execute();


    $resultadoContratos =
        $stmtContratos->get_result();

} else {

    $resultadoContratos =
        $conexion->query($sqlContratos);

}

if (!$resultadoContratos) {

    die(
        'Error consultando contratos: '
        . $conexion->error
    );

}


$contratos = [];


//==================================================
// RECORRER CONTRATOS
//==================================================

while (
    $contrato =
    $resultadoContratos->fetch_assoc()
) {


    //==================================================
    // VALOR CONTRATO
    //==================================================

    $valorContrato =
        (float) $contrato['valor'];


    //==================================================
    // SALARIO MÍNIMO DEL AÑO DEL CONTRATO
    //==================================================

    $salarioAnio =
        (float) ($contrato['salario'] ?? 0);


    //==================================================
    // SMMLV %
    //
    // Valor contrato / salario mínimo del año
    //==================================================

    if ($salarioAnio > 0) {

        $smmlvPorcentaje =
            $valorContrato / $salarioAnio;

    } else {

        $smmlvPorcentaje = 0;

    }


    //==================================================
    // SMMLV
    //
    // SMMLV % × salario mínimo del año actual
    //==================================================

    $smmlvActual =
        $smmlvPorcentaje * $salarioActualValor;


    //==================================================
    // GUARDAR DATOS CALCULADOS
    //==================================================

    $contrato['salario_anio'] =
        $salarioAnio;

    $contrato['smmlv_porcentaje'] =
        $smmlvPorcentaje;

    $contrato['smmlv_actual'] =
        $smmlvActual;


    $contratos[] =
        $contrato;

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


function numero($valor)
{

    return number_format(
        (float) $valor,
        2,
        ',',
        '.'
    );

}


//==================================================
// TOTALES
//==================================================

$totalContratos =
    count($contratos);

$totalValor =
    0;

$totalSmmlv =
    0;


foreach (
    $contratos
    as $contrato
) {

    $totalValor +=
        (float) $contrato['valor'];

    $totalSmmlv +=
        (float) $contrato['smmlv_actual'];

}


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

                        <i class="bi bi-calculator"></i>

                        Contratos SMMLV

                    </h2>


                    <p class="text-muted mb-0">

                        Conversión de contratos a salarios mínimos
                        según el año correspondiente.

                    </p>

                     <small class="text-muted">
                                <i class="bi bi-file-earmark-text text-primary"></i>

                                Total contratos = <strong> <?= $totalContratos ?></strong>

                            </small>

                            <h4 class="mb-0">


                            </h4>
                    <small class="text-muted">
                        <i class="bi bi-currency-dollar text-warning"></i>

                        Salario mínimo <?= $anioActual ?> = <strong> <?= dinero($salarioActualValor) ?></strong>
                    </small>

                    <h5 class="mb-0">


                    </h5>
                </div>


                <div>

                    <a href="<?= BASE_URL ?>smmlv/agregar.php" class="btn btn-primary">

                        <i class="bi bi-plus-circle"></i>

                        Nuevo contrato

                    </a>

                </div>


            </div>


            <hr>


<!--==================================================
BUSCADOR POR OBJETO
==================================================-->

<form method="GET" class="mb-4">

    <div class="row g-2">

        <div class="col-md-8">

            <div class="input-group">

              

                <input
                    type="text"
                    name="buscar"
                    class="form-control"
                    placeholder="Buscar por objeto del contrato..."
                    value="<?= htmlspecialchars($_GET['buscar'] ?? '') ?>"
                >

            </div>

        </div>


        <div class="col-md-auto">

            <button
                type="submit"
                class="btn btn-primary"
            >

                <i class="bi bi-search"></i>

                Buscar

            </button>

        </div>


        <?php if (!empty($_GET['buscar'])): ?>

            <div class="col-md-auto">

                <a
                    href="<?= BASE_URL ?>smmlv/index.php"
                    class="btn btn-secondary"
                >

                    <i class="bi bi-x-circle"></i>

                    Limpiar

                </a>

            </div>

        <?php endif; ?>


    </div>

</form>

            <!--==================================================
        TABLA
        ==================================================-->

            <div class="table-responsive">


                <table class="table align-middle mb-0">


                    <thead>

                        <tr>

                            <th>
                                No. CONTRATO
                            </th>

                            <th>
                                ENTIDAD
                            </th>

                            <th>
                                OBJETO
                            </th>

                            <th>
                                VALOR
                            </th>

                            <th>
                                AÑO
                            </th>

                            <th>
                                SMMLV %
                            </th>

                            <th>
                                SMMLV
                            </th>

                            <th>
                                ACCIONES
                            </th>

                        </tr>

                    </thead>


                    <tbody>


                        <?php if ($totalContratos > 0): ?>


                        <?php foreach ($contratos as $contrato): ?>


                        <tr>


                            <!-- CONTRATO -->

                            <td>

                                <strong>

                                    <?= htmlspecialchars(
                                            $contrato['numero_contrato']
                                        ) ?>

                                </strong>

                            </td>


                            <!-- ENTIDAD -->

                            <td>

                                <?= htmlspecialchars(
                                        $contrato['entidad']
                                    ) ?>

                            </td>


                            <!-- OBJETO -->

                            <td>

                                <?= htmlspecialchars(
                                        $contrato['objeto']
                                    ) ?>

                            </td>


                            <!-- VALOR -->

                            <td class="text-end">

                                <?= dinero(
                                        $contrato['valor']
                                    ) ?>

                            </td>


                            <!-- AÑO -->

                            <td class="text-center">

                                <?= (int) $contrato['anio'] ?>

                            </td>


                            <!-- SMMLV % -->

                            <td class="text-end">

                                <?=
                                        numero(
                                            $contrato['smmlv_porcentaje']
                                        )
                                    ?>

                            </td>


                            <!-- SMMLV -->

                            <td class="text-end">

                                <strong>

                                    <?= dinero(
                                            $contrato['smmlv_actual']
                                        ) ?>

                                </strong>

                            </td>


                            <!-- ACCIONES -->

                            <td>

                                <div class="d-flex gap-1">

                                    <a href="<?= BASE_URL ?>smmlv/editar.php?id=<?= $contrato['id'] ?>"
                                        class="btn btn-warning btn-sm" title="Editar">

                                        <i class="bi bi-pencil"></i>

                                    </a>


                                    <a href="<?= BASE_URL ?>smmlv/eliminar.php?id=<?= $contrato['id'] ?>"
                                        class="btn btn-danger btn-sm" title="Eliminar"
                                        onclick="return confirm('¿Está seguro de eliminar este contrato?');">

                                        <i class="bi bi-trash"></i>

                                    </a>

                                </div>

                            </td>


                        </tr>


                        <?php endforeach; ?>


                        <?php else: ?>


                        <tr>

                            <td colspan="8" class="text-center text-muted py-4">

                                <i class="bi bi-inbox fs-3 d-block mb-2"></i>

                                No hay contratos registrados.

                            </td>

                        </tr>


                        <?php endif; ?>


                    </tbody>





                </table>


            </div>


        </section>


    </div>


    <?php

include __DIR__ . '/../includes/footer.php';

include __DIR__ . '/../includes/scripts.php';

?>