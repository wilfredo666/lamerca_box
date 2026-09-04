$(function () {
  $("#tablaUsuarios").DataTable({
    language: { emptyTable: "No hay usuarios registrados", info: "Mostrando _START_ a _END_ de _TOTAL_ usuarios", infoEmpty: "Mostrando 0 a 0 de 0 usuarios", infoFiltered: "(filtrado de _MAX_ usuarios)", lengthMenu: "Mostrar _MENU_ usuarios", search: "Buscar:", zeroRecords: "No se encontraron usuarios", paginate: { first: "Primero", last: "Último", next: "Siguiente", previous: "Anterior" } },
    order: [[0, "asc"]], pageLength: 10, responsive: true
  });
  $("#modalUsuario").on("show.bs.modal", function (event) {
    const button = $(event.relatedTarget), editar = button.hasClass("btn-editar-usuario");
    $("#formUsuario").attr("action", editar ? window.usuariosBaseUrl + "usuarios/editar?id=" + button.data("id") : window.usuariosBaseUrl + "usuarios/nuevo");
    $("#tituloModalUsuario").text(editar ? "Editar usuario" : "Nuevo usuario");
    $("#nombreUsuario").val(editar ? button.data("nombre") : "");
    $("#emailUsuario").val(editar ? button.data("email") : "");
    $("#passwordUsuario").val("").prop("required", !editar);
    $("#categoriaUsuario").val(editar ? button.data("categoria") : "Encargado");
    $("#estadoUsuario").val(editar ? button.data("estado") : "1");
  });
  $(".form-eliminar-usuario").on("submit", function (event) {
    event.preventDefault(); const form = this;
    Swal.fire({ title: "¿Eliminar usuario?", text: "Esta acción no se puede deshacer.", icon: "warning", showCancelButton: true, confirmButtonColor: "#d33", cancelButtonText: "Cancelar", confirmButtonText: "Sí, eliminar" }).then(function (result) { if (result.isConfirmed) form.submit(); });
  });
});
