<?php

require_once __DIR__ . '/../../config/conexion.php';


// ==================================================
// VALIDAR ID DEL PROCESO
// ==================================================

$procesoId = (int) ($_GET['id'] ?? 0);

if ($procesoId <= 0) {
    header('Location: index.php');
    exit;
}


// ==================================================
// CONSULTAR PROCESO
// ==================================================

$sqlProceso = "
    SELECT
        id,
        nombre_contrato,
        fecha_entrega,
        estado
    FROM procesos
    WHERE id = ?
    LIMIT 1
";

$stmtProceso = $conexion->prepare($sqlProceso);

if (!$stmtProceso) {
    die('Error al preparar la consulta del proceso: ' . $conexion->error);
}

$stmtProceso->bind_param('i', $procesoId);
$stmtProceso->execute();

$resultadoProceso = $stmtProceso->get_result();

$proceso = $resultadoProceso->fetch_assoc();

$stmtProceso->close();


// ==================================================
// VALIDAR QUE EXISTA
// ==================================================

if (!$proceso) {
    header('Location: index.php');
    exit;
}


// ==================================================
// PRODUCTOS DEL PROCESO
// ==================================================

$sqlProductos = "
    SELECT
        pp.id AS proceso_producto_id,
        pp.producto_id,
        pp.comprado,
        pp.fecha_compra,

        pr.producto,
        pr.unidad_medida

    FROM proceso_productos pp

    INNER JOIN productos pr
        ON pr.id = pp.producto_id

    WHERE pp.proceso_id = ?

    ORDER BY pr.producto ASC
";

$stmtProductos = $conexion->prepare($sqlProductos);

if (!$stmtProductos) {
    die('Error al preparar la consulta de productos: ' . $conexion->error);
}

$stmtProductos->bind_param('i', $procesoId);
$stmtProductos->execute();

$resultadoProductos = $stmtProductos->get_result();


// ==================================================
// CALCULAR PROGRESO
// ==================================================

$totalProductos = $resultadoProductos->num_rows;
$productosComprados = 0;


// Guardamos los productos para poder recorrerlos
// nuevamente después.

$productos = [];

while ($producto = $resultadoProductos->fetch_assoc()) {

    $productos[] = $producto;

    if ((int) $producto['comprado'] === 1) {
        $productosComprados++;
    }
}

$stmtProductos->close();


if ($totalProductos > 0) {

    $porcentaje = round(
        ($productosComprados / $totalProductos) * 100
    );

} else {

    $porcentaje = 0;

}


// ==================================================
// DÍAS RESTANTES
// ==================================================
// ==================================================
// DÍAS RESTANTES
// ==================================================

$fechaEntrega = new DateTime(
    date('Y-m-d', strtotime($proceso['fecha_entrega']))
);

$fechaActual = new DateTime(
    date('Y-m-d')
);

$diferencia = $fechaActual->diff($fechaEntrega);

if ($fechaActual <= $fechaEntrega) {

    $diasRestantes = $diferencia->days;

} else {

    $diasRestantes = -$diferencia->days;

}

// ==================================================
// INCLUDES
// ==================================================

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';
require_once __DIR__ . '/../includes/sidebar.php';
?>

<div class="container-fluid page-body-wrapper">

    <div class="main-panel">

        <div class="content-wrapper">


            <!-- ==================================================
                 ENCABEZADO
            ================================================== -->

            <div class="panel-header mb-4">

                <div>

                    <h2 class="h5 mb-1 section-title">

                        <i class="bi bi-clipboard-check"></i>

                        <?= htmlspecialchars($proceso['nombre_contrato']) ?>

                    </h2>

                    <p class="text-muted mb-0">

                        Control de productos y seguimiento de compra.

                    </p>

                </div>


                <div>

                    <a href="index.php" class="btn btn-outline-secondary">

                        <i class="bi bi-arrow-left"></i>

                        Volver

                    </a>

                </div>

            </div>


            <!-- ==================================================
                 INFORMACIÓN DEL PROCESO
            ================================================== -->

            <div class="row g-3 mb-4">


                <!-- FECHA DE ENTREGA -->

                <div class="col-md-4">

                    <div class="card border-0 shadow-sm h-100">

                        <div class="card-body">

                            <small class="text-muted">
                                Fecha de entrega
                            </small>

                            <h5 class="mb-0 mt-1">

                                <i class="bi bi-calendar-event"></i>

                                <?= date(
                                    'd/m/Y',
                                    strtotime($proceso['fecha_entrega'])
                                ) ?>

                            </h5>

                        </div>

                    </div>

                </div>


                <!-- DÍAS RESTANTES -->

                <div class="col-md-4">

                    <div class="card border-0 shadow-sm h-100">

                        <div class="card-body">

                            <small class="text-muted">
                                Tiempo restante
                            </small>

                            <h5 class="mb-0 mt-1">

                                <?php if ($diasRestantes > 3): ?>

                                <span class="text-success">

                                    <i class="bi bi-clock"></i>

                                    <?= $diasRestantes ?> días

                                </span>

                                <?php elseif ($diasRestantes >= 0): ?>

                                <span class="text-danger">

                                    <i class="bi bi-exclamation-triangle"></i>

                                    <?= $diasRestantes ?> días

                                </span>

                                <?php else: ?>

                                <span class="text-danger">

                                    <i class="bi bi-exclamation-octagon"></i>

                                    Fecha vencida

                                </span>

                                <?php endif; ?>

                            </h5>

                        </div>

                    </div>

                </div>


                <!-- ESTADO -->

                <div class="col-md-4">

                    <div class="card border-0 shadow-sm h-100">

                        <div class="card-body">

                            <small class="text-muted">
                                Estado
                            </small>

                            <h5 class="mb-0 mt-1">

                                <?php if ($proceso['estado'] === 'finalizado'): ?>

                                <span class="badge bg-success">

                                    <i class="bi bi-check-circle"></i>

                                    Finalizado

                                </span>

                                <?php else: ?>

                                <span class="badge bg-warning text-dark">

                                    <i class="bi bi-clock"></i>

                                    En proceso

                                </span>

                                <?php endif; ?>

                            </h5>

                        </div>

                    </div>

                </div>

            </div>


            <!-- ==================================================
                 PROGRESO
            ================================================== -->

            <div class="card border-0 shadow-sm mb-4">

                <div class="card-body">

                    <div class="d-flex justify-content-between align-items-center mb-2">

                        <div>

                            <strong>
                                Progreso de compras
                            </strong>

                            <div class="text-muted small">

                                <?= $productosComprados ?>
                                de
                                <?= $totalProductos ?>
                                productos comprados

                            </div>

                        </div>

                        <strong class="fs-5">

                            <?= $porcentaje ?>%

                        </strong>

                    </div>


                    <div class="progress" style="height: 12px;">

                        <div class="progress-bar" role="progressbar" style="width: <?= $porcentaje ?>%;"
                            aria-valuenow="<?= $porcentaje ?>" aria-valuemin="0" aria-valuemax="100"></div>

                    </div>

                </div>

            </div>


            <!-- ==================================================
     AGREGAR PRODUCTO
================================================== -->

            <?php if ($proceso['estado'] === 'proceso'): ?>

            <div class="card border-0 shadow-sm mb-4">


                <div class="card-body">

                    <div class="d-flex justify-content-between align-items-center mb-3">

                        <div>
                            <h5 class="mb-1">

                                <i class="bi bi-plus-circle"></i>

                                Agregar Producto

                            </h5>
                            <small class="text-muted">

                                Busque un producto existente en el inventario.

                            </small>
                        </div>

                    </div>
                    <div class="row g-3 align-items-end">

                        <!-- PRODUCTO -->

                        <div class="col-md-10 position-relative">

                            <input type="text" id="buscarProducto" class="form-control" autocomplete="off"
                                placeholder="Buscar producto...">


                            <input type="hidden" id="producto_id">


                            <div id="listaProductos" class="list-group position-absolute w-100 shadow" style="
                        display:none;
                        z-index:9999;
                        max-height:250px;
                        overflow:auto;
                    "></div>

                        </div>


                        <!-- BOTÓN -->

                        <div class="col-md-2">

                            <button type="button" id="btnAgregar" class="btn btn-primary w-100">

                                <i class="bi bi-plus-circle"></i>

                                Agregar

                            </button>

                        </div>


                    </div>

                </div>

            </div>

            <?php endif; ?>


            <!-- ==================================================
                 LISTADO DE PRODUCTOS
            ================================================== -->

            <div class="card border-0 shadow-sm">

                <div class="card-body">

                    <div class="mb-3">

                        <h5 class="mb-1">

                            <i class="bi bi-box-seam"></i>

                            Productos del proceso

                        </h5>

                        <small class="text-muted">

                            Marque los productos comprados y luego guarde los cambios.

                        </small>

                    </div>


                    <?php if (count($productos) > 0): ?>

                    <div class="table-responsive">

                        <table class="table table-hover align-middle">

                            <thead>

                                <tr>

                                    <th>
                                        Producto
                                    </th>

                                    <th>
                                        Estado
                                    </th>

                                    <th class="text-center">
                                        Comprado
                                    </th>

                                </tr>

                            </thead>


                            <tbody>

                                <?php foreach ($productos as $producto): ?>

                                <tr>

                                    <!-- PRODUCTO -->

                                    <td>

                                        <strong>

                                            <?= htmlspecialchars(
                                                    $producto['producto']
                                                ) ?>

                                        </strong>

                                    </td>


                                    <!-- ESTADO -->

                                    <td>

                                        <?php if ((int) $producto['comprado'] === 1): ?>

                                        <span class="badge bg-success">

                                            <i class="bi bi-check-circle"></i>

                                            Comprado

                                        </span>

                                        <?php else: ?>

                                        <span class="badge bg-warning text-dark">

                                            <i class="bi bi-hourglass-split"></i>

                                            Pendiente

                                        </span>

                                        <?php endif; ?>

                                    </td>


                                    <!-- CHECKBOX -->

                                    <!-- CHECKBOX -->

                                    <td class="text-center">

                                        <input type="checkbox" class="form-check-input producto-checkbox"
                                            style="width: 20px; height: 20px; cursor: pointer;"
                                            data-id="<?= (int) $producto['proceso_producto_id'] ?>"
                                            <?= (int) $producto['comprado'] === 1 ? 'checked' : '' ?>>

                                    </td>

                                </tr>

                                <?php endforeach; ?>

                            </tbody>

                        </table>

                    </div>


                    <!-- ==================================================
     ACCIONES DE PRODUCTOS
================================================== -->

                    <div class="d-flex justify-content-between align-items-center mt-4">

                        <div>

                            <small class="text-muted">

                                Los cambios realizados se guardarán al presionar
                                uno de los botones.

                            </small>

                        </div>


                        <div class="d-flex gap-2">

                            <!-- GUARDAR -->

                            <button type="button" id="btnGuardarCompras" class="btn btn-primary">

                                <i class="bi bi-save"></i>

                                Guardar cambios

                            </button>


                            <!-- GUARDAR Y WHATSAPP -->

                            <button type="button" id="btnWhatsapp" class="btn btn-success">

                                <i class="bi bi-whatsapp"></i>

                                Guardar y enviar por WhatsApp

                            </button>

                        </div>

                    </div>


                    <!-- ==================================================
                             FINALIZAR
                        ================================================== -->

                    <?php if ($porcentaje === 100): ?>

                    <div class="alert alert-success mt-4">

                        <div class="d-flex justify-content-between align-items-center">

                            <div>

                                <strong>

                                    <i class="bi bi-check-circle"></i>

                                    Todos los productos fueron comprados.

                                </strong>

                                <div class="small mt-1">

                                    El proceso está listo para finalizar.

                                </div>

                            </div>


                            <a href="finalizar.php?id=<?= $procesoId ?>" class="btn btn-success">

                                <i class="bi bi-check-lg"></i>

                                Finalizar proceso

                            </a>

                        </div>

                    </div>

                    <?php endif; ?>


                    <?php else: ?>

                    <div class="text-center py-5">

                        <i class="bi bi-box-seam text-muted" style="font-size: 3rem;"></i>

                        <h6 class="mt-3">

                            No hay productos agregados

                        </h6>

                        <p class="text-muted mb-0">

                            Utilice el buscador para agregar los productos
                            que necesita comprar.

                        </p>

                    </div>

                    <?php endif; ?>

                </div>

            </div>

        </div>


        <script>
        document.addEventListener('DOMContentLoaded', function() {

            // ==================================================
            // ELEMENTOS
            // ==================================================

            const buscarProducto =
                document.getElementById('buscarProducto');

            const productoId =
                document.getElementById('producto_id');

            const listaProductos =
                document.getElementById('listaProductos');

            const btnAgregar =
                document.getElementById('btnAgregar');

            const btnGuardarCompras =
                document.getElementById('btnGuardarCompras');

            const btnWhatsapp =
                document.getElementById('btnWhatsapp');


            // ==================================================
            // BUSCAR PRODUCTOS
            // ==================================================

            if (buscarProducto) {

                buscarProducto.addEventListener('input', function() {

                    const texto =
                        this.value.trim();


                    if (texto.length < 1) {

                        listaProductos.innerHTML = '';

                        listaProductos.style.display = 'none';

                        productoId.value = '';

                        return;

                    }


                    fetch(
                            'buscar_producto.php?q=' +
                            encodeURIComponent(texto)
                        )

                        .then(response => response.json())

                        .then(productos => {

                            listaProductos.innerHTML = '';


                            if (productos.length === 0) {

                                listaProductos.innerHTML = `
                        <div class="list-group-item text-muted">
                            No se encontraron productos.
                        </div>
                    `;

                                listaProductos.style.display = 'block';

                                return;
                            }


                            productos.forEach(producto => {

                                const item =
                                    document.createElement('button');


                                item.type = 'button';

                                item.className =
                                    'list-group-item list-group-item-action';


                                item.innerHTML = `
                        <strong>
                            ${producto.producto}
                        </strong>
                    `;


                                item.addEventListener(
                                    'click',
                                    function() {

                                        buscarProducto.value =
                                            producto.producto;

                                        productoId.value =
                                            producto.id;

                                        listaProductos.innerHTML = '';

                                        listaProductos.style.display = 'none';

                                    }
                                );


                                listaProductos.appendChild(item);

                            });


                            listaProductos.style.display = 'block';

                        })

                        .catch(error => {

                            console.error(
                                'Error al buscar productos:',
                                error
                            );


                            listaProductos.innerHTML = `
                    <div class="list-group-item text-danger">
                        Error al buscar productos.
                    </div>
                `;


                            listaProductos.style.display = 'block';

                        });

                });

            }


            // ==================================================
            // AGREGAR PRODUCTO
            // ==================================================

            if (btnAgregar) {

                btnAgregar.addEventListener(
                    'click',
                    function() {

                        const idProducto =
                            productoId.value;


                        if (!idProducto) {

                            alert(
                                'Primero seleccione un producto.'
                            );

                            return;

                        }


                        const datos =
                            new FormData();


                        datos.append(
                            'proceso_id',
                            <?= $procesoId ?>
                        );


                        datos.append(
                            'producto_id',
                            idProducto
                        );


                        btnAgregar.disabled = true;


                        fetch(
                                'agregar_producto.php', {
                                    method: 'POST',
                                    body: datos
                                }
                            )

                            .then(response => response.text())

                            .then(respuesta => {

                                console.log(
                                    'Respuesta agregar producto:',
                                    respuesta
                                );


                                /*
                                 * IMPORTANTE:
                                 * NO recargamos la página.
                                 *
                                 * Después de agregar podemos limpiar
                                 * el buscador.
                                 */

                                buscarProducto.value = '';

                                productoId.value = '';

                                listaProductos.innerHTML = '';

                                listaProductos.style.display = 'none';

                                btnAgregar.disabled = false;


                                /*
                                 * Avisamos que se agregó.
                                 */

                                alert(
                                    'Producto agregado correctamente.'
                                );


                                /*
                                 * Para que aparezca en la tabla
                                 * hacemos una recarga SOLO aquí.
                                 *
                                 * Si quieres después también podemos
                                 * eliminar esta recarga y actualizar la
                                 * tabla mediante JavaScript.
                                 */

                                window.location.reload();

                            })

                            .catch(error => {

                                console.error(error);


                                alert(
                                    'No fue posible agregar el producto.'
                                );


                                btnAgregar.disabled = false;

                            });

                    }
                );

            }


            // ==================================================
            // CERRAR LISTA DE PRODUCTOS
            // ==================================================

            document.addEventListener(
                'click',
                function(event) {

                    if (
                        buscarProducto &&
                        listaProductos &&
                        !buscarProducto.contains(event.target) &&
                        !listaProductos.contains(event.target)
                    ) {

                        listaProductos.style.display =
                            'none';

                    }

                }
            );


            // ==================================================
            // OBTENER PRODUCTOS
            // ==================================================

            function obtenerProductos() {

                const checkboxes =
                    document.querySelectorAll(
                        '.producto-checkbox'
                    );


                const productos = [];


                checkboxes.forEach(
                    function(checkbox) {

                        productos.push({

                            id: checkbox.dataset.id,

                            comprado: checkbox.checked ? 1 : 0

                        });

                    }
                );


                return productos;

            }


            // ==================================================
            // GUARDAR COMPRAS
            // ==================================================

            function guardarCompras(
                enviarWhatsapp = false
            ) {

                console.log(
                    'guardarCompras ejecutado:',
                    enviarWhatsapp
                );


                const productos =
                    obtenerProductos();


                console.log(
                    'Productos:',
                    productos
                );


                if (productos.length === 0) {

                    alert(
                        'No hay productos para guardar.'
                    );

                    return;

                }


                const datos =
                    new FormData();


                datos.append(
                    'proceso_id',
                    <?= $procesoId ?>
                );


                datos.append(
                    'productos',
                    JSON.stringify(productos)
                );


                datos.append(
                    'whatsapp',
                    enviarWhatsapp ? '1' : '0'
                );


                btnGuardarCompras.disabled = true;

                btnWhatsapp.disabled = true;


                console.log(
                    'Enviando datos a guardar_compras.php'
                );


                fetch(
                        'guardar_compras.php', {
                            method: 'POST',
                            body: datos
                        }
                    )

                    .then(
                        function(response) {

                            console.log(
                                'HTTP:',
                                response.status
                            );


                            return response.text();

                        }
                    )

                    .then(
                        function(texto) {

                            console.log(
                                'Respuesta PHP:',
                                texto
                            );


                            let resultado;


                            try {

                                resultado =
                                    JSON.parse(texto);

                            } catch (error) {

                                console.error(
                                    'JSON inválido:',
                                    error
                                );


                                alert(
                                    'El servidor no devolvió una respuesta válida.'
                                );


                                btnGuardarCompras.disabled =
                                    false;

                                btnWhatsapp.disabled =
                                    false;

                                return;

                            }


                            console.log(
                                'Resultado:',
                                resultado
                            );


                            if (!resultado.success) {

                                alert(
                                    resultado.mensaje ||
                                    'No fue posible guardar los cambios.'
                                );


                                btnGuardarCompras.disabled =
                                    false;

                                btnWhatsapp.disabled =
                                    false;

                                return;

                            }


                            // ==========================================
                            // GUARDADO CORRECTO
                            // ==========================================

                            if (enviarWhatsapp) {

                                alert(
                                    'Los cambios fueron guardados y el mensaje fue enviado por WhatsApp.'
                                );

                            } else {

                                alert(
                                    'Los cambios fueron guardados correctamente.'
                                );

                            }

                            window.location.href =
                                'ver.php?id=<?= $procesoId ?>';

                            btnGuardarCompras.disabled =
                                false;

                            btnWhatsapp.disabled =
                                false;


                            /*
                             * IMPORTANTE:
                             * Ya NO hacemos:
                             *
                             * window.location.href
                             *
                             * por guardar.
                             *
                             * La página permanece donde está.
                             */

                        }
                    )

                    .catch(
                        function(error) {

                            console.error(
                                'ERROR FETCH:',
                                error
                            );


                            alert(
                                'Ocurrió un error al comunicarse con el servidor.'
                            );


                            btnGuardarCompras.disabled =
                                false;

                            btnWhatsapp.disabled =
                                false;

                        }
                    );

            }


            // ==================================================
            // BOTÓN GUARDAR
            // ==================================================

            if (btnGuardarCompras) {

                btnGuardarCompras.addEventListener(
                    'click',
                    function() {

                        console.log(
                            'BOTÓN GUARDAR PRESIONADO'
                        );


                        guardarCompras(false);

                    }
                );

            }


            // ==================================================
            // BOTÓN GUARDAR Y WHATSAPP
            // ==================================================

            if (btnWhatsapp) {

                btnWhatsapp.addEventListener(
                    'click',
                    function() {

                        console.log(
                            'BOTÓN WHATSAPP PRESIONADO'
                        );


                        guardarCompras(true);

                    }
                );

            }

        });
        </script>

        <?php
require_once __DIR__ . '/../includes/footer.php';
require_once __DIR__ . '/../includes/scripts.php';


?>