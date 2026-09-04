document.addEventListener("DOMContentLoaded", function () {
    const formulario = document.getElementById("formRecepcion");
    const tabla = document.getElementById("tablaPaquetes");
    const plantilla = document.getElementById("plantillaEncomienda");
    const contadorTexto = document.getElementById("contadorPaquetes");
    const textoContador = document.getElementById("textoContador");
    const botonAgregar = document.getElementById("agregarEncomienda");
    const buscadorCliente = document.getElementById("buscarCliente");
    const campoCliente = document.getElementById("idClienteRecepcion");
    const resultadosClientes = document.getElementById("resultadosClientes");
    const clienteSeleccionado = document.getElementById("clienteSeleccionado");

    if (!formulario || !tabla || !plantilla || !botonAgregar) {
        return;
    }

    function actualizarNumeros() {
        const filas = tabla.querySelectorAll("tr");
        filas.forEach(function (fila, indice) {
            fila.querySelector(".numero-encomienda").textContent = indice + 1;
        });
        if (contadorTexto && textoContador) {
            contadorTexto.textContent = filas.length;
            textoContador.textContent = filas.length === 1 ? "encomienda registrada" : "encomiendas registradas";
        }
    }

    function seleccionarCliente(boton) {
        campoCliente.value = boton.dataset.id;
        buscadorCliente.value = boton.dataset.nombre;
        buscadorCliente.setCustomValidity("");
        clienteSeleccionado.replaceChildren();

        const nombre = document.createElement("strong");
        nombre.textContent = boton.dataset.nombre;
        clienteSeleccionado.appendChild(nombre);
        if (boton.dataset.celular) {
            const celular = document.createElement("span");
            celular.textContent = boton.dataset.celular;
            clienteSeleccionado.appendChild(celular);
        }

        clienteSeleccionado.hidden = false;
        resultadosClientes.hidden = true;
        buscadorCliente.setAttribute("aria-expanded", "false");
    }

    function filtrarClientes() {
        const termino = buscadorCliente.value.trim().toLocaleLowerCase();
        let resultadosVisibles = 0;

        document.querySelectorAll(".resultado-cliente").forEach(function (boton) {
            const contenido = `${boton.dataset.nombre} ${boton.dataset.celular}`.toLocaleLowerCase();
            const coincide = termino !== "" && contenido.includes(termino);
            boton.hidden = !coincide;
            resultadosVisibles += coincide ? 1 : 0;
        });

        resultadosClientes.hidden = resultadosVisibles === 0;
        buscadorCliente.setAttribute("aria-expanded", resultadosVisibles > 0 ? "true" : "false");
    }

    if (buscadorCliente && campoCliente && resultadosClientes && clienteSeleccionado) {
        document.querySelectorAll(".resultado-cliente").forEach(function (boton) {
            boton.addEventListener("click", function () {
                seleccionarCliente(boton);
            });
        });

        buscadorCliente.addEventListener("input", function () {
            campoCliente.value = "";
            clienteSeleccionado.hidden = true;
            filtrarClientes();
        });

        buscadorCliente.addEventListener("focus", filtrarClientes);
    }

    function agregarEncomienda(enfocar) {
        const fragmento = plantilla.content.cloneNode(true);
        const fila = fragmento.querySelector("tr");
        const botonQuitar = fragmento.querySelector(".boton-quitar");

        botonQuitar.addEventListener("click", function () {
            fila.remove();
            actualizarNumeros();
        });
        tabla.appendChild(fragmento);
        actualizarNumeros();

        if (enfocar) {
            fila.querySelector('input[name="destinatario[]"]').focus();
        }
    }

    botonAgregar.addEventListener("click", function () {
        agregarEncomienda(true);
    });

    formulario.addEventListener("keydown", function (evento) {
        if (evento.key === "Enter" && evento.target.matches('input[name="descripcion[]"]')) {
            evento.preventDefault();
            agregarEncomienda(true);
        }
    });

    formulario.addEventListener("submit", function (evento) {
        if (campoCliente && !campoCliente.value) {
            evento.preventDefault();
            buscadorCliente.setCustomValidity("Seleccione un cliente de los resultados.");
            buscadorCliente.reportValidity();
            return;
        }
        if (tabla.querySelectorAll("tr").length === 0) {
            evento.preventDefault();
            botonAgregar.focus();
        }
    });

    if (tabla.querySelectorAll("tr").length === 0) {
        agregarEncomienda(false);
    } else {
        tabla.querySelectorAll(".boton-quitar").forEach(function (botonQuitar) {
            botonQuitar.addEventListener("click", function () {
                botonQuitar.closest("tr").remove();
                actualizarNumeros();
            });
        });
    }
    actualizarNumeros();
});

/* Manejo del modal de cliente */
  $("#modalCliente").on("show.bs.modal", function (event) {
    const button = $(event.relatedTarget);
    const form = $("#formCliente")[0];
    const esEdicion = button.hasClass("btn-editar-cliente");

    form.reset();
    $("#accionCliente").val(esEdicion ? "editar" : "crear");
    $("#idCliente").val(esEdicion ? button.data("id") : "");
    $("#tituloModalCliente").text(esEdicion ? "Editar cliente" : "Nuevo cliente");
    $("#paisCliente").val(esEdicion ? button.data("pais") : "Bolivia");

    if (esEdicion) {
      $("#nombreCliente").val(button.data("nombre"));
      $("#celularCliente").val(button.data("celular"));
      $("#paisCliente").val(button.data("pais"));
      $("#ciudadCliente").val(button.data("ciudad"));
      $("#observacionesCliente").val(button.data("observaciones"));
    }
  });
