<?php

require_once __DIR__ . '/../../config/conexion.php';


// ==================================================
// VALIDAR ID
// ==================================================

$personaId = (int) ($_GET['id'] ?? 0);

if ($personaId <= 0) {

    header('Location: ' . BASE_URL . 'favores');
    exit;
}


// ==================================================
// CONSULTAR PERSONA
// ==================================================

$sqlPersona = "
    SELECT
        id,
        nombre,
        tipo
    FROM favores_personas
    WHERE id = ?
    LIMIT 1
";

$stmtPersona = $conexion->prepare($sqlPersona);

$stmtPersona->bind_param(
    "i",
    $personaId
);

$stmtPersona->execute();

$resultadoPersona = $stmtPersona->get_result();

$persona = $resultadoPersona->fetch_assoc();

$stmtPersona->close();


if (!$persona) {

    header('Location: ' . BASE_URL . 'favores');
    exit;
}


// ==================================================
// CONSULTAR MOVIMIENTOS
// ==================================================

$sqlMovimientos = "
    SELECT
        id,
        descripcion,
        valor,
        tipo,
        fecha
    FROM favores_movimientos
    WHERE persona_id = ?
    ORDER BY fecha DESC, id DESC
";

$stmtMovimientos = $conexion->prepare($sqlMovimientos);

$stmtMovimientos->bind_param(
    "i",
    $personaId
);

$stmtMovimientos->execute();

$movimientos = $stmtMovimientos->get_result();


// ==================================================
// CALCULAR TOTAL
// ==================================================

$sqlTotal = "
    SELECT
        COALESCE(
            SUM(
                CASE
                    WHEN tipo = 'cargo' THEN valor
                    WHEN tipo = 'abono' THEN -valor
                    ELSE 0
                END
            ),
            0
        ) AS total
    FROM favores_movimientos
    WHERE persona_id = ?
";

$stmtTotal = $conexion->prepare($sqlTotal);

$stmtTotal->bind_param(
    "i",
    $personaId
);

$stmtTotal->execute();

$resultadoTotal = $stmtTotal->get_result();

$total = $resultadoTotal->fetch_assoc()['total'];

$stmtTotal->close();


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
                        <?php if (isset($_GET['mensaje']) && $_GET['mensaje'] === 'actualizado'): ?>

                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            Movimiento actualizado correctamente.
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>

                        <?php endif; ?>

                        <?php if (isset($_GET['error'])): ?>

                        <div class="alert alert-danger alert-dismissible fade show" role="alert">

                            <?php

        switch ($_GET['error']) {

            case 'descripcion':
                echo 'La descripción es obligatoria.';
                break;

            case 'valor':
                echo 'El valor debe ser mayor que cero.';
                break;

            case 'fecha':
                echo 'La fecha es obligatoria.';
                break;

            case 'actualizar':
                echo 'No fue posible actualizar el movimiento.';
                break;

            default:
                echo 'Ocurrió un error.';
                break;
        }

        ?>

                            <button type="button" class="btn-close" data-bs-dismiss="alert">
                            </button>

                        </div>

                        <?php endif; ?>

                        <!-- ==================================================
                             ENCABEZADO
                        ================================================== -->

                        <div class="panel-header d-flex justify-content-between align-items-center">

                            <div>

                                <h2 class="page-title">

                                    <?= htmlspecialchars($persona['nombre']) ?>

                                </h2>


                                <?php if ($persona['tipo'] === 'prestamo'): ?>

                                <span class="badge badge-success">
                                    Préstamo
                                </span>

                                <?php else: ?>

                                <span class="badge badge-warning">
                                    A pagar
                                </span>

                                <?php endif; ?>

                            </div>


                            <div>

                                <a href="/inventario/favores" class="btn btn-secondary">

                                    Volver

                                </a>


                                <a href="/inventario/favores/pdf/<?= $personaId ?>" class="btn btn-danger"
                                    target="_blank">

                                    <i class="mdi mdi-file-pdf"></i>

                                    Generar PDF

                                </a>


                                <button type="button" class="btn btn-primary" data-bs-toggle="modal"
                                    data-bs-target="#modalMovimiento">

                                    <i class="mdi mdi-plus"></i>

                                    Nuevo movimiento

                                </button>

                            </div>

                        </div>


                        <!-- ==================================================
                             TOTAL
                        ================================================== -->

                        <div class="row mb-4 mt-3">

                            <div class="col-md-6 col-lg-4">

                                <div class="card" style="border: 1px solid #dee2e6; border-radius: 8px;">

                                    <div class="card-body">

                                        <p class="card-text mb-1">

                                            Total pendiente

                                        </p>

                                        <h2 class="mb-0">

                                            $<?= number_format(
                                                $total,
                                                0,
                                                ',',
                                                '.'
                                            ) ?>

                                        </h2>

                                    </div>

                                </div>

                            </div>

                        </div>


                        <!-- ==================================================
                             MOVIMIENTOS
                        ================================================== -->

                        <div class="card">

                            <div class="card-body">

                                <h4 class="card-title">

                                    Movimientos

                                </h4>

                                <hr>


                                <?php if ($movimientos->num_rows > 0): ?>

                                <div class="table-responsive">

                                    <table class="table table-hover">

                                        <thead>

                                            <tr>

                                                <th>
                                                    Fecha
                                                </th>

                                                <th>
                                                    Descripción
                                                </th>

                                                <th>
                                                    Valor
                                                </th>

                                                <th>
                                                    Acciones
                                                </th>

                                            </tr>

                                        </thead>


                                        <tbody>

                                            <?php while ($movimiento = $movimientos->fetch_assoc()): ?>

                                            <tr>

                                                <!-- FECHA -->

                                                <td>

                                                    <?= date(
                                                                'd/m/Y',
                                                                strtotime($movimiento['fecha'])
                                                            ) ?>

                                                </td>


                                                <!-- DESCRIPCIÓN -->

                                                <td>

                                                    <?= htmlspecialchars(
                                                                $movimiento['descripcion']
                                                            ) ?>

                                                </td>


                                                <!-- VALOR -->

                                                <td>

                                                    <?php if ($movimiento['tipo'] === 'abono'): ?>

                                                    -$<?= number_format(
                                                                    $movimiento['valor'],
                                                                    0,
                                                                    ',',
                                                                    '.'
                                                                ) ?>

                                                    <?php else: ?>

                                                    $<?= number_format(
                                                                    $movimiento['valor'],
                                                                    0,
                                                                    ',',
                                                                    '.'
                                                                ) ?>

                                                    <?php endif; ?>

                                                </td>


                                                <!-- ACCIONES -->

                                                <td>

                                                    <button type="button"
                                                        class="btn btn-sm btn-secondary btn-editar-movimiento"
                                                        title="Editar" data-id="<?= $movimiento['id'] ?>"
                                                        data-descripcion="<?= htmlspecialchars(
                                                                    $movimiento['descripcion'],
                                                                    ENT_QUOTES
                                                                ) ?>" data-valor="<?= $movimiento['valor'] ?>"
                                                        data-tipo="<?= $movimiento['tipo'] ?>"
                                                        data-fecha="<?= $movimiento['fecha'] ?>">

                                                        <i class="mdi mdi-pencil"></i>

                                                    </button>


                                                    <a href="/inventario/favores/movimiento/eliminar?id=<?= $movimiento['id'] ?>&persona_id=<?= $personaId ?>"
                                                        class="btn btn-sm btn-danger"
                                                        onclick="return confirm('¿Está seguro de eliminar este movimiento?')"
                                                        title="Eliminar">

                                                        <i class="mdi mdi-delete"></i>

                                                    </a>

                                                </td>

                                            </tr>

                                            <?php endwhile; ?>

                                        </tbody>

                                    </table>

                                </div>

                                <?php else: ?>

                                <div class="alert alert-info">

                                    Esta persona todavía no tiene movimientos registrados.

                                </div>

                                <?php endif; ?>

                            </div>

                        </div>

                    </div>


                    <!-- ==================================================
                         SCRIPT DEL MODAL
                    ================================================== -->

                    <script>
                    document.addEventListener('DOMContentLoaded', function() {

                        const modalElemento =
                            document.getElementById('modalMovimiento');

                        const modal =
                            new bootstrap.Modal(modalElemento);

                        const form =
                            document.getElementById('formMovimiento');

                        const idMovimiento =
                            document.getElementById('idMovimiento');

                        const descripcion =
                            document.getElementById('descripcionMovimiento');

                        const valor =
                            document.getElementById('valorMovimiento');

                        const abono =
                            document.getElementById('abonoMovimiento');

                        const fecha =
                            document.getElementById('fechaMovimiento');

                        const titulo =
                            document.getElementById('tituloModalMovimiento');

                        const btnEliminar =
                            document.getElementById('btnEliminarMovimiento');


                        // ==================================================
                        // FORMATO DEL VALOR
                        // ==================================================

                        valor.addEventListener('input', function() {

                            let numero =
                                this.value.replace(/\D/g, '');

                            if (numero !== '') {

                                this.value =
                                    Number(numero).toLocaleString('es-CO');

                            }

                        });


                        // ==================================================
                        // NUEVO MOVIMIENTO
                        // ==================================================

                        document
                            .querySelector('[data-bs-target="#modalMovimiento"]')
                            .addEventListener('click', function() {

                                form.action =
                                    '/inventario/favores/movimiento/agregar/<?= $personaId ?>';

                                idMovimiento.value = '';

                                descripcion.value = '';

                                valor.value = '';

                                abono.checked = false;

                                fecha.value =
                                    '<?= date('Y-m-d') ?>';

                                titulo.innerText =
                                    'Nuevo movimiento';

                                btnEliminar.classList.add('d-none');

                            });


                        // ==================================================
                        // EDITAR MOVIMIENTO
                        // ==================================================

                        document
                            .querySelectorAll('.btn-editar-movimiento')
                            .forEach(function(boton) {

                                boton.addEventListener('click', function() {

                                    const id =
                                        this.dataset.id;

                                    const descripcionDato =
                                        this.dataset.descripcion;

                                    const valorDato =
                                        this.dataset.valor;

                                    const tipoDato =
                                        this.dataset.tipo;

                                    const fechaDato =
                                        this.dataset.fecha;


                                    form.action =
                                        '/inventario/favores/movimiento/editar';

                                    idMovimiento.value =
                                        id;

                                    descripcion.value =
                                        descripcionDato;

                                    valor.value =
                                        Number(valorDato)
                                        .toLocaleString('es-CO');

                                    fecha.value =
                                        fechaDato;

                                    abono.checked =
                                        tipoDato === 'abono';

                                    titulo.innerText =
                                        'Editar movimiento';

                                    btnEliminar.classList.remove('d-none');

                                    modal.show();

                                });

                            });


                        // ==================================================
                        // ELIMINAR MOVIMIENTO
                        // ==================================================

                        btnEliminar.addEventListener('click', function() {

                            const id =
                                idMovimiento.value;

                            if (!id) {
                                return;
                            }


                            if (
                                confirm(
                                    '¿Está seguro de eliminar este movimiento?'
                                )
                            ) {

                                window.location.href =
                                    '/inventario/favores/movimiento/eliminar?id=' +
                                    id +
                                    '&persona_id=<?= $personaId ?>';

                            }

                        });

                    });
                    </script><script>
document.addEventListener('DOMContentLoaded', function() {

    const modalElemento =
        document.getElementById('modalMovimiento');

    const modal =
        new bootstrap.Modal(modalElemento);

    const form =
        document.getElementById('formMovimiento');

    const idMovimiento =
        document.getElementById('idMovimiento');

    const descripcion =
        document.getElementById('descripcionMovimiento');

    const valor =
        document.getElementById('valorMovimiento');

    const abono =
        document.getElementById('abonoMovimiento');

    const fecha =
        document.getElementById('fechaMovimiento');

    const titulo =
        document.getElementById('tituloModalMovimiento');


    // ==================================================
    // FORMATO DEL VALOR
    // ==================================================

    valor.addEventListener('input', function() {

        let numero =
            this.value.replace(/\D/g, '');

        if (numero !== '') {

            this.value =
                Number(numero).toLocaleString('es-CO');

        }

    });


    // ==================================================
    // NUEVO MOVIMIENTO
    // ==================================================

    document
        .querySelector('[data-bs-target="#modalMovimiento"]')
        .addEventListener('click', function() {

            form.action =
                '/inventario/favores/movimiento/agregar/<?= $personaId ?>';

            idMovimiento.value = '';

            descripcion.value = '';

            valor.value = '';

            abono.checked = false;

            fecha.value =
                '<?= date('Y-m-d') ?>';

            titulo.innerText =
                'Nuevo movimiento';

        });


    // ==================================================
    // EDITAR MOVIMIENTO
    // ==================================================

    document
        .querySelectorAll('.btn-editar-movimiento')
        .forEach(function(boton) {

            boton.addEventListener('click', function() {

                const id =
                    this.dataset.id;

                const descripcionDato =
                    this.dataset.descripcion;

                const valorDato =
                    this.dataset.valor;

                const tipoDato =
                    this.dataset.tipo;

                const fechaDato =
                    this.dataset.fecha;


                form.action =
                    '/inventario/favores/movimiento/editar';

                idMovimiento.value =
                    id;

                descripcion.value =
                    descripcionDato;

                valor.value =
                    Number(valorDato)
                    .toLocaleString('es-CO');

                fecha.value =
                    fechaDato;

                abono.checked =
                    tipoDato === 'abono';

                titulo.innerText =
                    'Editar movimiento';

                modal.show();

            });

        });

});
</script>


                    <?php require_once __DIR__ . '/../../includes/footer.php'; ?>

                </div>

            </div>

        </div>

    </div>

</div>


<!-- ==================================================
     MODAL MOVIMIENTO
================================================== -->

<div class="modal fade" id="modalMovimiento" tabindex="-1">

    <div class="modal-dialog">

        <div class="modal-content">

            <form action="/inventario/favores/movimiento/agregar/<?= $personaId ?>" method="POST" id="formMovimiento">


                <input type="hidden" name="persona_id" value="<?= $personaId ?>">


                <input type="hidden" name="id" id="idMovimiento">


                <!-- ==================================================
                     HEADER
                ================================================== -->

                <div class="modal-header">

                    <h5 class="modal-title" id="tituloModalMovimiento">

                        Nuevo movimiento

                    </h5>


                    <button type="button" class="btn-close" data-bs-dismiss="modal">

                    </button>

                </div>


                <!-- ==================================================
                     BODY
                ================================================== -->

                <div class="modal-body">


                    <!-- DESCRIPCIÓN -->

                    <div class="mb-3">

                        <label class="form-label">

                            Descripción

                        </label>


                        <input type="text" name="descripcion" id="descripcionMovimiento" class="form-control"
                            maxlength="255" placeholder="Ej: Préstamo" required>

                    </div>


                    <!-- VALOR -->

                    <div class="mb-3">

                        <label class="form-label">

                            Valor

                        </label>


                        <input type="text" name="valor" id="valorMovimiento" class="form-control"
                            placeholder="Ej: 50.000" inputmode="numeric" required>


                        <small class="text-muted">

                            Ingrese el valor sin decimales.

                        </small>

                    </div>


                    <!-- ABONO -->

                   <div class="mb-3">
    <div class="form-check" style="margin-left: 25px;">
        <input type="checkbox"
            name="abono"
            id="abonoMovimiento"
            class="form-check-input">

        <label for="abonoMovimiento"
            class="form-check-label"
            style="font-size: 16px; margin-left: 2px;">
            Abonar
        </label>
    </div>
</div>


                    <!-- FECHA -->

                    <div class="mb-3">

                        <label class="form-label">

                            Fecha

                        </label>


                        <input type="date" name="fecha" id="fechaMovimiento" class="form-control" required>

                    </div>

                </div>


                <!-- ==================================================
                     FOOTER
                ================================================== -->

                <div class="modal-footer">

    <button type="button"
        class="btn btn-secondary"
        data-bs-dismiss="modal">

        Cancelar

    </button>


    <button type="submit"
        class="btn btn-primary">

        <i class="bi bi-save"></i>

        Guardar

    </button>

</div>

            </form>

        </div>

    </div>

</div>


<?php require_once __DIR__ . '/../../includes/scripts.php'; ?>