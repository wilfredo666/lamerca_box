document.addEventListener("DOMContentLoaded", function () {
    const boton = document.getElementById("botonEntregarSeleccionadas");
    const selectores = document.querySelectorAll(".selectorEncomienda");

    if (!boton || selectores.length === 0) {
        return;
    }

    function actualizarBoton() {
        const ids = Array.from(document.querySelectorAll(".selectorEncomienda:checked"))
            .map(function (selector) { return selector.value; });
        boton.hidden = ids.length === 0;
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
