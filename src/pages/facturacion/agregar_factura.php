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

$stmt->bind_param("i", $contratoId);
$stmt->execute();

$resultado = $stmt->get_result();

$contrato = $resultado->fetch_assoc();

$stmt->close();

if (!$contrato) {
    exit('El contrato no existe.');
}


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

                                    <i class="bi bi-receipt"></i>

                                    Nueva Factura

                                </h2>

                                <p class="h5 text-muted mb-1">

                                    Contrato:

                                    <strong>
                                        <?= htmlspecialchars($contrato['numero_contrato']) ?>
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

                        <form
                            action="guardar_factura.php"
                            method="POST"
                            enctype="multipart/form-data"
                            id="formFactura"
                        >

                            <input
                                type="hidden"
                                name="contrato_id"
                                value="<?= $contratoId ?>"
                            >


                            <div class="row g-3">


                                <!--==================================================
                                PROVEEDOR
                                ==================================================-->

                                <div class="col-md-6">

                                    <label for="proveedor" class="form-label">

                                        Proveedor

                                        <span class="text-danger">*</span>

                                    </label>

                                    <input
                                        type="text"
                                        name="proveedor"
                                        id="proveedor"
                                        class="form-control"
                                        maxlength="150"
                                        required
                                    >

                                </div>


                                <!--==================================================
                                NÚMERO DE FACTURA
                                ==================================================-->

                                <div class="col-md-6">

                                    <label for="numero_factura" class="form-label">

                                        N° de Factura

                                        <span class="text-danger">*</span>

                                    </label>

                                    <input
                                        type="text"
                                        name="numero_factura"
                                        id="numero_factura"
                                        class="form-control"
                                        maxlength="100"
                                        required
                                    >

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

                                        <input
                                            type="text"
                                            name="valor"
                                            id="valor"
                                            class="form-control"
                                            inputmode="numeric"
                                            placeholder="Ej. 1.500.000"
                                            required
                                        >

                                    </div>

                                </div>


                                <!--==================================================
                                INFORMACIÓN TRIBUTARIA
                                ==================================================-->

                                <div class="col-12">

                                    <hr class="my-3">

                                    <h5 class="mb-3">
                                        Información tributaria
                                    </h5>

                                </div>


                                <div class="row g-3 px-5">


                                    <!--==================================================
                                    IVA
                                    ==================================================-->

                                    <div class="col-12">

                                        <div class="form-check mb-2">

                                            <input
                                                class="form-check-input"
                                                type="checkbox"
                                                name="tiene_iva"
                                                id="tiene_iva"
                                                value="1"
                                            >

                                            <label
                                                class="form-check-label fs-6"
                                                for="tiene_iva"
                                            >
                                                Tiene IVA
                                            </label>

                                        </div>


                                        <div
                                            id="contenedor_iva"
                                            style="display: none;"
                                        >

                                            <label
                                                for="valor_iva"
                                                class="form-label"
                                            >
                                                Valor IVA
                                            </label>

                                            <div
                                                class="input-group"
                                                style="max-width: 400px;"
                                            >

                                                <span class="input-group-text">
                                                    $
                                                </span>

                                                <input
                                                    type="text"
                                                    name="valor_iva"
                                                    id="valor_iva"
                                                    class="form-control"
                                                    inputmode="numeric"
                                                    placeholder="Ej. 285.000"
                                                >

                                            </div>

                                        </div>

                                    </div>


                                    <!--==================================================
                                    IMPOCONSUMO
                                    ==================================================-->

                                    <div class="col-12">

                                        <div class="form-check mb-2">

                                            <input
                                                class="form-check-input"
                                                type="checkbox"
                                                name="tiene_impoconsumo"
                                                id="tiene_impoconsumo"
                                                value="1"
                                            >

                                            <label
                                                class="form-check-label fs-6"
                                                for="tiene_impoconsumo"
                                            >
                                                Tiene Impoconsumo
                                            </label>

                                        </div>


                                        <div
                                            id="contenedor_impoconsumo"
                                            style="display: none;"
                                        >

                                            <label
                                                for="valor_impoconsumo"
                                                class="form-label"
                                            >
                                                Valor Impoconsumo
                                            </label>

                                            <div
                                                class="input-group"
                                                style="max-width: 400px;"
                                            >

                                                <span class="input-group-text">
                                                    $
                                                </span>

                                                <input
                                                    type="text"
                                                    name="valor_impoconsumo"
                                                    id="valor_impoconsumo"
                                                    class="form-control"
                                                    inputmode="numeric"
                                                    placeholder="Ej. 100.000"
                                                >

                                            </div>

                                        </div>

                                    </div>


                                    <!--==================================================
                                    RETENCIÓN
                                    ==================================================-->

                                    <div class="col-12">

                                        <div class="form-check mb-2">

                                            <input
                                                class="form-check-input"
                                                type="checkbox"
                                                name="tiene_retencion"
                                                id="tiene_retencion"
                                                value="1"
                                            >

                                            <label
                                                class="form-check-label fs-6"
                                                for="tiene_retencion"
                                            >
                                                Tiene Retención
                                            </label>

                                        </div>


                                        <div
                                            id="contenedor_retencion"
                                            style="display: none;"
                                        >

                                            <label
                                                for="valor_retencion"
                                                class="form-label"
                                            >
                                                Valor Retención
                                            </label>

                                            <div
                                                class="input-group"
                                                style="max-width: 400px;"
                                            >

                                                <span class="input-group-text">
                                                    $
                                                </span>

                                                <input
                                                    type="text"
                                                    name="valor_retencion"
                                                    id="valor_retencion"
                                                    class="form-control"
                                                    inputmode="numeric"
                                                    placeholder="Ej. 50.000"
                                                >

                                            </div>

                                        </div>

                                    </div>


                                </div>


                                <!--==================================================
                                OBSERVACIÓN
                                ==================================================-->

                                <div class="col-12">

                                    <label
                                        for="observacion"
                                        class="form-label"
                                    >

                                        Observación

                                    </label>

                                    <textarea
                                        name="observacion"
                                        id="observacion"
                                        class="form-control"
                                        rows="3"
                                    ></textarea>

                                </div>


                                <!--==================================================
                                SOPORTE
                                ==================================================-->

                                <div class="col-12">

                                    <label
                                        for="soporte"
                                        class="form-label"
                                    >

                                        Soporte de la Factura

                                    </label>

                                    <input
                                        type="file"
                                        name="soporte"
                                        id="soporte"
                                        class="form-control"
                                        accept=".pdf,.jpg,.jpeg,.png"
                                    >

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

                                <a
                                    href="ver.php?id=<?= $contratoId ?>"
                                    class="btn btn-secondary"
                                >

                                    <i class="bi bi-x-circle"></i>

                                    Cancelar

                                </a>


                                <button
                                    type="submit"
                                    class="btn btn-success"
                                >

                                    <i class="bi bi-save"></i>

                                    Guardar Factura

                                </button>

                            </div>


                        </form>

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

    const formulario = document.getElementById('formFactura');

    const valor = document.getElementById('valor');


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


    //==================================================
    // MOSTRAR / OCULTAR IVA
    //==================================================

    tieneIva.addEventListener('change', function () {

        if (this.checked) {

            contenedorIva.style.display = 'block';

        } else {

            contenedorIva.style.display = 'none';

            valorIva.value = '';

        }

    });


    //==================================================
    // MOSTRAR / OCULTAR IMPOCONSUMO
    //==================================================

    tieneImpoconsumo.addEventListener('change', function () {

        if (this.checked) {

            contenedorImpoconsumo.style.display = 'block';

        } else {

            contenedorImpoconsumo.style.display = 'none';

            valorImpoconsumo.value = '';

        }

    });


    //==================================================
    // MOSTRAR / OCULTAR RETENCIÓN
    //==================================================

    tieneRetencion.addEventListener('change', function () {

        if (this.checked) {

            contenedorRetencion.style.display = 'block';

        } else {

            contenedorRetencion.style.display = 'none';

            valorRetencion.value = '';

        }

    });


    //==================================================
    // FORMATEAR VALORES
    //==================================================

    function formatearValor(campo) {

        campo.addEventListener('input', function () {

            let valorNumerico =
                this.value.replace(/\D/g, '');

            if (valorNumerico !== '') {

                this.value =
                    Number(valorNumerico)
                        .toLocaleString('es-CO');

            } else {

                this.value = '';

            }

        });

    }


    formatearValor(valor);

    formatearValor(valorIva);

    formatearValor(valorImpoconsumo);

    formatearValor(valorRetencion);


    //==================================================
    // VALIDAR FORMULARIO
    //==================================================

    formulario.addEventListener('submit', function (e) {

        if (valor.value.trim() === '') {

            e.preventDefault();

            alert(
                'Debe ingresar el valor de la factura.'
            );

            valor.focus();

            return false;
        }


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


        return true;

    });

});

</script>