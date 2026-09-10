document.addEventListener("DOMContentLoaded", function () {
    const buscador = document.querySelector("[data-buscador-tiempo-real]");
    const tarjetas = Array.from(document.querySelectorAll(".grid-encomiendas .tarjeta-encomienda"));
    const contador = document.querySelector("[data-contador-resultados]");
    const sinResultados = document.querySelector("[data-sin-resultados]");

    if (!buscador) {
        return;
    }

    buscador.addEventListener("input", function () {
        const termino = buscador.value.trim().toLocaleLowerCase();
        let visibles = 0;

        tarjetas.forEach(function (tarjeta) {
            const coincide = tarjeta.textContent.toLocaleLowerCase().includes(termino);
            tarjeta.hidden = !coincide;
            visibles += coincide ? 1 : 0;
        });

        if (contador) {
            contador.textContent = visibles;
        }
        if (sinResultados) {
            sinResultados.hidden = visibles !== 0;
        }
    });
});
