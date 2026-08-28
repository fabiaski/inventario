<?php

require_once __DIR__ . '/../../config/conexion.php';


//==================================================
// VALIDAR CONTRATO
//==================================================

$contratoId = (int) ($_GET['id'] ?? 0);

if ($contratoId <= 0) {
    exit('Contrato no válido.');
}


//==================================================
// CONSULTAR CONTRATO
//==================================================

$sql = "
    SELECT
        id,
        numero_contrato,
        objeto_contrato
    FROM contratos
    WHERE id = ?
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
    $contratoId
);

$stmt->execute();

$resultado = $stmt->get_result();

$contrato = $resultado->fetch_assoc();

$stmt->close();


if (!$contrato) {
    exit('El contrato no existe.');
}


require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';
require_once __DIR__ . '/../includes/sidebar.php';

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

                                    Nueva Factura

                                </h2>

                                <p class="h5 text-muted mb-1">

                                    Contrato:
                                    <strong>
                                        <?= htmlspecialchars(
                            $contrato['numero_contrato']
                        ) ?>
                                    </strong>

                                </p>

                            </div>


                            <a href="ver.php?id=<?= $contratoId ?>" class="btn btn-secondary">

                                <i class="bi bi-arrow-left"></i>

                                Volver al Contrato

                            </a>

                        </div>


                        <hr>


                        <!--==================================================
        FORMULARIO
        ==================================================-->

                        <form action="guardar_factura.php" method="POST" enctype="multipart/form-data">


                            <input type="hidden" name="contrato_id" value="<?= $contratoId ?>">


                            <div class="row g-3">


                                <!--==================================================
                PROVEEDOR
                ==================================================-->

                                <div class="col-md-6">

                                    <label for="proveedor" class="form-label">

                                        Proveedor

                                        <span class="text-danger">*</span>

                                    </label>

                                    <input type="text" name="proveedor" id="proveedor" class="form-control"
                                        maxlength="150" required>

                                </div>


                                <!--==================================================
                NÚMERO DE FACTURA
                ==================================================-->

                                <div class="col-md-6">

                                    <label for="numero_factura" class="form-label">

                                        N° de Factura

                                        <span class="text-danger">*</span>

                                    </label>

                                    <input type="text" name="numero_factura" id="numero_factura" class="form-control"
                                        maxlength="100" required>

                                </div>


                                <!--==================================================
                VALOR DE LA FACTURA
                ==================================================-->

                                <div class="col-md-6">

                                    <label for="valor" class="form-label">

                                        Valor de la Factura

                                        <span class="text-danger">*</span>

                                    </label>

                                    <div class="input-group">

                                        <span class="input-group-text">
                                            $
                                        </span>

                                        <input type="number" name="valor" id="valor" class="form-control" min="0"
                                            step="0.01" value="0" required>

                                    </div>

                                </div>


                                <!--==================================================
                PORCENTAJE IVA
                ==================================================-->

                                <div class="col-md-6">

                                    <label for="porcentaje_iva" class="form-label">

                                        % IVA

                                        <span class="text-danger">*</span>

                                    </label>

                                    <div class="input-group">

                                        <input type="number" name="porcentaje_iva" id="porcentaje_iva"
                                            class="form-control" min="0" max="100" step="1" value="19" required>

                                        <span class="input-group-text">
                                            %
                                        </span>

                                    </div>

                                    <small class="text-muted">

                                        Por defecto se utiliza 19%.

                                    </small>

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

                                        <input type="number" name="valor_sin_iva" id="valor_sin_iva"
                                            class="form-control" step="0.01" value="0" readonly>

                                    </div>

                                </div>


                                <!--==================================================
                IVA
                ==================================================-->

                                <div class="col-md-6">

                                    <label for="valor_iva" class="form-label">

                                        IVA

                                    </label>

                                    <div class="input-group">

                                        <span class="input-group-text">
                                            $
                                        </span>

                                        <input type="number" name="valor_iva" id="valor_iva" class="form-control"
                                            step="0.01" value="0" readonly>

                                    </div>

                                </div>


                                <!--==================================================
                OBSERVACIÓN
                ==================================================-->

                                <div class="col-12">

                                    <label for="observacion" class="form-label">

                                        Observación

                                    </label>

                                    <textarea name="observacion" id="observacion" class="form-control"
                                        rows="3"></textarea>

                                </div>


                                <!--==================================================
                SOPORTE
                ==================================================-->

                                <div class="col-12">

                                    <label for="soporte" class="form-label">

                                        Soporte de la Factura

                                    </label>

                                    <input type="file" name="soporte" id="soporte" class="form-control"
                                        accept=".pdf,.jpg,.jpeg,.png">

                                    <small class="text-muted">

                                        Puede adjuntar un PDF, JPG, JPEG o PNG.

                                    </small>

                                </div>


                            </div>


                            <hr class="my-4">


                            <!--==================================================
            BOTONES
            ==================================================-->

                            <div class="d-flex justify-content-end gap-2">

                                <a href="ver.php?id=<?= $contratoId ?>" class="btn btn-secondary">

                                    <i class="bi bi-x-circle"></i>

                                    Cancelar

                                </a>


                                <button type="submit" class="btn btn-success">

                                    <i class="bi bi-save"></i>

                                    Guardar Factura

                                </button>

                            </div>


                        </form>


                        </section>

                    </div>


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


                                /*
                                 * El valor ingresado es el valor
                                 * TOTAL de la factura.
                                 *
                                 * Ejemplo:
                                 *
                                 * $119.000 con IVA 19%
                                 *
                                 * Sin IVA = 119000 / 1.19
                                 *
                                 * IVA = 119000 - valor sin IVA
                                 */


                                const valorBase =
                                    valorFactura /
                                    (
                                        1 +
                                        (
                                            porcentajeIVA / 100
                                        )
                                    );


                                const iva =
                                    valorFactura -
                                    valorBase;


                                valorSinIva.value =
                                    valorBase.toFixed(2);


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


                            calcularIVA();

                        }
                    );
                    </script>


                    <?php

include __DIR__ . '/../includes/footer.php';

include __DIR__ . '/../includes/scripts.php';

?>