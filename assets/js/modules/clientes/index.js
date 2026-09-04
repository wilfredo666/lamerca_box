$(function () {
  $("#tablaClientes").DataTable({
    language: {
      decimal: "",
      emptyTable: "No hay clientes registrados",
      info: "Mostrando _START_ a _END_ de _TOTAL_ clientes",
      infoEmpty: "Mostrando 0 a 0 de 0 clientes",
      infoFiltered: "(filtrado de _MAX_ clientes)",
      lengthMenu: "Mostrar _MENU_ clientes",
      loadingRecords: "Cargando...",
      processing: "Procesando...",
      search: "Buscar:",
      zeroRecords: "No se encontraron clientes",
      paginate: {
        first: "Primero",
        last: "Último",
        next: "Siguiente",
        previous: "Anterior"
      }
    },
    order: [[0, "asc"]],
    pageLength: 10,
    responsive: true
  });

  $("#modalCliente").on("show.bs.modal", function (event) {
    const button = $(event.relatedTarget);
    const form = $("#formCliente")[0];
    const esEdicion = button.hasClass("btn-editar-cliente");

    form.reset();
    $("#accionCliente").val(esEdicion ? "editar" : "crear");
    $("#idCliente").val(esEdicion ? button.data("id") : "");
    $("#tituloModalCliente").text(esEdicion ? "Editar cliente" : "Nuevo cliente");
    $("#paisCliente").val(esEdicion ? button.data("pais") : "Bolivia");

    if (esEdicion) {
      $("#nombreCliente").val(button.data("nombre"));
      $("#celularCliente").val(button.data("celular"));
      $("#paisCliente").val(button.data("pais"));
      $("#ciudadCliente").val(button.data("ciudad"));
      $("#observacionesCliente").val(button.data("observaciones"));
    }
  });

  $(".form-eliminar-cliente, .form-estado-cliente").on("submit", function (event) {
    event.preventDefault();
    const form = this;
    const esEliminacion = $(form).hasClass("form-eliminar-cliente");
    const mensaje = esEliminacion
      ? "¿Desea eliminar este cliente? Esta acción no se puede deshacer."
      : $(form).data("mensaje-confirmacion");

    Swal.fire({
      title: "¿Está seguro?",
      text: mensaje,
      icon: "warning",
      showCancelButton: true,
      confirmButtonColor: "#dc3545",
      cancelButtonColor: "#6c757d",
      confirmButtonText: esEliminacion ? "Sí, eliminar" : "Sí, continuar",
      cancelButtonText: "Cancelar"
    }).then(function (resultado) {
      if (resultado.isConfirmed) {
        form.submit();
      }
    });
  });

  const mensajeExito = $("#mensajeCliente").data("mensaje");
  if (mensajeExito) {
    Swal.fire({
      title: "Cliente registrado",
      text: mensajeExito,
      icon: "success",
      toast: true,
      position: "top-end",
      showConfirmButton: false,
      timer: 1000,
      timerProgressBar: true
    });
  }
});
