document.addEventListener("DOMContentLoaded", function () {

    const filtroSinImagen = document.querySelector("[data-filtro-sin-imagen]");
    const formularioBusqueda = document.querySelector(".formulario-busqueda-manual");
    const campoSinImagen = document.querySelector("[data-campo-sin-imagen]");

    function enviarBusqueda() {
        if (!formularioBusqueda) return;
        formularioBusqueda.querySelectorAll('input[name="pagina"]').forEach(function (campo) {
            campo.remove();
        });
        formularioBusqueda.submit();
    }

    if (filtroSinImagen) {
        filtroSinImagen.addEventListener("click", function () {
            const soloSinImagen = filtroSinImagen.getAttribute("aria-pressed") !== "true";
            filtroSinImagen.setAttribute("aria-pressed", soloSinImagen ? "true" : "false");
            if (campoSinImagen) {
                campoSinImagen.value = soloSinImagen ? "1" : "0";
            }
            enviarBusqueda();
        });
    }

    const modalEliminar = document.getElementById("modalEliminarEncomienda");
    const campoIdEliminar = document.getElementById("idEncomiendaEliminar");
    const campoMotivoEliminar = document.getElementById("motivoEliminacionEncomienda");
    const textoConfirmacionEliminar = document.getElementById("textoConfirmacionEliminacion");

    if (modalEliminar && campoIdEliminar && campoMotivoEliminar) {
        document.querySelectorAll("[data-abrir-modal-eliminacion]").forEach(function (botonEliminar) {
            botonEliminar.addEventListener("click", function () {
                campoIdEliminar.value = botonEliminar.dataset.id;
                campoMotivoEliminar.value = "";
                if (textoConfirmacionEliminar) {
                    textoConfirmacionEliminar.textContent =
                        "Se conservará el registro " + (botonEliminar.dataset.codigo || "")
                        + " con estado Eliminado. Indique el motivo para continuar.";
                }
                modalEliminar.hidden = false;
                campoMotivoEliminar.focus();
            });
        });

        document.querySelectorAll("[data-cerrar-modal-eliminacion]").forEach(function (botonCerrar) {
            botonCerrar.addEventListener("click", function () {
                modalEliminar.hidden = true;
            });
        });

        modalEliminar.addEventListener("click", function (evento) {
            if (evento.target === modalEliminar) {
                modalEliminar.hidden = true;
            }
        });

        document.addEventListener("keydown", function (evento) {
            if (evento.key === "Escape" && !modalEliminar.hidden) {
                modalEliminar.hidden = true;
            }
        });
    }

    const boton = document.getElementById("botonEntregarSeleccionadas");
    const botonTraspasar = document.getElementById("botonTraspasarSeleccionadas");
    const selectores = document.querySelectorAll(".selectorEncomienda");

    if (!boton || !botonTraspasar || selectores.length === 0) {
        return;
    }

    function actualizarBoton() {
        const ids = Array.from(document.querySelectorAll(".selectorEncomienda:checked"))
            .map(function (selector) { return selector.value; });
        boton.hidden = ids.length === 0;
        botonTraspasar.hidden = ids.length === 0;
        boton.dataset.ids = ids.join(",");
    }

    selectores.forEach(function (selector) {
        selector.addEventListener("change", actualizarBoton);
    });

    const modal = document.getElementById("modalEntregaSeleccionadas");
    const cerrar = document.getElementById("cerrarModalEntrega");
    const detalle = document.getElementById("detalleEntregaSeleccionadas");
    const costoBase = document.getElementById("costoBaseEntrega");
    const recargo = document.getElementById("recargoEntrega");
    const descuento = document.getElementById("descuentoEntrega");
    const totalFinal = document.getElementById("totalFinalEntrega");
    const cobrar = document.getElementById("cobrarEntregarSeleccionadas");
    const metodo = document.getElementById("metodoCobroEntrega");

    function recalcular() {
        
        const base = Number(costoBase.dataset.valor || 0) + 0;

        const extra = Math.max(0, Number(recargo.value) || 0);
        const rebaja = Math.min(base, Math.max(0, Number(descuento.value) || 0));
        totalFinal.textContent = (base + extra - rebaja).toFixed(2);
    }

    function cerrarModal() {
        modal.hidden = true;
    }

    const modalTraspaso = document.getElementById("modalTraspasoSeleccionadas");
    const cerrarTraspaso = document.getElementById("cerrarModalTraspaso");
    const detalleTraspaso = document.getElementById("detalleTraspasoSeleccionadas");
    const almacenDestino = document.getElementById("almacenDestinoTraspaso");
    const observaciones = document.getElementById("observacionesTraspaso");
    const registrarTraspaso = document.getElementById("registrarTraspasoSeleccionadas");

    function cerrarModalTraspaso() {
        modalTraspaso.hidden = true;
    }

    botonTraspasar.addEventListener("click", function () {
        const ids = Array.from(document.querySelectorAll(".selectorEncomienda:checked")).map(function (selector) {
            return selector.value;
        });
        if (ids.length === 0) return;
        detalleTraspaso.replaceChildren();
        ids.forEach(function (id) {
            const tarjeta = document.querySelector('.tarjeta-encomienda[data-id="' + id + '"]');
            if (!tarjeta) return;
            const item = document.createElement("div");
            item.textContent = tarjeta.dataset.destinatario + " | " + tarjeta.dataset.descripcion + " | " + tarjeta.dataset.codigo;
            detalleTraspaso.appendChild(item);
        });
        almacenDestino.value = "";
        observaciones.value = "";
        modalTraspaso.hidden = false;
    });

    cerrarTraspaso.addEventListener("click", cerrarModalTraspaso);
    modalTraspaso.addEventListener("click", function (evento) {
        if (evento.target === modalTraspaso) cerrarModalTraspaso();
    });
    registrarTraspaso.addEventListener("click", function () {
        const ids = Array.from(document.querySelectorAll(".selectorEncomienda:checked")).map(function (selector) {
            return Number(selector.value);
        });
        if (!almacenDestino.value) {
            alert("Seleccione el almacén destino.");
            return;
        }
        registrarTraspaso.disabled = true;
        const formulario = new FormData();
        ids.forEach(function (id) { formulario.append("ids[]", id); });
        formulario.append("id_almacen_destino", almacenDestino.value);
        formulario.append("concepto", observaciones.value.trim());
        formulario.append("csrf_token", window.traspasoCsrfToken);
        fetch(window.traspasoMultipleUrl, { method: "POST", body: formulario })
            .then(function (respuesta) {
                if (!respuesta.ok) {
                    return respuesta.text().then(function (mensaje) {
                        throw new Error(mensaje || "No se pudo registrar el traspaso.");
                    });
                }
                window.location.reload();
            })
            .catch(function (error) {
                registrarTraspaso.disabled = false;
                alert(error.message);
            });
    });

    boton.addEventListener("click", function () {
        const ids = Array.from(document.querySelectorAll(".selectorEncomienda:checked")).map(function (selector) {
            return selector.value;
        });
        if (ids.length === 0) return;
        let base = 0;
        detalle.replaceChildren();

        const tabla = document.createElement("table");
        tabla.className = "tabla-detalle-entrega";
        const thead = document.createElement("thead");
        thead.innerHTML = "<tr><th>Encomienda</th><th>Precio</th><th>Recargo</th><th>Pago</th></tr>";
        const tbody = document.createElement("tbody");
        let totalRecargo = 0;
        ids.forEach(function (id) {
            const tarjeta = document.querySelector('.tarjeta-encomienda[data-id="' + id + '"]');
            if (!tarjeta) return;
            const precio = Number(tarjeta.dataset.precio) || 2;
            const pagaRemitente = tarjeta.dataset.quienPaga === "Remitente";
            if (!pagaRemitente) {
                base += precio;
            }

            const fila = document.createElement("tr");
            const celdaEncomienda = document.createElement("td");
            celdaEncomienda.textContent = tarjeta.dataset.destinatario + " | " + tarjeta.dataset.descripcion + " | " + tarjeta.dataset.codigo;
            const celdaPrecio = document.createElement("td");
            celdaPrecio.textContent = precio.toFixed(2) + " Bs";
            const celdaRecargo = document.createElement("td");
            //calcular la fecha de registro de la encomienda con la fecha actual para determinar el recargo
            const fechaRegistro = new Date(tarjeta.dataset.fechaRegistro);
            const fechaActual = new Date();
            const semanasTranscurridas = Math.floor((fechaActual - fechaRegistro) / (7 * 24 * 60 * 60 * 1000));
            //si ha transcurrido al menos una semana desde la fecha de registro, se aplica un recargo de 1.00 Bs. por cada semana
            //si ha transcurrido menos de una semana, no se aplica recargo
            let recargo;
            if (semanasTranscurridas < 1) {
                recargo = 0.00;
            } else {
                recargo = semanasTranscurridas * 1.00;
            }
            
            celdaRecargo.textContent = recargo.toFixed(2) + " Bs"; // Inicialmente en 0.00 Bs
            const celdaPago = document.createElement("td");
            if (pagaRemitente) {
                celdaPago.innerHTML = '<span class="etiqueta-pagado">✅ Pagado (Remitente)</span>';
                
            } else {
                celdaPago.innerHTML = '<span class="etiqueta-por-cobrar">🟡 Por cobrar</span>';
            }
            fila.appendChild(celdaEncomienda);
            fila.appendChild(celdaPrecio);
            fila.appendChild(celdaRecargo);
            fila.appendChild(celdaPago);
            tbody.appendChild(fila);
            //total de recargos
            totalRecargo += recargo;
        });

        tabla.appendChild(thead);
        tabla.appendChild(tbody);
        detalle.appendChild(tabla);

        costoBase.dataset.valor = (base + totalRecargo).toFixed(2);
        costoBase.textContent = (base + totalRecargo).toFixed(2);
        recargo.value = "0.00";
        descuento.value = "0.00";
        recalcular();
        modal.hidden = false;
    });

    [recargo, descuento].forEach(function (campo) {
        campo.addEventListener("input", recalcular);
    });
    cerrar.addEventListener("click", cerrarModal);
    modal.addEventListener("click", function (evento) {
        if (evento.target === modal) cerrarModal();
    });

    cobrar.addEventListener("click", function () {
    var Toast = Swal.mixin({
      toast: true,
      position: 'top-end',
      showConfirmButton: false,
      timer: 3000
    });

        const ids = Array.from(document.querySelectorAll(".selectorEncomienda:checked")).map(function (selector) {
            return Number(selector.value);
        });
        cobrar.disabled = true;
        fetch(window.entregaMultipleUrl, {
            method: "POST",
            headers: {"Content-Type": "application/json"},
            body: JSON.stringify({
                ids: ids,
                recargo: Number(recargo.value) || 0,
                descuento: Number(descuento.value) || 0,
                medio_cobro: metodo.value,
                csrf_token: window.entregaCsrfToken
            })
        }).then(function (respuesta) {
        return respuesta.json().then(function (datos) {

            if (!respuesta.ok || !datos.ok) {
                throw new Error(
                    datos.error || "No se pudo completar la entrega."
                );
            }

            return datos;
        });
    })
         .then(function (datos) {

        // Aquí sabemos que el servidor respondió correctamente
        Toast.fire({
            icon: 'success',
            title: 'Entrega realizada correctamente.'
        });

        // Esperamos un poco para que el usuario vea el Toast
        setTimeout(function () {
            window.location.href = "buscar";
        }, 1000);
    })
        .catch(function (error) {
            cobrar.disabled = false;
            alert(error.message);
        });
    });
});
