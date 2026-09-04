let contador = 1;
const tabla = document.getElementById("tablaPaquetes");
const contadorTexto = document.getElementById("contadorPaquetes");
const buscadorCaja = document.getElementById("buscarCajaTikTok");
const campoCaja = document.getElementById("cajaCliente");
const resultadosCajas = document.getElementById("resultadosCajasTikTok");
const cajaSeleccionada = document.getElementById("cajaSeleccionada");

function seleccionarCaja(boton) {
    campoCaja.value = boton.dataset.id;
    buscadorCaja.value = boton.dataset.nombre;
    cajaSeleccionada.replaceChildren();

    const titulo = document.createElement("strong");
    titulo.className = "nombre-caja-seleccionada";
    titulo.textContent = `📦 ${boton.dataset.nombre}`;
    cajaSeleccionada.appendChild(titulo);

    [
        ["👩", "Propietaria", boton.dataset.propietaria || "Sin propietaria"],
        ["📱", "WhatsApp", boton.dataset.whatsapp || "Sin WhatsApp"],
        ["📝", "Nota", boton.dataset.observaciones || "Sin nota"],
        ["📦", "Total histórico", boton.dataset.totalHistorico || "0"],
        ["🟡", "Pendientes", boton.dataset.pendientes || "0"]
    ].forEach(function (dato) {
        const detalle = document.createElement("p");
        const etiqueta = document.createElement("strong");
        etiqueta.textContent = `${dato[0]} ${dato[1]}: `;
        detalle.append(etiqueta, document.createTextNode(dato[2]));
        cajaSeleccionada.appendChild(detalle);
    });

    const enlaceEditar = document.createElement("a");
    enlaceEditar.className = "btnEditarCajaTikTok";
    enlaceEditar.href = boton.dataset.urlEditar;
    enlaceEditar.textContent = "🖊 Editar Caja";
    cajaSeleccionada.appendChild(enlaceEditar);

    cajaSeleccionada.hidden = false;
    resultadosCajas.hidden = true;
    buscadorCaja.setAttribute("aria-expanded", "false");
}

function filtrarCajas() {
    const termino = buscadorCaja.value.trim().toLocaleLowerCase();
    let resultadosVisibles = 0;

    document.querySelectorAll(".resultado-caja").forEach(function (boton) {
        const contenido = [
            boton.dataset.nombre,
            boton.dataset.propietaria,
            boton.dataset.whatsapp,
            boton.dataset.codigo
        ].join(" ").toLocaleLowerCase();
        const coincide = termino !== "" && contenido.includes(termino);

        boton.hidden = !coincide;
        resultadosVisibles += coincide ? 1 : 0;
    });

    resultadosCajas.hidden = resultadosVisibles === 0;
    buscadorCaja.setAttribute("aria-expanded", resultadosVisibles > 0 ? "true" : "false");
}

document.querySelectorAll(".resultado-caja").forEach(function (boton) {
    boton.addEventListener("click", function () {
        seleccionarCaja(boton);
    });
});

buscadorCaja.addEventListener("input", function () {
    campoCaja.value = "";
    cajaSeleccionada.hidden = true;
    filtrarCajas();
});

buscadorCaja.addEventListener("focus", filtrarCajas);

const nuevaCaja = document.querySelector('.resultado-caja[data-nueva-caja="true"]');
if (nuevaCaja) {
    seleccionarCaja(nuevaCaja);
}

document.getElementById("formRecepcion").addEventListener("keydown", function(event){
    if(event.key === "Enter"){
        event.preventDefault();
    }
});

function agregarFila(){
    contador++;
    contadorTexto.textContent = contador;
    const fila = document.createElement("tr");
    fila.innerHTML = `<td>${contador}</td>
      <td><input type="text" name="cliente[]" class="cliente" required onkeydown="enterCliente(event,this)"></td>
      <td><input type="text" name="celular[]" class="celular" onkeydown="enterCelular(event,this)"></td>
      <td><input type="text" name="detalle[]" class="detalle" onkeydown="detectarEnter(event,this)"></td>
      <td><input type="number" name="precio_base[]" value="2" min="0" step="0.50"></td>
      <td><select name="pagado_por[]"><option value="Cliente">Cliente</option><option value="Vendedor">Vendedor</option></select></td>`;
    tabla.appendChild(fila);
    fila.querySelector(".cliente").focus();
}

function detectarEnter(event){
    if(event.key === "Enter"){
        event.preventDefault();
        agregarFila();
    }
}

function enterCliente(event, campo){
    if(event.key === "Enter"){
        event.preventDefault();
        campo.closest("tr").querySelector(".celular").focus();
    }
}

function enterCelular(event, campo){
    if(event.key === "Enter"){
        event.preventDefault();
        campo.closest("tr").querySelector(".detalle").focus();
    }
}
