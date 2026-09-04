const buscador=document.getElementById("buscar");

buscador.addEventListener("keyup",function(){

let texto=this.value.toLowerCase();

document.querySelectorAll(".filaCaja").forEach(function(fila){

let contenido=fila.innerText.toLowerCase();

fila.style.display=contenido.includes(texto) ? "" : "none";

});

});
