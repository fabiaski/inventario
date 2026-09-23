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
        f.tiene_iva,
        f.valor_iva,
        f.tiene_impoconsumo,
        f.valor_impoconsumo,
        f.tiene_retencion,
        f.valor_retencion,
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
        'Error preparando consulta: ' .
        $conexion->error
    );
}

$stmt->bind_param("i", $facturaId);

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


$stmtSoportes = $conexion->prepare($sqlSoportes);

if (!$stmtSoportes) {
    exit(
        'Error preparando consulta de soportes: ' .
        $conexion->error
    );
}

$stmtSoportes->bind_param("i", $facturaId);

$stmtSoportes->execute();

$resultadoSoportes = $stmtSoportes->get_result();

$soportes = [];

while ($soporte = $resultadoSoportes->fetch_assoc()) {
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
                                        <?= htmlspecialchars($factura['numero_contrato']) ?>
                                    </strong>

                                </p>

                            </div>


                            <a
                                href="ver.php?id=<?= $factura['contrato_id'] ?>"
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
                            action="actualizar_factura.php"
                            method="POST"
                            id="formFactura"
                        >

                            <input
                                type="hidden"
                                name="factura_id"
                                value="<?= $factura['id'] ?>"
                            >


                            <div class="row g-3">


                                <!--==================================================
                                PROVEEDOR
                                ==================================================-->

                                <div class="col-md-6">

                                    <label
                                        for="proveedor"
                                        class="form-label"
                                    >

                                        Proveedor

                                        <span class="text-danger">*</span>

                                    </label>

                                    <input
                                        type="text"
                                        name="proveedor"
                                        id="proveedor"
                                        class="form-control"
                                        maxlength="150"
                                        value="<?= htmlspecialchars($factura['proveedor']) ?>"
                                        required
                                    >

                                </div>


                                <!--==================================================
                                NÚMERO FACTURA
                                ==================================================-->

                                <div class="col-md-6">

                                    <label
                                        for="numero_factura"
                                        class="form-label"
                                    >

                                        N° de Factura

                                        <span class="text-danger">*</span>

                                    </label>

                                    <input
                                        type="text"
                                        name="numero_factura"
                                        id="numero_factura"
                                        class="form-control"
                                        maxlength="100"
                                        value="<?= htmlspecialchars($factura['numero_factura']) ?>"
                                        required
                                    >

                                </div>


                                <!--==================================================
                                VALOR FACTURA
                                ==================================================-->

                                <div class="col-md-6">

                                    <label
                                        for="valor"
                                        class="form-label"
                                    >

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
                                            value="<?= number_format((float) $factura['valor'], 0, ',', '.') ?>"
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
                                                <?= (int) $factura['tiene_iva'] === 1 ? 'checked' : '' ?>
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
                                            style="<?= (int) $factura['tiene_iva'] === 1 ? 'display: block;' : 'display: none;' ?>"
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
                                                    value="<?= !empty($factura['valor_iva']) ? number_format((float) $factura['valor_iva'], 0, ',', '.') : '' ?>"
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
                                                <?= (int) $factura['tiene_impoconsumo'] === 1 ? 'checked' : '' ?>
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
                                            style="<?= (int) $factura['tiene_impoconsumo'] === 1 ? 'display: block;' : 'display: none;' ?>"
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
                                                    value="<?= !empty($factura['valor_impoconsumo']) ? number_format((float) $factura['valor_impoconsumo'], 0, ',', '.') : '' ?>"
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
                                                <?= (int) $factura['tiene_retencion'] === 1 ? 'checked' : '' ?>
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
                                            style="<?= (int) $factura['tiene_retencion'] === 1 ? 'display: block;' : 'display: none;' ?>"
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
                                                    value="<?= !empty($factura['valor_retencion']) ? number_format((float) $factura['valor_retencion'], 0, ',', '.') : '' ?>"
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
                                    ><?= htmlspecialchars($factura['observacion'] ?? '') ?></textarea>

                                </div>


                            </div>


                            <hr class="my-4">


                            <div class="d-flex justify-content-end gap-2">

                                <a
                                    href="ver.php?id=<?= $factura['contrato_id'] ?>"
                                    class="btn btn-secondary"
                                >

                                    <i class="bi bi-x-circle"></i>

                                    Cancelar

                                </a>


                                <button
                                    type="submit"
                                    class="btn btn-primary"
                                >

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


                                                <?php foreach ($soportes as $soporte): ?>


                                                    <tr>

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


                                                            <?php if ($extension === 'pdf'): ?>

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


                                                            <?= htmlspecialchars($nombreArchivo) ?>

                                                        </td>


                                                        <td>

                                                            <?= htmlspecialchars(
                                                                $soporte['tipo_archivo']
                                                            ) ?>

                                                        </td>


                                                        <td>

                                                            <div class="d-flex gap-1">


                                                                <a
                                                                    href="../uploads/soportes_facturas/<?= rawurlencode($nombreArchivo) ?>"
                                                                    target="_blank"
                                                                    class="btn btn-info btn-sm"
                                                                    title="Ver archivo"
                                                                >

                                                                    <i class="bi bi-eye"></i>

                                                                </a>


                                                                <a
                                                                    href="eliminar_soporte.php?id=<?= $soporte['id'] ?>"
                                                                    class="btn btn-danger btn-sm"
                                                                    title="Eliminar soporte"
                                                                    onclick="return confirm('¿Está seguro de eliminar este soporte?');"
                                                                >

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


                                <form
                                    action="agregar_soporte.php"
                                    method="POST"
                                    enctype="multipart/form-data"
                                >

                                    <input
                                        type="hidden"
                                        name="factura_id"
                                        value="<?= $factura['id'] ?>"
                                    >


                                    <label
                                        for="soporte"
                                        class="form-label"
                                    >

                                        Agregar nuevo soporte

                                    </label>


                                    <div class="row g-2">

                                        <div class="col-md-9">

                                            <input
                                                type="file"
                                                name="soporte"
                                                id="soporte"
                                                class="form-control"
                                                accept=".pdf,.jpg,.jpeg,.png"
                                                required
                                            >

                                            <div class="form-text">

                                                Formatos permitidos:
                                                PDF, JPG, JPEG y PNG.
                                                Máximo 10 MB.

                                            </div>

                                        </div>


                                        <div class="col-md-3">

                                            <button
                                                type="submit"
                                                class="btn btn-success w-100"
                                            >

                                                <i class="bi bi-upload"></i>

                                                Subir Soporte

                                            </button>

                                        </div>

                                    </div>

                                </form>


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


<script>

document.addEventListener('DOMContentLoaded', function () {

    const formulario =
        document.getElementById('formFactura');


    const valor =
        document.getElementById('valor');


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
    // IVA
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
    // IMPOCONSUMO
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
    // RETENCIÓN
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