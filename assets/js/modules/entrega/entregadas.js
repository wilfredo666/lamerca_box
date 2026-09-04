$(function () {
  $("#tablaEntregas").DataTable({
    language: {
      emptyTable: "No hay entregas registradas",
      info: "Mostrando _START_ a _END_ de _TOTAL_ entregas",
      infoEmpty: "Mostrando 0 a 0 de 0 entregas",
      infoFiltered: "(filtrado de _MAX_ entregas)",
      lengthMenu: "Mostrar _MENU_ entregas",
      loadingRecords: "Cargando...",
      processing: "Procesando...",
      search: "Buscar:",
      zeroRecords: "No se encontraron entregas",
      paginate: {
        first: "Primero",
        last: "Último",
        next: "Siguiente",
        previous: "Anterior"
      }
    },
    order: [[6, "desc"]],
    pageLength: 10,
    responsive: true
  });
});
