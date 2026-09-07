$(function () {
  $("#tablaClasificaciones").DataTable({
    language: {
      emptyTable: "No hay clasificaciones registradas",
      info: "Mostrando _START_ a _END_ de _TOTAL_ clasificaciones",
      infoEmpty: "Mostrando 0 a 0 de 0 clasificaciones",
      infoFiltered: "(filtrado de _MAX_ clasificaciones)",
      lengthMenu: "Mostrar _MENU_ clasificaciones",
      search: "Buscar:",
      zeroRecords: "No se encontraron clasificaciones",
      paginate: { first: "Primero", last: "Último", next: "Siguiente", previous: "Anterior" }
    },
    order: [[0, "asc"]],
    pageLength: 10,
    responsive: true
  });

  $("#modalClasificacion").on("show.bs.modal", function (event) {
    const button = $(event.relatedTarget);
    const editar = button.hasClass("btn-editar-clasificacion");
    $("#formClasificacion").attr("action", editar
      ? window.clasificacionesBaseUrl + "clasificaciones/editar?id=" + button.data("id")
      : window.clasificacionesBaseUrl + "clasificaciones/nuevo");
    $("#tituloModalClasificacion").text(editar ? "Editar clasificación" : "Nueva clasificación");
    $("#descripcionClasificacion").val(editar ? button.data("descripcion") : "");
    $("#estadoClasificacion").val(editar ? button.data("estado") : "1");
  });

  $(".form-eliminar-clasificacion").on("submit", function (event) {
    event.preventDefault();
    const form = this;
    Swal.fire({
      title: "¿Eliminar clasificación?",
      text: "Si está utilizada, deberá marcarla como inactiva.",
      icon: "warning",
      showCancelButton: true,
      confirmButtonColor: "#d33",
      cancelButtonText: "Cancelar",
      confirmButtonText: "Sí, eliminar"
    }).then(function (result) {
      if (result.isConfirmed) form.submit();
    });
  });
});
