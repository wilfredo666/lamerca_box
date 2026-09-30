$(function () {
  $("#tablaEncomiendasEliminadas").DataTable({
    language: {
      emptyTable: "No hay encomiendas eliminadas",
      info: "Mostrando _START_ a _END_ de _TOTAL_ encomiendas eliminadas",
      infoEmpty: "Mostrando 0 a 0 de 0 encomiendas eliminadas",
      infoFiltered: "(filtrado de _MAX_ encomiendas)",
      lengthMenu: "Mostrar _MENU_ encomiendas",
      loadingRecords: "Cargando...",
      processing: "Procesando...",
      search: "Buscar:",
      zeroRecords: "No se encontraron encomiendas eliminadas",
      paginate: {
        first: "Primero",
        last: "Último",
        next: "Siguiente",
        previous: "Anterior"
      }
    },
    order: [[3, "desc"]],
    pageLength: 10,
    responsive: true
  });
});
