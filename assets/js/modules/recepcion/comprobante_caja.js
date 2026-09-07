const botonWhatsapp = document.querySelector(".btnWhatsapp");

if (botonWhatsapp) {
  botonWhatsapp.addEventListener("click", function(){

    let numero = this.dataset.whatsapp;

    if (numero.length === 8) {

        numero = "591" + numero;

    }

    if (numero === "") {
        alert("Este cliente no tiene un número de WhatsApp registrado.");
        return;

    }

    let url =
        "https://api.whatsapp.com/send/?phone=" +
        numero +
        "&text=" +
        encodeURIComponent(this.dataset.mensaje) +
        "&type=phone_number&app_absent=0";

    window.open(url, "_blank", "noopener");
  });
}
