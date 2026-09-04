const formulario = document.querySelector("form");

const campos = formulario.querySelectorAll(
    'input[name="nombre_tiktok"], input[name="propietaria"], input[name="whatsapp"], textarea[name="observaciones"]'
);

campos.forEach(function(campo, indice){

    campo.addEventListener("keydown", function(event){

        if(event.key === "Enter"){

            event.preventDefault();

            if(indice < campos.length - 1){

                campos[indice + 1].focus();

            }else{

                formulario.querySelector("button").focus();

            }

        }

    });

});
