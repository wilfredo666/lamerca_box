$(function () {
  $("#tablaTraspasos").DataTable({
    language: {
      emptyTable: "No hay traspasos registrados",
      info: "Mostrando _START_ a _END_ de _TOTAL_ traspasos",
      infoEmpty: "Mostrando 0 a 0 de 0 traspasos",
      infoFiltered: "(filtrado de _MAX_ traspasos)",
      lengthMenu: "Mostrar _MENU_ traspasos",
      loadingRecords: "Cargando...",
      processing: "Procesando...",
      search: "Buscar:",
      zeroRecords: "No se encontraron traspasos",
      paginate: {
        first: "Primero",
        last: "Último",
        next: "Siguiente",
        previous: "Anterior"
      }
    },
    order: [[7, "desc"]],
    pageLength: 10,
    responsive: true
  });
});
