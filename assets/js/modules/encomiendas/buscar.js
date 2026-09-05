document.addEventListener("DOMContentLoaded", function () {
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
        const base = Number(costoBase.dataset.valor || 0);
        const extra = Math.max(0, Number(recargo.value) || 0);
        const rebaja = Math.min(base + extra, Math.max(0, Number(descuento.value) || 0));
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
        ids.forEach(function (id) {
            const tarjeta = document.querySelector('.tarjeta-encomienda[data-id="' + id + '"]');
            if (!tarjeta) return;
            base += Number(tarjeta.dataset.precio) || 2;
            const item = document.createElement("div");
            item.textContent = tarjeta.dataset.destinatario + " | " + tarjeta.dataset.descripcion + " | " + tarjeta.dataset.codigo;
            detalle.appendChild(item);
        });
        costoBase.dataset.valor = base.toFixed(2);
        costoBase.textContent = base.toFixed(2);
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
                if (!respuesta.ok || !datos.ok) throw new Error(datos.error || "No se pudo completar la entrega.");
                window.location.reload();
            });
        }).catch(function (error) {
            cobrar.disabled = false;
            alert(error.message);
        });
    });
});
