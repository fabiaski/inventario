<?php

require_once __DIR__ . '/../../config/conexion.php';

// ==================================================
// VALIDAR ID
// ==================================================

$personaId = (int) ($_GET['id'] ?? 0);

if ($personaId <= 0) {
    header('Location: index.php');
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
$stmtPersona->bind_param("i", $personaId);
$stmtPersona->execute();

$resultadoPersona = $stmtPersona->get_result();
$persona = $resultadoPersona->fetch_assoc();

$stmtPersona->close();

if (!$persona) {
    header('Location: index.php');
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
        fecha,
        estado
    FROM favores_movimientos
    WHERE persona_id = ?
    ORDER BY fecha DESC, id DESC
";

$stmtMovimientos = $conexion->prepare($sqlMovimientos);
$stmtMovimientos->bind_param("i", $personaId);
$stmtMovimientos->execute();

$movimientos = $stmtMovimientos->get_result();


// ==================================================
// CALCULAR TOTAL PENDIENTE
// ==================================================

$sqlTotal = "
    SELECT
        COALESCE(SUM(valor), 0) AS total
    FROM favores_movimientos
    WHERE persona_id = ?
    AND estado = 'pendiente'
";

$stmtTotal = $conexion->prepare($sqlTotal);
$stmtTotal->bind_param("i", $personaId);
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
                        <div class="panel-header d-flex justify-content-between align-items-center">

                            <div>

                                <h2 class="page-title">

                                    <?= htmlspecialchars($persona['nombre']) ?>

                                </h2>

                                <?php if ($persona['tipo'] === 'me_debe'): ?>

                                <span class="badge badge-success">
                                    Me debe
                                </span>

                                <?php else: ?>

                                <span class="badge badge-warning">
                                    Le debo
                                </span>

                                <?php endif; ?>

                            </div>

                            <div>

                                <a href="favores.php" class="btn btn-secondary">
                                    Volver
                                </a>

                                <a href="pdf.php?id=<?= $personaId ?>" class="btn btn-danger" target="_blank">
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


                        <!-- ==========================================
                 MOVIMIENTOS
            ========================================== -->

                        <div class="card">

                            <div class="card-body">

                                <h4 class="card-title ">
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
                                                    Estado
                                                </th>

                                                <th>
                                                    Acciones
                                                </th>

                                            </tr>

                                        </thead>

                                        <tbody>

                                            <?php while ($movimiento = $movimientos->fetch_assoc()): ?>

                                            <tr>

                                                <td>

                                                    <?= date(
                                                    'd/m/Y',
                                                    strtotime($movimiento['fecha'])
                                                ) ?>

                                                </td>

                                                <td>

                                                    <?= htmlspecialchars(
                                                    $movimiento['descripcion']
                                                ) ?>

                                                </td>

                                                <td>

                                                    $<?= number_format(
                                                    $movimiento['valor'],
                                                    0,
                                                    ',',
                                                    '.'
                                                ) ?>

                                                </td>

                                                <td>

                                                    <?php if ($movimiento['estado'] === 'pendiente'): ?>

                                                    <span class="badge badge-danger">
                                                        Pendiente
                                                    </span>

                                                    <?php else: ?>

                                                    <span class="badge badge-success">
                                                        Pagado
                                                    </span>

                                                    <?php endif; ?>

                                                </td>

                                                <td>

                                                    <button type="button"
                                                        class="btn btn-sm btn-secondary btn-editar-movimiento"
                                                        title="Editar" data-id="<?= $movimiento['id'] ?>"
                                                        data-descripcion="<?= htmlspecialchars($movimiento['descripcion'], ENT_QUOTES) ?>"
                                                        data-valor="<?= $movimiento['valor'] ?>"
                                                        data-fecha="<?= $movimiento['fecha'] ?>"
                                                        data-estado="<?= $movimiento['estado'] ?>">
                                                        <i class="mdi mdi-pencil"></i>
                                                    </button>

                                                    <?php if ($movimiento['estado'] === 'pendiente'): ?>

                                                    <a href="pagar_movimiento.php?id=<?= $movimiento['id'] ?>&persona_id=<?= $personaId ?>"
                                                        class="btn btn-sm btn-success"
                                                        onclick="return confirm('¿Marcar este movimiento como pagado?')"
                                                        title="Marcar como pagado">
                                                        <i class="mdi mdi-check"></i>
                                                    </a>

                                                    <?php endif; ?>


                                                    <a href="eliminar_movimiento.php?id=<?= $movimiento['id'] ?>&persona_id=<?= $personaId ?>"
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

                    <script>

document.addEventListener('DOMContentLoaded', function () {

    const modalElemento = document.getElementById('modalMovimiento');

    const modal = new bootstrap.Modal(modalElemento);

    const form = document.getElementById('formMovimiento');

    const idMovimiento = document.getElementById('idMovimiento');
    const descripcion = document.getElementById('descripcionMovimiento');
    const valor = document.getElementById('valorMovimiento');
    const fecha = document.getElementById('fechaMovimiento');

    const titulo = document.getElementById('tituloModalMovimiento');

    const btnEliminar = document.getElementById('btnEliminarMovimiento');


    // ==================================================
    // FORMATO DEL VALOR
    // ==================================================

    valor.addEventListener('input', function () {

        let numero = this.value.replace(/\D/g, '');

        if (numero !== '') {
            this.value = Number(numero).toLocaleString('es-CO');
        }

    });


    // ==================================================
    // NUEVO MOVIMIENTO
    // ==================================================

    document
        .querySelector('[data-bs-target="#modalMovimiento"]')
        .addEventListener('click', function () {

            form.action = 'agregar_movimiento.php';

            idMovimiento.value = '';

            descripcion.value = '';

            valor.value = '';

            fecha.value = '<?= date('Y-m-d') ?>';

            titulo.innerText = 'Nuevo movimiento';

            btnEliminar.classList.add('d-none');

        });


    // ==================================================
    // EDITAR MOVIMIENTO
    // ==================================================

    document
        .querySelectorAll('.btn-editar-movimiento')
        .forEach(function (boton) {

            boton.addEventListener('click', function () {

                const id = this.dataset.id;
                const descripcionDato = this.dataset.descripcion;
                const valorDato = this.dataset.valor;
                const fechaDato = this.dataset.fecha;

                form.action = 'editar_movimiento.php';

                idMovimiento.value = id;

                descripcion.value = descripcionDato;

                valor.value = Number(valorDato).toLocaleString('es-CO');

                fecha.value = fechaDato;

                titulo.innerText = 'Editar movimiento';

                btnEliminar.classList.remove('d-none');

                modal.show();

            });

        });


    // ==================================================
    // ELIMINAR MOVIMIENTO
    // ==================================================

    btnEliminar.addEventListener('click', function () {

        const id = idMovimiento.value;

        if (!id) {
            return;
        }

        if (confirm('¿Está seguro de eliminar este movimiento?')) {

            window.location.href =
                'eliminar_movimiento.php?id=' +
                id +
                '&persona_id=<?= $personaId ?>';

        }

    });

});

</script>
                    <!-- MODAL MOVIMIENTO -->

<div class="modal fade" id="modalMovimiento" tabindex="-1">

    <div class="modal-dialog">

        <div class="modal-content">

            <form action="agregar_movimiento.php" method="POST" id="formMovimiento">

                <input type="hidden" name="persona_id" value="<?= $personaId ?>">
                <input type="hidden" name="id" id="idMovimiento">

                <div class="modal-header">

                    <h5 class="modal-title" id="tituloModalMovimiento">
                        Nuevo movimiento
                    </h5>

                    <button
                        type="button"
                        class="btn-close"
                        data-bs-dismiss="modal">
                    </button>

                </div>

                <div class="modal-body">

                    <div class="mb-3">

                        <label class="form-label">
                            Descripción
                        </label>

                        <input
                            type="text"
                            name="descripcion"
                            id="descripcionMovimiento"
                            class="form-control"
                            maxlength="255"
                            placeholder="Ej: Préstamo"
                            required
                        >

                    </div>

                    <div class="mb-3">

                        <label class="form-label">
                            Valor
                        </label>

                        <input
                            type="text"
                            name="valor"
                            id="valorMovimiento"
                            class="form-control"
                            placeholder="Ej: 50.000"
                            inputmode="numeric"
                            required
                        >

                        <small class="text-muted">
                            Ingrese el valor sin decimales.
                        </small>

                    </div>

                    <div class="mb-3">

                        <label class="form-label">
                            Fecha
                        </label>

                        <input
                            type="date"
                            name="fecha"
                            id="fechaMovimiento"
                            class="form-control"
                            required
                        >

                    </div>

                </div>

                <div class="modal-footer">

                    <button
                        type="button"
                        class="btn btn-danger d-none"
                        id="btnEliminarMovimiento"
                    >
                        <i class="mdi mdi-delete"></i>
                        Eliminar
                    </button>

                    <button
                        type="button"
                        class="btn btn-secondary"
                        data-bs-dismiss="modal"
                    >
                        Cancelar
                    </button>

                    <button
                        type="submit"
                        class="btn btn-primary"
                    >
                        <i class="bi bi-save"></i>
                        Guardar
                    </button>

                </div>

            </form>

        </div>

    </div>

</div>

                    <?php require_once __DIR__ . '/../../includes/footer.php'; ?>

                </div>

                

                <!-- MODAL NUEVO MOVIMIENTO -->

                <div class="modal fade" id="modalMovimiento" tabindex="-1">

                    <div class="modal-dialog">

                        <div class="modal-content">

                            <form action="agregar_movimiento.php" method="POST" id="formMovimiento">

                                <input type="hidden" name="persona_id" value="<?= $personaId ?>">

                                <div class="modal-header">

                                    <h5 class="modal-title">
                                        Nuevo movimiento
                                    </h5>

                                    <button type="button" class="btn-close" data-bs-dismiss="modal">
                                    </button>

                                </div>

                                <div class="modal-body">

                                    <div class="mb-3">

                                        <label for="descripcionMovimiento" class="form-label">
                                            Descripción
                                        </label>

                                        <input type="text" name="descripcion" id="descripcionMovimiento"
                                            class="form-control" maxlength="255" placeholder="Ej: Préstamo" required>

                                    </div>

                                    <div class="mb-3">

                                        <label for="valorMovimiento" class="form-label">
                                            Valor
                                        </label>

                                        <input type="text" name="valor" id="valorMovimiento" class="form-control"
                                            placeholder="Ej: 50.000" inputmode="numeric" required>

                                        <small class="text-muted">
                                            Ingrese el valor sin decimales.
                                        </small>

                                    </div>

                                    <div class="mb-3">

                                        <label for="fechaMovimiento" class="form-label">
                                            Fecha
                                        </label>

                                        <input type="date" name="fecha" id="fechaMovimiento" class="form-control"
                                            value="<?= date('Y-m-d') ?>" required>

                                    </div>

                                </div>

                                <div class="modal-footer">

                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                        Cancelar
                                    </button>

                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-save"></i>
                                        Guardar
                                    </button>

                                </div>

                            </form>

                        </div>

                    </div>

                </div>

            </div>


            <?php require_once __DIR__ . '/../../includes/scripts.php'; ?>