const botonWhatsapp = document.querySelector(".btnWhatsapp");

botonWhatsapp.addEventListener("click", function(){

    let numero = this.dataset.whatsapp;

    if(numero.length === 8){

        numero = "591" + numero;

    }

    if(numero === ""){

        alert("Esta Caja TikTok no tiene un número de WhatsApp registrado.");
        return;

    }

    let url =
        "https://wa.me/" +
        numero +
        "?text=" +
        encodeURIComponent(this.dataset.mensaje);

    window.open(url,"_blank");

});
