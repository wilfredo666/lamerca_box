const buscador = document.getElementById("buscar");

buscador.addEventListener("keyup", function(){

    let texto = this.value.toLowerCase();

    let paquetes = document.querySelectorAll(".paquete");

    paquetes.forEach(function(paquete){

        let contenido = paquete.innerText.toLowerCase();

        if(contenido.includes(texto)){

            paquete.style.display="block";

        }else{

            paquete.style.display="none";

        }

    });

});

const checks = document.querySelectorAll(".selectorPaquete");

const panel = document.getElementById("panelSeleccion");

const layout = document.getElementById("layoutPrincipal");

const cantidad = document.getElementById("cantidadSeleccionados");

const subtotal = document.getElementById("subtotalCobro");
const total = document.getElementById("totalCobro");
const descuento = document.getElementById("descuentoGeneral");

const listaSeleccionados = document.getElementById("listaSeleccionados");

let miniTarjetas = {};

checks.forEach(function(check){

    check.addEventListener("change",actualizarSeleccion);

});

function crearMiniTarjeta(
    id,
    nombre,
    observacion,
    tipo,
    precio,
    recargo,
    total
){

    let existente=document.getElementById("mini_"+id);

    if(existente){

        existente.querySelector(".checkPanel").checked=true;

        existente.style.opacity="1";

        return;

    }

    let html = `
    <div
    id="mini_${id}"
    class="itemPanel"
    style="
    border:1px solid #ddd;
    border-radius:10px;
    padding:10px;
    margin-bottom:10px;
    ">

        <label
        style="
        display:flex;
        align-items:flex-start;
        gap:12px;
        ">

            <input
            type="checkbox"
            class="checkPanel"
            data-id="${id}"
            style="
            width:18px;
            height:18px;
            margin-top:3px;
            flex-shrink:0;
            cursor:pointer;
            "
            checked>

            <div>

                <div style="flex:1;">

                    <div style="
                        font-size:15px;
                        font-weight:bold;
                        color:#111827;
                        margin-bottom:4px;
                    ">
                        ${nombre}
                    </div>

                    <div style="
                        font-size:13px;
                        color:#6b7280;
                        margin-bottom:3px;
                    ">
                        📦 ${observacion}
                    </div>

                    <div style="
                        font-size:13px;
                        color:#374151;
                        margin-bottom:3px;
                    ">
                        🏷 ${tipo}
                    </div>

                    <div style="
                        color:#16a34a;
                        font-weight:bold;
                        font-size:14px;
                    ">
                        💵 ${total.toFixed(2)} Bs

                        ${recargo > 0
                            ? `<div style="font-size:12px;color:#dc2626;">
                                Incluye ${recargo.toFixed(2)} Bs de recargo
                            </div>`
                            : ""
                        }
                    </div>

                </div>

        </label>

    </div>
    `;

    let contenedor=document.createElement("div");

    contenedor.innerHTML=html;

    listaSeleccionados.prepend(contenedor.firstElementChild);

}

function actualizarTotal(){

    let subtotalValor = 0;

    document.querySelectorAll(".selectorPaquete:checked")
    .forEach(function(check){

        subtotalValor +=
            Number(check.dataset.total) || 0;

    });


    let descuentoValor =
        Number(descuento.value) || 0;


    if(descuentoValor < 0){

        descuentoValor = 0;

    }


    if(descuentoValor > subtotalValor){

        descuentoValor = subtotalValor;

    }


    let totalValor =
        subtotalValor - descuentoValor;


    subtotal.innerHTML =
        subtotalValor.toFixed(2);

    total.innerHTML =
        totalValor.toFixed(2);


    /*
    |----------------------------------------------------------
    | ACTUALIZAR MONTO DE LOS BOTONES DE COBRO
    |----------------------------------------------------------
    */

    document.getElementById(
        "montoEfectivoMultiple"
    ).innerText =
        totalValor.toFixed(2);


    document.getElementById(
        "montoQRMultiple"
    ).innerText =
        totalValor.toFixed(2);

}

function actualizarSeleccion(){

    let seleccionados = 0;

    let totalCobrar = 0;

    // listaSeleccionados.innerHTML = "";

    checks.forEach(function(c){

        let tarjeta = c.closest(".paquete");

        if(c.checked){

            tarjeta.classList.add("paqueteSeleccionado");

            seleccionados++;

            totalCobrar += Number(c.dataset.total) || 0;

            let nombre = tarjeta.querySelector(".nombreCliente").innerText;

            nombre = nombre.replace("👤","").trim();

            if(nombre==""){

                nombre="Sin nombre";

            }

            let observacion = tarjeta.querySelector(".detalle")
            .innerText
            .replace("📦","")
            .trim();

            if(observacion==""){

                observacion="Sin detalle";

            }

            let precio = Number(c.dataset.precio);

            if(isNaN(precio)){
                precio = 0;
            }

            let recargo = Number(c.dataset.recargo) || 0;

            let total = Number(c.dataset.total) || 0;

        let tipo = tarjeta
        .querySelector(".datosExtra b")
        .parentNode
        .innerText
        .replace("🏷️","")
        .replace("Tipo:","")
        .trim();

        console.log("Creando mini tarjeta",c.value);

        crearMiniTarjeta(
            c.value,
            nombre,
            observacion,
            tipo,
            precio,
            recargo,
            total
        );
    }else{

        tarjeta.classList.remove("paqueteSeleccionado");

        let mini=document.getElementById("mini_"+c.value);

        if(mini){

            mini.querySelector(".checkPanel").checked=false;

            mini.style.opacity="0.45";

        }

    }

    });

    cantidad.innerHTML = seleccionados;

    actualizarTotal();

    document.querySelectorAll(".checkPanel").forEach(function(check){

        check.addEventListener("change",function(){

            let id = this.dataset.id;

            let original = document.querySelector(
                '.selectorPaquete[value="'+id+'"]'
            );

            original.checked = this.checked;

            actualizarSeleccion();

        });

    });

    if(seleccionados>0){

        panel.style.display="flex";

        layout.classList.add("panelActivo");

    }else{

        panel.style.display="none";

        layout.classList.remove("panelActivo");
    }

}

descuento.addEventListener("input", function(){

    actualizarTotal();

});

function obtenerIdsSeleccionados(){

    let ids = [];

    document.querySelectorAll(
        ".selectorPaquete:checked"
    ).forEach(function(check){

        ids.push(check.value);

    });

    return ids;

}


function obtenerDescuento(){

    return Number(
        descuento.value
    ) || 0;

}


function obtenerTotalActual(){

    return Number(
        total.innerText
    ) || 0;

}


function procesarEntregaMultiple(medioCobro){

    let ids =
        obtenerIdsSeleccionados();


    if(ids.length === 0){

        alert(
            "Seleccione al menos un paquete."
        );

        return;

    }


    let totalActual =
        obtenerTotalActual();


    if(totalActual <= 0){

        alert(
            "El total a cobrar no es válido."
        );

        return;

    }


    const boton =
        medioCobro === "Efectivo"
        ? document.getElementById(
            "btnEfectivoMultiple"
        )
        : document.getElementById(
            "btnConfirmarQRMultiple"
        );


    boton.disabled = true;


    fetch(
        window.entregaMultipleUrl,
        {

            method:"POST",

            headers:{
                "Content-Type":
                    "application/json"
            },

            body:JSON.stringify({

                ids:ids,

                descuento:
                    obtenerDescuento(),

                medio_cobro:
                    medioCobro,

                csrf_token:
                    window.entregaCsrfToken

            })

        }
    )

    .then(function(r){
        return r.json().then(function(respuesta){
            if (!r.ok) {
                throw new Error(respuesta.error || "No se pudo procesar la entrega.");
            }
            return respuesta;
        });
    })

    .then(function(respuesta){

        if(respuesta.ok){

            if(
                medioCobro === "Efectivo"
            ){

                document.getElementById(
                    "btnEfectivoMultiple"
                ).innerHTML =
                    "✅ PAQUETES ENTREGADOS";

            }


            if(
                medioCobro === "QR"
            ){

                cerrarModalQRMultiple();

            }


            setTimeout(function(){

                location.reload();

            },500);


        }else{

            alert(respuesta.error || "Ocurrió un error al procesar la entrega.");

            boton.disabled = false;

        }

    })

    .catch(function(error){

        console.error(error);
        alert(error.message || "No se pudo conectar con el servidor.");

        boton.disabled = false;

    });

}


document.getElementById(
    "btnEfectivoMultiple"
).addEventListener(
    "click",
    function(){

        procesarEntregaMultiple(
            "Efectivo"
        );

    }
);


document.getElementById(
    "btnQRMultiple"
).addEventListener(
    "click",
    function(){

        let totalActual =
            obtenerTotalActual();


        document.getElementById(
            "montoModalQRMultiple"
        ).innerText =
            totalActual.toFixed(2)
            + " Bs";


        document.getElementById(
            "btnConfirmarQRMultiple"
        ).innerText =
            "✅ COBRO VERIFICADO "
            + totalActual.toFixed(2)
            + " Bs";


        document.getElementById(
            "modalQRMultiple"
        ).style.display = "flex";

    }
);


function cerrarModalQRMultiple(){

    document.getElementById(
        "modalQRMultiple"
    ).style.display = "none";

}


document.getElementById(
    "btnConfirmarQRMultiple"
).addEventListener(
    "click",
    function(){

        procesarEntregaMultiple(
            "QR"
        );

    }
);
