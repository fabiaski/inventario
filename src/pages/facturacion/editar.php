<?php

require_once __DIR__ . '/../../config/conexion.php';


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

$sql = "
    SELECT
        id,
        numero_contrato,
        fecha,
        objeto_contrato,
        valor_contrato,
        tiene_iva,
        valor_iva,
        tiene_impoconsumo,
        valor_impoconsumo,
        tiene_retencion,
        valor_retencion
    FROM contratos
    WHERE id = ?
";


$stmt = $conexion->prepare($sql);

if (!$stmt) {
    exit(
        'Error preparando la consulta: '
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

                                <h2 class="h5 mb-1 section-title">

                                    <i class="bi bi-pencil-square"></i>

                                    Editar Contrato

                                </h2>

                                <p class="text-muted mb-0">

                                    Modifique la información del contrato.

                                </p>

                            </div>


                            <a
                                href="ver.php?id=<?= $contrato['id'] ?>"
                                class="btn btn-secondary"
                            >

                                <i class="bi bi-arrow-left"></i>

                                Volver

                            </a>

                        </div>


                        <hr>


                        <!--==================================================
                        FORMULARIO
                        ==================================================-->

                        <form
                            action="actualizar.php"
                            method="POST"
                            id="formContrato"
                        >


                            <input
                                type="hidden"
                                name="id"
                                value="<?= $contrato['id'] ?>"
                            >


                            <div class="row g-3">


                                <!--==================================================
                                NÚMERO DE CONTRATO
                                ==================================================-->

                                <div class="col-md-3">

                                    <label
                                        for="numero_contrato"
                                        class="form-label"
                                    >

                                        Número de Contrato

                                        <span class="text-danger">*</span>

                                    </label>


                                    <input
                                        type="text"
                                        name="numero_contrato"
                                        id="numero_contrato"
                                        class="form-control"
                                        maxlength="100"
                                        value="<?= htmlspecialchars(
                                            $contrato['numero_contrato']
                                        ) ?>"
                                        required
                                    >

                                </div>


                                <!--==================================================
                                FECHA
                                ==================================================-->

                                <div class="col-md-2">

                                    <label
                                        for="fecha"
                                        class="form-label"
                                    >

                                        Fecha

                                        <span class="text-danger">*</span>

                                    </label>


                                    <input
                                        type="date"
                                        name="fecha"
                                        id="fecha"
                                        class="form-control"
                                        value="<?= htmlspecialchars(
                                            $contrato['fecha']
                                        ) ?>"
                                        required
                                    >

                                </div>


                                <!--==================================================
                                VALOR DEL CONTRATO
                                ==================================================-->

                                <div class="col-md-4">

                                    <label
                                        for="valor_contrato"
                                        class="form-label"
                                    >

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
                                            inputmode="numeric"
                                            value="<?= number_format(
                                                (float) $contrato['valor_contrato'],
                                                0,
                                                ',',
                                                '.'
                                            ) ?>"
                                            required
                                        >

                                    </div>

                                </div>


                                <!--==================================================
                                OBJETO DEL CONTRATO
                                ==================================================-->

                                <div class="col-12">

                                    <label
                                        for="objeto_contrato"
                                        class="form-label"
                                    >

                                        Objeto del Contrato

                                        <span class="text-danger">*</span>

                                    </label>


                                    <textarea
                                        name="objeto_contrato"
                                        id="objeto_contrato"
                                        class="form-control"
                                        rows="4"
                                        maxlength="2000"
                                        style="resize: vertical;"
                                        required
                                    ><?= htmlspecialchars(
                                        $contrato['objeto_contrato']
                                    ) ?></textarea>

                                </div>


                            </div>


                            <hr class="my-4">


                            <!--==================================================
                            INFORMACIÓN TRIBUTARIA
                            ==================================================-->

                            <h5 class="mb-3">

                                Información tributaria

                            </h5>


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
                                            <?= (int) $contrato['tiene_iva'] === 1
                                                ? 'checked'
                                                : '' ?>
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
                                        style="<?= (int) $contrato['tiene_iva'] === 1
                                            ? 'display: block;'
                                            : 'display: none;' ?>"
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
                                                placeholder="Ej. 9.500.000"
                                                value="<?= !empty($contrato['valor_iva'])
                                                    ? number_format(
                                                        (float) $contrato['valor_iva'],
                                                        0,
                                                        ',',
                                                        '.'
                                                    )
                                                    : '' ?>"
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
                                            <?= (int) $contrato['tiene_impoconsumo'] === 1
                                                ? 'checked'
                                                : '' ?>
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
                                        style="<?= (int) $contrato['tiene_impoconsumo'] === 1
                                            ? 'display: block;'
                                            : 'display: none;' ?>"
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
                                                placeholder="Ej. 500.000"
                                                value="<?= !empty($contrato['valor_impoconsumo'])
                                                    ? number_format(
                                                        (float) $contrato['valor_impoconsumo'],
                                                        0,
                                                        ',',
                                                        '.'
                                                    )
                                                    : '' ?>"
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
                                            <?= (int) $contrato['tiene_retencion'] === 1
                                                ? 'checked'
                                                : '' ?>
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
                                        style="<?= (int) $contrato['tiene_retencion'] === 1
                                            ? 'display: block;'
                                            : 'display: none;' ?>"
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
                                                placeholder="Ej. 1.000.000"
                                                value="<?= !empty($contrato['valor_retencion'])
                                                    ? number_format(
                                                        (float) $contrato['valor_retencion'],
                                                        0,
                                                        ',',
                                                        '.'
                                                    )
                                                    : '' ?>"
                                            >

                                        </div>

                                    </div>

                                </div>


                            </div>


                            <!--==================================================
                            BOTONES
                            ==================================================-->

                            <div class="mt-4 d-flex gap-2">


                                <button
                                    type="submit"
                                    class="btn btn-primary"
                                >

                                    <i class="bi bi-save"></i>

                                    Guardar Cambios

                                </button>


                                <a
                                    href="ver.php?id=<?= $contrato['id'] ?>"
                                    class="btn btn-secondary"
                                >

                                    Cancelar

                                </a>


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


    //==================================================
    // CAMPOS
    //==================================================

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
    // FORMATEAR DINERO
    //==================================================

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


    //==================================================
    // VALIDAR FORMULARIO
    //==================================================

    formulario.addEventListener('submit', function (e) {


        // VALOR CONTRATO

        if (valorContrato.value.trim() === '') {

            e.preventDefault();

            alert(
                'Debe ingresar el valor del contrato.'
            );

            valorContrato.focus();

            return false;
        }


        // IVA

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


        // IMPOCONSUMO

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


        // RETENCIÓN

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