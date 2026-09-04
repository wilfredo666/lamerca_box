/*
|--------------------------------------------------------------------------
| BUSCADOR
|--------------------------------------------------------------------------
*/

const buscador =
    document.getElementById("buscar");


buscador.addEventListener(

    "keyup",

    function(){

        let texto =
            this.value.toLowerCase();


        let paquetes =
            document.querySelectorAll(
                ".paquete"
            );


        paquetes.forEach(

            function(paquete){

                let contenido =
                    paquete.innerText
                    .toLowerCase();


                if(
                    contenido.includes(texto)
                ){

                    paquete.style.display =
                        "flex";

                }

                else{

                    paquete.style.display =
                        "none";

                }

            }

        );

    }

);
