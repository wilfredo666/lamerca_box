const buscador = document.getElementById("buscar");

buscador.addEventListener("keyup", function(){

    let texto = this.value.toLowerCase();

    let filas = document.querySelectorAll("#tablaCajas tr");

    filas.forEach(function(fila,indice){

        if(indice==0){

            return;

        }

        let contenido = fila.innerText.toLowerCase();

        if(contenido.includes(texto)){

            fila.style.display="";

        }else{

            fila.style.display="none";

        }

    });

});
