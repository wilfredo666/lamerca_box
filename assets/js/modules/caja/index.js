$(function () {
  $("#tablaCaja").DataTable({
    language: {
      emptyTable: "No hay información",
      info: "Mostrando _START_ a _END_ de _TOTAL_ entradas",
      infoEmpty: "Mostrando 0 a 0 de 0 entradas",
      infoFiltered: "(filtrado de _MAX_ entradas)",
      lengthMenu: "Mostrar _MENU_ entradas",
      search: "Buscar:",
      zeroRecords: "No se encontraron movimientos",
      paginate: { previous: "Anterior", next: "Siguiente" }
    },
    order: [[5, "desc"]],
    pageLength: 10,
    responsive: true,
    dom: "Bfrtip",
    buttons: ["copy", "csv", "excel", "pdf", "print"]
  });

  $(".form-anular-caja").on("submit", function (event) {
    event.preventDefault();
    const form = this;
    Swal.fire({
      title: "¿Anular movimiento?",
      text: "El movimiento dejará de incluirse en los totales de caja.",
      icon: "warning",
      showCancelButton: true,
      confirmButtonColor: "#d33",
      cancelButtonText: "Cancelar",
      confirmButtonText: "Sí, anular"
    }).then(function (result) {
      if (result.isConfirmed) form.submit();
    });
  });
});
