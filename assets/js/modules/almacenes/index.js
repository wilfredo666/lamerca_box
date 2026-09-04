$(function () {
  $("#tablaAlmacenes").DataTable({
    language: {
      emptyTable: "No hay almacenes registrados",
      info: "Mostrando _START_ a _END_ de _TOTAL_ almacenes",
      infoEmpty: "Mostrando 0 a 0 de 0 almacenes",
      infoFiltered: "(filtrado de _MAX_ almacenes)",
      lengthMenu: "Mostrar _MENU_ almacenes",
      search: "Buscar:",
      zeroRecords: "No se encontraron almacenes",
      paginate: { first: "Primero", last: "Último", next: "Siguiente", previous: "Anterior" }
    },
    order: [[0, "asc"]],
    pageLength: 10,
    responsive: true
  });

  $("#modalAlmacen").on("show.bs.modal", function (event) {
    const button = $(event.relatedTarget);
    const editar = button.hasClass("btn-editar-almacen");
    const form = $("#formAlmacen");
    form.attr("action", editar
      ? window.almacenesBaseUrl + "almacenes/editar?id=" + button.data("id")
      : window.almacenesBaseUrl + "almacenes/nuevo");
    $("#tituloModalAlmacen").text(editar ? "Editar almacén" : "Nuevo almacén");
    $("#nombreAlmacen").val(editar ? button.data("nombre") : "").prop("readonly", false);
    $("#descripcionAlmacen").val(editar ? button.data("descripcion") : "");
    $("#ciudadAlmacen").val(editar ? button.data("ciudad") : "");
    $("#direccionAlmacen").val(editar ? button.data("direccion") : "");
    $("#encargadoAlmacen").val(editar ? button.data("encargado") : "");
    $("#contactoAlmacen").val(editar ? button.data("contacto") : "");
    $("#estadoAlmacen").val(editar ? button.data("estado") : "1");
  });

  $(".form-eliminar-almacen").on("submit", function (event) {
    event.preventDefault();
    const form = this;
    Swal.fire({
      title: "¿Eliminar almacén?",
      text: "Esta acción no se puede deshacer.",
      icon: "warning",
      showCancelButton: true,
      confirmButtonColor: "#d33",
      cancelButtonColor: "#6c757d",
      confirmButtonText: "Sí, eliminar",
      cancelButtonText: "Cancelar"
    }).then(function (result) {
      if (result.isConfirmed) {
        form.submit();
      }
    });
  });
});
