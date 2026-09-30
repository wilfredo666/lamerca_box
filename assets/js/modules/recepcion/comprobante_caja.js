document.querySelectorAll(".btnWhatsapp").forEach(function (botonWhatsapp) {
  botonWhatsapp.addEventListener("click", function () {
    let numero = (this.dataset.whatsapp || "").replace(/\D/g, "");

    if (numero.length === 8) {
      numero = "591" + numero;
    }

    if (numero === "") {
      alert("No hay un número de WhatsApp registrado para este contacto.");
      return;
    }

    const url =
      "https://api.whatsapp.com/send/?phone=" +
      numero +
      "&text=" +
      encodeURIComponent(this.dataset.mensaje || "") +
      "&type=phone_number&app_absent=0";

    window.open(url, "_blank", "noopener");
  });
});
