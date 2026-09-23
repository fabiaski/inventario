<?php

$error = $_GET['error'] ?? '';

require_once __DIR__ . '/../../config/conexion.php';

require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/navbar.php';
require_once __DIR__ . '/../../includes/sidebar.php';

?>

<!-- partial -->
<div class="main-panel">
    <div class="content-wrapper">

        <div class="row">
            <div class="col-12 grid-margin stretch-card">

                <div class="card">
                    <div class="card-body">

                        <?php if ($error === 'iva'): ?>

                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                Debe ingresar un valor válido para el IVA.
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>

                        <?php elseif ($error === 'impoconsumo'): ?>

                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                Debe ingresar un valor válido para el Impoconsumo.
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>

                        <?php elseif ($error === 'retencion'): ?>

                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                Debe ingresar un valor válido para la Retención.
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>

                        <?php elseif ($error === 'valor_contrato'): ?>

                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                Debe ingresar un valor válido para el contrato.
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>

                        <?php endif; ?>


                        <!-- ENCABEZADO -->
                        <div class="panel-header d-flex justify-content-between align-items-center">

                            <div>

                                <h2 class="mb-1 section-title">
                                    <i class="bi bi-receipt"></i>
                                    Nuevo Contrato
                                </h2>

                                <p class="text-muted mb-0">
                                    Registre la información del contrato.
                                </p>

                            </div>

                            <a href="factura.php" class="btn btn-secondary">

                                <i class="bi bi-arrow-left"></i>
                                Volver

                            </a>

                        </div>


                        <hr>


                        <!-- FORMULARIO -->
                        <form action="guardar.php" method="POST" id="formContrato">

                            <div class="row g-3">


                                <!-- Número de contrato -->
                                <div class="col-md-3">

                                    <label for="numero_contrato" class="form-label">
                                        Número de Contrato
                                        <span class="text-danger">*</span>
                                    </label>

                                    <input
                                        type="text"
                                        name="numero_contrato"
                                        id="numero_contrato"
                                        class="form-control"
                                        maxlength="100"
                                        placeholder="Ej. CONTRATO-001-2026"
                                        required>

                                </div>


                                <!-- Fecha -->
                                <div class="col-md-2">

                                    <label for="fecha" class="form-label">
                                        Fecha
                                        <span class="text-danger">*</span>
                                    </label>

                                    <input
                                        type="date"
                                        name="fecha"
                                        id="fecha"
                                        class="form-control"
                                        value="<?= date('Y-m-d') ?>"
                                        required>

                                </div>


                                <!-- Valor del contrato -->
                                <div class="col-md-4">

                                    <label for="valor_contrato" class="form-label">
                                        Valor del Contrato
                                        <span class="text-danger">*</span>
                                    </label>

                                    <div class="input-group">

                                        <span class="input-group-text">
                                            $
                                        </span>

                                        <input
                                            type="text"
                                            name="valor_contrato"
                                            id="valor_contrato"
                                            class="form-control"
                                            placeholder="Ej. 50.000.000"
                                            inputmode="numeric"
                                            required>

                                    </div>

                                </div>


                                <!-- Objeto del contrato -->
                                <div class="col-12">

                                    <label for="objeto_contrato" class="form-label">
                                        Objeto del Contrato
                                        <span class="text-danger">*</span>
                                    </label>

                                    <textarea
                                        name="objeto_contrato"
                                        id="objeto_contrato"
                                        class="form-control"
                                        rows="4"
                                        style="resize: vertical;"
                                        maxlength="2000"
                                        placeholder="Describa el objeto, propósito o finalidad del contrato..."
                                        required></textarea>

                                </div>

                            </div>


                            <hr class="my-4">


                            <!-- INFORMACIÓN TRIBUTARIA -->
                            <h5 class="mb-3">
                                Información tributaria
                            </h5>


                            <div class="row g-3 px-5">


                                <!-- IVA -->
                                <div class="col-12">

                                    <div class="form-check mb-2">

                                        <input
                                            class="form-check-input"
                                            type="checkbox"
                                            name="tiene_iva"
                                            id="tiene_iva"
                                            value="1">

                                        <label
                                            class="form-check-label fs-6"
                                            for="tiene_iva">

                                            Tiene IVA

                                        </label>

                                    </div>


                                    <div
                                        id="contenedor_iva"
                                        style="display: none;">

                                        <label
                                            for="valor_iva"
                                            class="form-label">

                                            Valor IVA

                                        </label>


                                        <div
                                            class="input-group"
                                            style="max-width: 400px;">

                                            <span class="input-group-text">
                                                $
                                            </span>

                                            <input
                                                type="text"
                                                name="valor_iva"
                                                id="valor_iva"
                                                class="form-control"
                                                placeholder="Ej. 9.500.000"
                                                inputmode="numeric">

                                        </div>

                                    </div>

                                </div>


                                <!-- IMPOCONSUMO -->
                                <div class="col-12">

                                    <div class="form-check mb-2">

                                        <input
                                            class="form-check-input"
                                            type="checkbox"
                                            name="tiene_impoconsumo"
                                            id="tiene_impoconsumo"
                                            value="1">

                                        <label
                                            class="form-check-label fs-6"
                                            for="tiene_impoconsumo">

                                            Tiene Impoconsumo

                                        </label>

                                    </div>


                                    <div
                                        id="contenedor_impoconsumo"
                                        style="display: none;">

                                        <label
                                            for="valor_impoconsumo"
                                            class="form-label">

                                            Valor Impoconsumo

                                        </label>


                                        <div
                                            class="input-group"
                                            style="max-width: 400px;">

                                            <span class="input-group-text">
                                                $
                                            </span>

                                            <input
                                                type="text"
                                                name="valor_impoconsumo"
                                                id="valor_impoconsumo"
                                                class="form-control"
                                                placeholder="Ej. 500.000"
                                                inputmode="numeric">

                                        </div>

                                    </div>

                                </div>


                                <!-- RETENCIÓN -->
                                <div class="col-12">

                                    <div class="form-check mb-2">

                                        <input
                                            class="form-check-input"
                                            type="checkbox"
                                            name="tiene_retencion"
                                            id="tiene_retencion"
                                            value="1">

                                        <label
                                            class="form-check-label fs-6"
                                            for="tiene_retencion">

                                            Tiene Retención

                                        </label>

                                    </div>


                                    <div
                                        id="contenedor_retencion"
                                        style="display: none;">

                                        <label
                                            for="valor_retencion"
                                            class="form-label">

                                            Valor Retención

                                        </label>


                                        <div
                                            class="input-group"
                                            style="max-width: 400px;">

                                            <span class="input-group-text">
                                                $
                                            </span>

                                            <input
                                                type="text"
                                                name="valor_retencion"
                                                id="valor_retencion"
                                                class="form-control"
                                                placeholder="Ej. 1.000.000"
                                                inputmode="numeric">

                                        </div>

                                    </div>

                                </div>

                            </div>


                            <hr class="my-4">


                            <!-- BOTONES -->
                            <div class="d-flex justify-content-end gap-2">

                                <a
                                    href="facturacion.php"
                                    class="btn btn-secondary">

                                    <i class="bi bi-x-circle"></i>
                                    Cancelar

                                </a>


                                <button
                                    type="submit"
                                    class="btn btn-success">

                                    <i class="bi bi-save"></i>
                                    Guardar Contrato

                                </button>

                            </div>


                        </form>

                    </div>
                </div>

            </div>
        </div>

    </div>


    <?php

    include __DIR__ . '/../../includes/footer.php';
    include __DIR__ . '/../../includes/scripts.php';

    ?>


<script>

document.addEventListener('DOMContentLoaded', function () {


    // ==================================================
    // CAMPOS
    // ==================================================

    const formulario =
        document.getElementById('formContrato');

    const valorContrato =
        document.getElementById('valor_contrato');


    const tieneIva =
        document.getElementById('tiene_iva');

    const valorIva =
        document.getElementById('valor_iva');

    const contenedorIva =
        document.getElementById('contenedor_iva');


    const tieneImpoconsumo =
        document.getElementById('tiene_impoconsumo');

    const valorImpoconsumo =
        document.getElementById('valor_impoconsumo');

    const contenedorImpoconsumo =
        document.getElementById('contenedor_impoconsumo');


    const tieneRetencion =
        document.getElementById('tiene_retencion');

    const valorRetencion =
        document.getElementById('valor_retencion');

    const contenedorRetencion =
        document.getElementById('contenedor_retencion');


    // ==================================================
    // IVA
    // ==================================================

    tieneIva.addEventListener('change', function () {

        if (this.checked) {

            contenedorIva.style.display = 'block';

        } else {

            contenedorIva.style.display = 'none';

            valorIva.value = '';

        }

    });


    // ==================================================
    // IMPOCONSUMO
    // ==================================================

    tieneImpoconsumo.addEventListener('change', function () {

        if (this.checked) {

            contenedorImpoconsumo.style.display = 'block';

        } else {

            contenedorImpoconsumo.style.display = 'none';

            valorImpoconsumo.value = '';

        }

    });


    // ==================================================
    // RETENCIÓN
    // ==================================================

    tieneRetencion.addEventListener('change', function () {

        if (this.checked) {

            contenedorRetencion.style.display = 'block';

        } else {

            contenedorRetencion.style.display = 'none';

            valorRetencion.value = '';

        }

    });


    // ==================================================
    // FORMATEAR VALORES
    // ==================================================

    function formatearValor(campo) {

        campo.addEventListener('input', function () {

            let valor =
                this.value.replace(/\D/g, '');

            if (valor !== '') {

                this.value =
                    Number(valor).toLocaleString('es-CO');

            } else {

                this.value = '';

            }

        });

    }


    formatearValor(valorContrato);

    formatearValor(valorIva);

    formatearValor(valorImpoconsumo);

    formatearValor(valorRetencion);


    // ==================================================
    // VALIDAR FORMULARIO
    // ==================================================

    formulario.addEventListener('submit', function (e) {


        // ----------------------------------------------
        // VALOR DEL CONTRATO
        // ----------------------------------------------

        if (valorContrato.value.trim() === '') {

            e.preventDefault();

            alert(
                'Debe ingresar el valor del contrato.'
            );

            valorContrato.focus();

            return false;

        }


        // ----------------------------------------------
        // IVA
        // ----------------------------------------------

        if (
            tieneIva.checked &&
            valorIva.value.trim() === ''
        ) {

            e.preventDefault();

            alert(
                'Debe ingresar el valor del IVA.'
            );

            valorIva.focus();

            return false;

        }


        // ----------------------------------------------
        // IMPOCONSUMO
        // ----------------------------------------------

        if (
            tieneImpoconsumo.checked &&
            valorImpoconsumo.value.trim() === ''
        ) {

            e.preventDefault();

            alert(
                'Debe ingresar el valor del Impoconsumo.'
            );

            valorImpoconsumo.focus();

            return false;

        }


        // ----------------------------------------------
        // RETENCIÓN
        // ----------------------------------------------

        if (
            tieneRetencion.checked &&
            valorRetencion.value.trim() === ''
        ) {

            e.preventDefault();

            alert(
                'Debe ingresar el valor de la Retención.'
            );

            valorRetencion.focus();

            return false;

        }


        // Si todo está correcto
        return true;

    });

});

</script>