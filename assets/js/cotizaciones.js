//========================================
// INPUTS
//========================================

const txtBuscar = document.getElementById("buscarProducto");

const lista = document.getElementById("listaProductos");

const productoId = document.getElementById("producto_id");

const valorUnidad = document.getElementById("valorUnidad");


//========================================
// BUSCAR PRODUCTO
//========================================

txtBuscar.addEventListener("keyup", function(){

    const texto = this.value.trim().toLowerCase();

    lista.innerHTML = "";

    if(texto.length==0){

        lista.style.display="none";

        return;

    }

    const encontrados = productos.filter(function(p){

        return p.producto.toLowerCase().includes(texto);

    });

    if(encontrados.length==0){

        lista.style.display="none";

        return;

    }

    encontrados.forEach(function(p){

        const item = document.createElement("a");

        item.href="#";

        item.className="list-group-item list-group-item-action";

        item.innerHTML=`
            <strong>${p.producto}</strong><br>
            <small>
                ${p.unidad_medida}
                |
                $${Number(p.precio).toLocaleString("es-CO")}
            </small>
        `;

        item.onclick=function(e){

            e.preventDefault();

            seleccionarProducto(p);

        };

        lista.appendChild(item);

    });

    lista.style.display="block";

});


//========================================
// SELECCIONAR
//========================================

function seleccionarProducto(producto){

    productoId.value=producto.id;

    txtBuscar.value=producto.producto;

    valorUnidad.value=
        Number(producto.precio)
        .toLocaleString("es-CO");

    lista.style.display="none";

}

//========================================
// CERRAR LISTA AL HACER CLIC FUERA
//========================================

document.addEventListener("click", function(e){

    if(!txtBuscar.contains(e.target) && !lista.contains(e.target)){

        lista.style.display = "none";

    }

});


//========================================
// VARIABLES
//========================================

const cantidad = document.getElementById("cantidad");

const incremento = document.getElementById("incremento");

//const unidad = document.getElementById("unidad");

const btnAgregar = document.getElementById("btnAgregar");

const tbody = document.querySelector("#tablaCotizacion tbody");

let numeroFila = 1;

let filaEditando = null;

//========================================
// BOTON AGREGAR
//========================================

btnAgregar.addEventListener("click", agregarProducto);


//========================================
// AGREGAR PRODUCTO
//========================================

function agregarProducto(){

    if(productoId.value==""){

        alert("Seleccione un producto.");

        return;

    }

    const producto = productos.find(function(p){

        return p.id == productoId.value;

    });

    if(!producto){

        alert("Producto no encontrado.");

        return;

    }

    const cant = Number(cantidad.value);

    const precio = Number(producto.precio);

    const porcentaje = Number(incremento.value);

    const totalUnidad = Math.round(cant * precio);

    const valorIncremento = Math.round(precio * (porcentaje / 100));

    const unidadMasIncremento = Math.round(precio + valorIncremento);

    const totalFila = Math.round(unidadMasIncremento * cant);


//========================================
// ACTUALIZAR PRODUCTO
//========================================

if(filaEditando){

    // Cantidad
    filaEditando.cells[2].innerHTML = cant;

    // Total UND
    filaEditando.cells[5].innerHTML =
        formato(totalUnidad);

    // Porcentaje
    filaEditando.cells[6].innerHTML =
        porcentaje + "%";

    // Valor incremento
    filaEditando.cells[7].innerHTML =
        formato(valorIncremento);

    // UND + incremento
    filaEditando.cells[8].innerHTML =
        formato(unidadMasIncremento);

    // TOTAL VENTA
    const celdaTotal = filaEditando.cells[9];

    celdaTotal.dataset.total = totalFila;

    celdaTotal.innerHTML =
        formato(totalFila);

    // Terminar edición
    filaEditando = null;

    btnAgregar.innerHTML = `
        <i class="bi bi-plus-lg"></i>
        Agregar
    `;

    // Limpiar formulario
    limpiarFormulario();

    // ACTUALIZAR RESUMEN INMEDIATAMENTE
    recalcularTotales();

    return;
}


    const filaVacia = document.getElementById("sinProductos");

    if(filaVacia){

        filaVacia.remove();

    }

    const fila = document.createElement("tr");

    fila.innerHTML = `

        <td>${numeroFila++}</td>

        <td>${producto.producto}</td>

        <td>${cant}</td>

        <td>${producto.unidad_medida}</td>

        <td class="text-end">${formato(precio)}</td>

        <td class="text-end">${formato(totalUnidad)}</td>

        <td class="text-center">${porcentaje}%</td>

        <td class="text-end">${formato(valorIncremento)}</td>

        <td class="text-end">${formato(unidadMasIncremento)}</td>

    <td class="text-end totalVenta" data-total="${totalFila}"> ${formato(totalFila)} </td>

    <td class="text-center">

            <button
                type="button"
                class="btn btn-warning btn-sm editar">

                <i class="bi bi-pencil"></i>

            </button>

            <button
                type="button"
                class="btn btn-danger btn-sm eliminar">

                <i class="bi bi-trash"></i>

            </button>

        </td>

    `;

    tbody.appendChild(fila);

    recalcularTotales();

    limpiarFormulario();

}


tbody.addEventListener("click", function(e){

    const fila = e.target.closest("tr");

    //==========================
    // EDITAR
    //==========================

    if(e.target.closest(".editar")){

        editarFila(fila);

        return;

    }

    //==========================
    // ELIMINAR
    //==========================

    if(e.target.closest(".eliminar")){

        fila.remove();

        renumerar();

        recalcularTotales();

        verificarTabla();

    }

});

//========================================
// RENUMERAR
//========================================

function renumerar(){

    numeroFila = 1;

    tbody.querySelectorAll("tr").forEach(function(fila){

        if(fila.id=="sinProductos"){

            return;

        }

        fila.cells[0].innerHTML = numeroFila++;

    });

}


//========================================
// TABLA VACIA
//========================================

function verificarTabla(){

    if(tbody.querySelectorAll("tr").length==0){

        tbody.innerHTML=`

            <tr id="sinProductos">

                <td colspan="11"
                    class="text-center text-muted">

                    No hay productos agregados.

                </td>

            </tr>

        `;

    }

}


//========================================
// FORMATEAR PESOS
//========================================

function formato(valor){

    return "$ " + Math.round(Number(valor)).toLocaleString("es-CO");

}

//========================================
// LIMPIAR FORMULARIO
//========================================

function limpiarFormulario(){

    txtBuscar.value="";

    productoId.value="";

    valorUnidad.value="";

if(unidad){

    unidad.value = "";

}
    cantidad.value=1;

    incremento.value=0;

}
//========================================
// EDITAR FORMULARIO
//========================================

function editarFila(fila){

    filaEditando = fila;

    const nombreProducto = fila.cells[1].innerText;

    const producto = productos.find(function(p){

        return p.producto == nombreProducto;

    });

    if(producto){

        productoId.value = producto.id;

        txtBuscar.value = producto.producto;

        valorUnidad.value = formato(producto.precio);

    }

    cantidad.value = fila.cells[2].innerText;

    incremento.value =
        fila.cells[6].innerText.replace("%","");

    btnAgregar.innerHTML = `
        <i class="bi bi-check-lg"></i>
        Actualizar
    `;

}

//========================================
// RECALCULAR TOTAL VENTA
//========================================

function recalcularTotales(){

    let totalGeneral = 0;

    tbody.querySelectorAll(".totalVenta").forEach(function(td){

        const total = Number(td.dataset.total) || 0;

        totalGeneral += total;

    });

    const totalVenta = document.getElementById("totalVenta");

    if(totalVenta){

        totalVenta.innerHTML = formato(totalGeneral);

    }

}