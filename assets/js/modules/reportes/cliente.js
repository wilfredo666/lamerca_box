const buscador = document.getElementById("buscar");

buscador.addEventListener("keyup", function(){

    let texto = this.value.toLowerCase();

    let tarjetas = document.querySelectorAll(".cliente-card");

    tarjetas.forEach(function(tarjeta){

        let contenido = tarjeta.innerText.toLowerCase();

        if(contenido.includes(texto)){

            tarjeta.style.display = "block";

        }else{

            tarjeta.style.display = "none";

        }

    });

});
