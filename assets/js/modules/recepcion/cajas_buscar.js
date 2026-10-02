document.addEventListener("DOMContentLoaded", function () {
    const buscador = document.querySelector("[data-buscador-tiempo-real]");
    const formularioBusqueda = buscador ? buscador.closest("form") : null;
    let temporizadorBusqueda;

    if (!buscador || !formularioBusqueda) {
        return;
    }

    buscador.addEventListener("input", function () {
        window.clearTimeout(temporizadorBusqueda);
        temporizadorBusqueda = window.setTimeout(function () {
            formularioBusqueda.querySelectorAll('input[name="pagina"]').forEach(function (campo) {
                campo.remove();
            });
            formularioBusqueda.submit();
        }, 350);
    });
});
