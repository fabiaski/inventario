console.log("COTIZACIONES.JS CARGADO");

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

    recalcularTotales();

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

    txtBuscar.value = "";

    productoId.value = "";

    valorUnidad.value = "";

    cantidad.value = 1;

    incremento.value = 0;

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
        // Actualizar retención
 calcularRetencion();

calcularPagos();

calcularLlega();

calcularValorTotalUnidad();

calcularGanancia();

calcularGananciaIdeal();

calcularDiferencia();


    //========================================
// CAMBIO DE PAGOS
//========================================

chkPago1.addEventListener("change", function(){

    calcularPagos();

});

chkPago2.addEventListener("change", function(){

    calcularPagos();

});


}



const retencion = document.getElementById("retencion");

const valorRetencion = document.getElementById("valorRetencion");

//========================================
// CALCULAR RETENCIÓN
//========================================

function calcularRetencion(){

    let totalGeneral = 0;

    tbody.querySelectorAll(".totalVenta").forEach(function(td){

        totalGeneral += Number(td.dataset.total) || 0;

    });

    const porcentaje = Number(retencion.value) || 0;

    const valor = Math.round(
        totalGeneral * (porcentaje / 100)
    );

    valorRetencion.innerHTML = formato(valor);

}

//========================================
// CAMBIO DE RETENCIÓN
//========================================

retencion.addEventListener("input", function(){

    calcularRetencion();

});


function calcularPagos(){

    let totalGeneral = 0;

    tbody.querySelectorAll(".totalVenta").forEach(function(td){

        totalGeneral += Number(td.dataset.total) || 0;

    });

    let porcentajePagos = 0;

    if(chkPago1.checked){

        porcentajePagos += 10;

    }

    if(chkPago2.checked){

        porcentajePagos += 10;

    }

    const totalPagos = Math.round(
        totalGeneral * (porcentajePagos / 100)
    );

    valorPagos.innerHTML = formato(totalPagos);

}


//========================================
// CALCULAR VALOR TOTAL UNIDAD
//========================================

function calcularValorTotalUnidad(){

    let total = 0;

    tbody.querySelectorAll("tr").forEach(function(fila){

        if(fila.id == "sinProductos"){
            return;
        }

        const valor = Number(
            fila.cells[5].innerText
                .replace(/\D/g, "")
        ) || 0;

        total += valor;

    });

    const resultado = document.getElementById("valorTotalUnidad");

    if(resultado){

        resultado.innerHTML = formato(total);

    }

}


//========================================
// CALCULAR LLEGA
//========================================

function calcularLlega(){

    let totalVenta = 0;

    tbody.querySelectorAll(".totalVenta").forEach(function(td){

        totalVenta += Number(td.dataset.total) || 0;

    });


    // Obtener retención
    const valorRet = document.getElementById("valorRetencion");

    const retencion = valorRet
        ? Number(
            valorRet.innerText
                .replace(/\D/g, "")
          ) || 0
        : 0;


    // Obtener pagos
    const valorPagos = document.getElementById("valorPagos");

    const pagos = valorPagos
        ? Number(
            valorPagos.innerText
                .replace(/\D/g, "")
          ) || 0
        : 0;


    // Calcular llega
    const llega = totalVenta - retencion - pagos;


    const resultado = document.getElementById("llega");

    if(resultado){

        resultado.innerHTML = formato(llega);

    }

}

//========================================
// CALCULAR GANANCIA
//========================================

function calcularGanancia(){

    const valorTotalUnidad =
        Number(
            document.getElementById("valorTotalUnidad")
                ?.innerText
                .replace(/\D/g, "")
        ) || 0;


    const llega =
        Number(
            document.getElementById("llega")
                ?.innerText
                .replace(/\D/g, "")
        ) || 0;


    const ganancia =llega - valorTotalUnidad;


    const resultado = document.getElementById("ganancia");

    if(resultado){

        resultado.innerHTML = formato(ganancia);

    }

}

//========================================
// CALCULAR GANANCIA IDEAL
//========================================

function calcularGananciaIdeal(){

    let totalVenta = 0;

    tbody.querySelectorAll(".totalVenta").forEach(function(td){

        totalVenta += Number(td.dataset.total) || 0;

    });

    const gananciaIdeal = Math.round(totalVenta * 0.20);

    const resultado = document.getElementById("gananciaIdeal");

    if(resultado){

        resultado.innerHTML = formato(gananciaIdeal);

    }

}



//========================================
// CALCULAR GANANCIA IDEAL
//========================================

function calcularGananciaIdeal(){

    let totalVenta = 0;

    tbody.querySelectorAll(".totalVenta").forEach(function(td){

        totalVenta += Number(td.dataset.total) || 0;

    });

    const gananciaIdeal = Math.round(totalVenta * 0.20);

    const resultado = document.getElementById("gananciaIdeal");

    if(resultado){

        resultado.innerHTML = formato(gananciaIdeal);

    }

}

//========================================
// CALCULAR DIFERENCIA
//========================================

function calcularDiferencia(){

    const gananciaIdeal =
        Number(
            document.getElementById("gananciaIdeal")
                ?.innerText
                .replace(/\D/g, "")
        ) || 0;

    const ganancia =
        Number(
            document.getElementById("ganancia")
                ?.innerText
                .replace(/\D/g, "")
        ) || 0;

    const diferencia = gananciaIdeal - ganancia;

    const resultado = document.getElementById("diferencia");

    if(resultado){

        resultado.innerHTML = formato(diferencia);

    }

}


//========================================
// OBTENER PRODUCTOS DE LA COTIZACIÓN
//========================================

function obtenerProductosCotizacion(){

    const productosCotizacion = [];

    tbody.querySelectorAll("tr").forEach(function(fila){

        // Ignorar fila vacía
        if(fila.id === "sinProductos"){
            return;
        }

        const nombreProducto = fila.cells[1].innerText.trim();

        const producto = productos.find(function(p){

            return p.producto === nombreProducto;

        });

        if(!producto){
            return;
        }

        const cantidad = Number(fila.cells[2].innerText);

        const valorUnidad = Number(producto.precio);

        const porcentajeIncremento =
            Number(
                fila.cells[6].innerText
                .replace("%","")
                .trim()
            );

        const valorIncremento =
            Number(
                fila.cells[7].innerText
                .replace("$","")
                .replace(/\./g,"")
                .replace(/\s/g,"")
                .trim()
            );

        const valorUnidadIncremento =
            Number(
                fila.cells[8].innerText
                .replace("$","")
                .replace(/\./g,"")
                .replace(/\s/g,"")
                .trim()
            );

        const valorTotalUnidad =
            Number(
                fila.cells[5].innerText
                .replace("$","")
                .replace(/\./g,"")
                .replace(/\s/g,"")
                .trim()
            );

        const totalVenta =
            Number(fila.cells[9].dataset.total) || 0;


        productosCotizacion.push({

            producto_id: producto.id,

            cantidad: cantidad,

            valor_unidad: valorUnidad,

            porcentaje_incremento: porcentajeIncremento,

            valor_incremento: valorIncremento,

            valor_unidad_incremento: valorUnidadIncremento,

            valor_total_unidad: valorTotalUnidad,

            total_venta: totalVenta

        });

    });

    return productosCotizacion;

}

//========================================
// BOTON GUARDAR COTIZACIÓN
//========================================

const btnGuardarCotizacion =
    document.getElementById("btnGuardarCotizacion");

console.log("BOTÓN:", btnGuardarCotizacion);

if(btnGuardarCotizacion){

    btnGuardarCotizacion.addEventListener("click", function(){

        console.log("SE HIZO CLIC EN GUARDAR");


        //========================================
        // OBTENER PRODUCTOS
        //========================================

        const productosCotizacion = [];

        tbody.querySelectorAll("tr").forEach(function(fila){

            // Ignorar fila vacía

            if(fila.id === "sinProductos"){

                return;

            }


            const nombreProducto =
                fila.cells[1].innerText.trim();


            const producto = productos.find(function(p){

                return p.producto === nombreProducto;

            });


            if(!producto){

                console.log(
                    "Producto no encontrado:",
                    nombreProducto
                );

                return;

            }


            const cantidadProducto =
                Number(fila.cells[2].innerText);


            const valorUnidadProducto =
                Number(producto.precio);


            const porcentajeIncremento =
                Number(
                    fila.cells[6]
                        .innerText
                        .replace("%","")
                        .trim()
                );


            const valorIncremento =
                Number(
                    fila.cells[7]
                        .innerText
                        .replace("$","")
                        .replace(/\./g,"")
                        .replace(/\s/g,"")
                        .trim()
                );


            const valorUnidadIncremento =
                Number(
                    fila.cells[8]
                        .innerText
                        .replace("$","")
                        .replace(/\./g,"")
                        .replace(/\s/g,"")
                        .trim()
                );


            const valorTotalUnidad =
                Number(
                    fila.cells[5]
                        .innerText
                        .replace("$","")
                        .replace(/\./g,"")
                        .replace(/\s/g,"")
                        .trim()
                );


            const totalVentaProducto =
                Number(fila.cells[9].dataset.total) || 0;


            productosCotizacion.push({

                producto_id: producto.id,

                cantidad: cantidadProducto,

                valor_unidad: valorUnidadProducto,

                porcentaje_incremento:
                    porcentajeIncremento,

                valor_incremento:
                    valorIncremento,

                valor_unidad_incremento:
                    valorUnidadIncremento,

                valor_total_unidad:
                    valorTotalUnidad,

                total_venta:
                    totalVentaProducto

            });

        });

//========================================
// DATOS DE LA COTIZACIÓN
//========================================

const cliente =
    document.getElementById("cliente").value.trim();

const fecha =
    document.getElementById("fecha").value;

const observaciones =
    document.getElementById("observaciones").value.trim();


//========================================
// RETENCIÓN
//========================================

const porcentajeRetencion =
    Number(
        document.getElementById("retencion").value
    ) || 0;

const valorRetencion =
    Number(
        document
            .getElementById("valorRetencion")
            .innerText
            .replace("$","")
            .replace(/\./g,"")
            .replace(/\s/g,"")
    ) || 0;


//========================================
// PAGOS
//========================================

const chkPago1 =
    document.getElementById("pago1");

const chkPago2 =
    document.getElementById("pago2");

const aplicaPago1 =
    chkPago1 && chkPago1.checked ? 1 : 0;

const aplicaPago2 =
    chkPago2 && chkPago2.checked ? 1 : 0;


const valorPagos =
    Number(
        document
            .getElementById("valorPagos")
            .innerText
            .replace("$","")
            .replace(/\./g,"")
            .replace(/\s/g,"")
    ) || 0;


//========================================
// TOTAL VENTA
//========================================

const totalVenta =
    Number(
        document
            .getElementById("totalVenta")
            .innerText
            .replace("$","")
            .replace(/\./g,"")
            .replace(/\s/g,"")
    ) || 0;


//========================================
// LLEGA
//========================================

const llega =
    Number(
        document
            .getElementById("llega")
            .innerText
            .replace("$","")
            .replace(/\./g,"")
            .replace(/\s/g,"")
    ) || 0;


//========================================
// GANANCIA
//========================================

const ganancia =
    Number(
        document
            .getElementById("ganancia")
            .innerText
            .replace("$","")
            .replace(/\./g,"")
            .replace(/\s/g,"")
    ) || 0;


//========================================
// GANANCIA IDEAL
//========================================

const porcentajeGananciaIdeal = 20;

const gananciaIdeal =
    Number(
        document
            .getElementById("gananciaIdeal")
            .innerText
            .replace("$","")
            .replace(/\./g,"")
            .replace(/\s/g,"")
    ) || 0;


//========================================
// DIFERENCIA
//========================================

const diferencia =
    Number(
        document
            .getElementById("diferencia")
            .innerText
            .replace("$","")
            .replace(/\./g,"")
            .replace(/\s/g,"")
    ) || 0;


//========================================
// VALIDAR PRODUCTOS
//========================================

if(productosCotizacion.length === 0){

    alert("Agregue al menos un producto.");

    return;

}


//========================================
// ENVIAR A GUARDAR.PHP
//========================================

const datos = new FormData();

datos.append("cliente", cliente);

datos.append("fecha", fecha);

datos.append("observaciones", observaciones);

datos.append(
    "porcentaje_retencion",
    porcentajeRetencion
);

datos.append(
    "aplica_pago1",
    aplicaPago1
);

datos.append(
    "aplica_pago2",
    aplicaPago2
);

datos.append(
    "porcentaje_ganancia_ideal",
    porcentajeGananciaIdeal
);

datos.append(
    "total_venta",
    totalVenta
);

datos.append(
    "valor_retencion",
    valorRetencion
);

datos.append(
    "valor_pagos",
    valorPagos
);

datos.append(
    "llega",
    llega
);

datos.append(
    "ganancia",
    ganancia
);

datos.append(
    "ganancia_ideal",
    gananciaIdeal
);

datos.append(
    "diferencia",
    diferencia
);

datos.append(
    "productos",
    JSON.stringify(productosCotizacion)
);


//========================================
// POST
//========================================

fetch("guardar.php", {

    method: "POST",

    body: datos

})
.then(function(response){

    if(!response.ok){

        throw new Error(
            "Error HTTP: " + response.status
        );

    }

    return response.text();

})
.then(function(resultado){

    console.log(
        "RESPUESTA guardar.php:",
        resultado
    );
        window.location.href = "ver.php?id=" + resultado;


    // El PHP debería redireccionar,
    // por lo que normalmente aquí no necesitamos hacer nada.

})
.catch(function(error){

    console.error(
        "ERROR AL GUARDAR:",
        error
    );

    alert(
        "Ocurrió un error al guardar la cotización."
    );

});

    });

}