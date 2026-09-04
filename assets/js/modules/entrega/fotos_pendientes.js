/*
|--------------------------------------------------------------------------
| BUSCADOR
|--------------------------------------------------------------------------
*/

const buscador =
    document.getElementById("buscar");


if(buscador){

    buscador.addEventListener(
        "keyup",
        function(){

            let texto =
                this.value.toLowerCase();

            document
            .querySelectorAll(".paquete")
            .forEach(function(paquete){

                let contenido =
                    paquete.innerText
                    .toLowerCase();

                if(
                    contenido.includes(texto)
                ){

                    paquete.style.display =
                        "flex";

                }else{

                    paquete.style.display =
                        "none";

                }

            });

        }
    );

}
