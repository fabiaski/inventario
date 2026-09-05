<?php

require_once __DIR__ . '/../../config/conexion.php';


//==================================================
// VALIDAR ID DE FACTURA
//==================================================

$facturaId = (int) ($_GET['id'] ?? 0);

if ($facturaId <= 0) {
    exit('Factura no válida.');
}


//==================================================
// CONSULTAR FACTURA
//==================================================

$sql = "
    SELECT
        f.id,
        f.contrato_id,
        f.proveedor,
        f.numero_factura,
        f.valor,
        f.valor_sin_iva,
        f.porcentaje_iva,
        f.valor_iva,
        f.observacion,
        c.numero_contrato
    FROM facturas f

    INNER JOIN contratos c
        ON c.id = f.contrato_id

    WHERE f.id = ?
";


$stmt = $conexion->prepare($sql);

if (!$stmt) {
    exit(
        'Error preparando consulta: '
        . $conexion->error
    );
}


$stmt->bind_param(
    "i",
    $facturaId
);


$stmt->execute();


$resultado = $stmt->get_result();


$factura = $resultado->fetch_assoc();


$stmt->close();


if (!$factura) {
    exit('La factura no existe.');
}


//==================================================
// CONSULTAR SOPORTES
//==================================================

$sqlSoportes = "
    SELECT
        id,
        archivo,
        tipo_archivo
    FROM soportes_factura
    WHERE factura_id = ?
    ORDER BY id ASC
";


$stmtSoportes =
    $conexion->prepare($sqlSoportes);


if (!$stmtSoportes) {
    exit(
        'Error preparando consulta de soportes: '
        . $conexion->error
    );
}


$stmtSoportes->bind_param(
    "i",
    $facturaId
);


$stmtSoportes->execute();


$resultadoSoportes =
    $stmtSoportes->get_result();


$soportes = [];


while (
    $soporte =
    $resultadoSoportes->fetch_assoc()
) {

    $soportes[] = $soporte;

}


$stmtSoportes->close();



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




                                <h2 class="h3 mb-2 section-title">

                                    <i class="bi bi-pencil-square"></i>

                                    Editar Factura

                                </h2>

                                <p class="h5 text-muted mb-1">

                                    Contrato:

                                    <strong>
                                        <?= htmlspecialchars(
                            $factura['numero_contrato']
                        ) ?>
                                    </strong>

                                </p>

                            </div>


                            <a href="ver.php?id=<?= $factura['contrato_id'] ?>" class="btn btn-secondary">

                                <i class="bi bi-arrow-left"></i>

                                Volver

                            </a>

                        </div>


                        <hr>


                        <form action="actualizar_factura.php" method="POST">


                            <input type="hidden" name="factura_id" value="<?= $factura['id'] ?>">


                            <div class="row g-3">


                                <!--==================================================
                                PROVEEDOR
                                ==================================================-->

                                <div class="col-md-6">

                                    <label for="proveedor" class="form-label">

                                        Proveedor

                                    </label>

                                    <input type="text" name="proveedor" id="proveedor" class="form-control"
                                        maxlength="150" value="<?= htmlspecialchars(
                            $factura['proveedor']
                        ) ?>" required>

                                </div>


                                <!--==================================================
                NÚMERO FACTURA
                ==================================================-->

                                <div class="col-md-6">

                                    <label for="numero_factura" class="form-label">

                                        N° de Factura

                                    </label>

                                    <input type="text" name="numero_factura" id="numero_factura" class="form-control"
                                        maxlength="100" value="<?= htmlspecialchars(
                            $factura['numero_factura']
                        ) ?>" required>

                                </div>


                                <!--==================================================
                VALOR
                ==================================================-->

                                <div class="col-md-6">

                                    <label for="valor" class="form-label">

                                        Valor de la Factura

                                    </label>

                                    <div class="input-group">

                                        <span class="input-group-text">
                                            $
                                        </span>

                                        <input type="number" name="valor" id="valor" class="form-control" min="0"
                                            step="0.01" value="<?= htmlspecialchars(
                                $factura['valor']
                            ) ?>" required>

                                    </div>

                                </div>


                                <!--==================================================
                IVA
                ==================================================-->

                                <div class="col-md-6">

                                    <label for="porcentaje_iva" class="form-label">

                                        % IVA

                                    </label>

                                    <div class="input-group">

                                        <input type="number" name="porcentaje_iva" id="porcentaje_iva"
                                            class="form-control" min="0" max="100" step="1" value="<?= htmlspecialchars(
                                $factura['porcentaje_iva']
                            ) ?>" required>

                                        <span class="input-group-text">
                                            %
                                        </span>

                                    </div>

                                </div>


                                <!--==================================================
                VALOR SIN IVA
                ==================================================-->

                                <div class="col-md-6">

                                    <label for="valor_sin_iva" class="form-label">

                                        Valor sin IVA

                                    </label>

                                    <div class="input-group">

                                        <span class="input-group-text">
                                            $
                                        </span>

                                        <input type="number" id="valor_sin_iva" class="form-control" value="<?= htmlspecialchars(
                                $factura['valor_sin_iva']
                            ) ?>" readonly>

                                    </div>

                                </div>


                                <!--==================================================
                VALOR IVA
                ==================================================-->

                                <div class="col-md-6">

                                    <label for="valor_iva" class="form-label">

                                        IVA

                                    </label>

                                    <div class="input-group">

                                        <span class="input-group-text">
                                            $
                                        </span>

                                        <input type="number" id="valor_iva" class="form-control" value="<?= htmlspecialchars(
                                $factura['valor_iva']
                            ) ?>" readonly>

                                    </div>

                                </div>


                                <!--==================================================
                OBSERVACIÓN
                ==================================================-->

                                <div class="col-12">

                                    <label for="observacion" class="form-label">

                                        Observación

                                    </label>

                                    <textarea name="observacion" id="observacion" class="form-control" rows="3"><?= htmlspecialchars(
                        $factura['observacion'] ?? ''
                    ) ?></textarea>

                                </div>


                            </div>


                            <hr class="my-4">


                            <div class="d-flex justify-content-end gap-2">

                                <a href="ver.php?id=<?= $factura['contrato_id'] ?>" class="btn btn-secondary">

                                    <i class="bi bi-x-circle"></i>

                                    Cancelar

                                </a>


                                <button type="submit" class="btn btn-primary">

                                    <i class="bi bi-save"></i>

                                    Guardar Cambios

                                </button>

                            </div>


                        </form>


                        <!--==================================================
        SOPORTES
        ==================================================-->

                        <hr class="my-4">


                        <div class="card">

                            <div class="card-header">

                                <strong>

                                    <i class="bi bi-paperclip"></i>

                                    Soportes de la factura

                                </strong>

                            </div>


                            <div class="card-body">


                                <?php if (!empty($soportes)): ?>


                                <div class="table-responsive">

                                    <table class="table table-bordered table-hover align-middle mb-0">

                                        <thead class="table-light">

                                            <tr>

                                                <th>
                                                    Archivo
                                                </th>

                                                <th>
                                                    Tipo
                                                </th>

                                                <th style="width: 180px;">
                                                    Acciones
                                                </th>

                                            </tr>

                                        </thead>


                                        <tbody>


                                            <?php foreach (
                                    $soportes
                                    as $soporte
                                ): ?>


                                            <tr>


                                                <!-- ARCHIVO -->

                                                <td>

                                                    <?php

                                            $nombreArchivo =
                                                $soporte['archivo'];

                                            $extension =
                                                strtolower(
                                                    pathinfo(
                                                        $nombreArchivo,
                                                        PATHINFO_EXTENSION
                                                    )
                                                );

                                            ?>


                                                    <?php if (
                                                $extension === 'pdf'
                                            ): ?>

                                                    <i class="bi bi-file-earmark-pdf text-danger"></i>

                                                    <?php elseif (
                                                in_array(
                                                    $extension,
                                                    [
                                                        'jpg',
                                                        'jpeg',
                                                        'png'
                                                    ],
                                                    true
                                                )
                                            ): ?>

                                                    <i class="bi bi-file-earmark-image text-primary"></i>

                                                    <?php else: ?>

                                                    <i class="bi bi-file-earmark"></i>

                                                    <?php endif; ?>


                                                    <?= htmlspecialchars(
                                                $nombreArchivo
                                            ) ?>

                                                </td>


                                                <!-- TIPO -->

                                                <td>

                                                    <?= htmlspecialchars(
                                                $soporte['tipo_archivo']
                                            ) ?>

                                                </td>


                                                <!-- ACCIONES -->

                                                <td>

                                                    <div class="d-flex gap-1">


                                                        <!-- VER -->

                                                        <a href="../uploads/soportes_facturas/<?= rawurlencode(
                                                        $nombreArchivo
                                                    ) ?>" target="_blank" class="btn btn-info btn-sm"
                                                            title="Ver archivo">

                                                            <i class="bi bi-eye"></i>

                                                        </a>


                                                        <!-- ELIMINAR -->

                                                        <a href="eliminar_soporte.php?id=<?= $soporte['id'] ?>"
                                                            class="btn btn-danger btn-sm" title="Eliminar soporte"
                                                            onclick="
                                                        return confirm(
                                                            '¿Está seguro de eliminar este soporte?'
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


                                <div class="text-muted">

                                    <i class="bi bi-info-circle"></i>

                                    Esta factura todavía no tiene soportes.

                                </div>


                                <?php endif; ?>


                                <!--==================================================
                AGREGAR SOPORTE
                ==================================================-->

                                <hr class="my-4">


                                <form action="agregar_soporte.php" method="POST" enctype="multipart/form-data">


                                    <input type="hidden" name="factura_id" value="<?= $factura['id'] ?>">


                                    <label for="soporte" class="form-label">

                                        Agregar nuevo soporte

                                    </label>


                                    <div class="row g-2">


                                        <div class="col-md-9">

                                            <input type="file" name="soporte" id="soporte" class="form-control"
                                                accept=".pdf,.jpg,.jpeg,.png" required>

                                            <div class="form-text">

                                                Formatos permitidos:
                                                PDF, JPG, JPEG y PNG.
                                                Máximo 10 MB.

                                            </div>

                                        </div>


                                        <div class="col-md-3">

                                            <button type="submit" class="btn btn-success w-100">

                                                <i class="bi bi-upload"></i>

                                                Subir Soporte

                                            </button>

                                        </div>


                                    </div>


                                </form>


                            </div>

                        </div>


                        </section>

                    </div>


                    <!--==================================================
CALCULAR IVA
==================================================-->

                    <script>
                    document.addEventListener(
                        'DOMContentLoaded',
                        function() {


                            const valor =
                                document.getElementById('valor');


                            const porcentaje =
                                document.getElementById(
                                    'porcentaje_iva'
                                );


                            const valorSinIva =
                                document.getElementById(
                                    'valor_sin_iva'
                                );


                            const valorIva =
                                document.getElementById(
                                    'valor_iva'
                                );


                            function calcularIVA() {

                                const valorFactura =
                                    parseFloat(
                                        valor.value
                                    ) || 0;


                                const porcentajeIVA =
                                    parseFloat(
                                        porcentaje.value
                                    ) || 0;


                                let base;


                                if (porcentajeIVA > 0) {

                                    base =
                                        valorFactura /
                                        (
                                            1 +
                                            porcentajeIVA / 100
                                        );

                                } else {

                                    base = valorFactura;

                                }


                                const iva =
                                    valorFactura - base;


                                valorSinIva.value =
                                    base.toFixed(2);


                                valorIva.value =
                                    iva.toFixed(2);

                            }


                            valor.addEventListener(
                                'input',
                                calcularIVA
                            );


                            porcentaje.addEventListener(
                                'input',
                                calcularIVA
                            );


                        }
                    );
                    </script>


                    <?php

include __DIR__ . '/../../includes/footer.php';

include __DIR__ . '/../../includes/scripts.php';

?>