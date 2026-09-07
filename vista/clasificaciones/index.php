<?php
$h = function ($valor) { return htmlspecialchars((string) ($valor ?? ""), ENT_QUOTES, "UTF-8"); };
?>
<section class="clasificaciones-contenedor">
  <script>window.clasificacionesBaseUrl = <?= json_encode($base_url) ?>;</script>
  <div class="clasificaciones-encabezado">
    <div>
      <h1>Clasificaciones</h1>
      <p>Administra las clasificaciones disponibles para registrar encomiendas.</p>
    </div>
    <button type="button" class="btn btn-success" data-toggle="modal" data-target="#modalClasificacion">
      <i class="fas fa-plus"></i> Nueva clasificación
    </button>
  </div>

  <div class="card clasificaciones-card">
    <div class="card-body table-responsive">
      <table id="tablaClasificaciones" class="table table-striped table-bordered">
        <thead><tr><th>Descripción</th><th>Estado</th><th>Fecha de creación</th><th>Acciones</th></tr></thead>
        <tbody>
          <?php foreach ($clasificaciones as $clasificacion): ?>
            <tr>
              <td><?= $h($clasificacion["descripcion"]) ?></td>
              <td><span class="badge badge-<?= (int) $clasificacion["estado"] === 1 ? "success" : "secondary" ?>"><?= (int) $clasificacion["estado"] === 1 ? "Activo" : "Inactivo" ?></span></td>
              <td><?= $h(date("d/m/Y H:i", strtotime($clasificacion["fecha_creacion"]))) ?></td>
              <td class="clasificaciones-acciones">
                <button type="button" class="btn btn-sm btn-primary btn-editar-clasificacion" data-toggle="modal" data-target="#modalClasificacion" data-id="<?= (int) $clasificacion["id"] ?>" data-descripcion="<?= $h($clasificacion["descripcion"]) ?>" data-estado="<?= (int) $clasificacion["estado"] ?>"><i class="fas fa-edit"></i></button>
                <form method="POST" action="<?= $base_url ?>clasificaciones/eliminar" class="form-eliminar-clasificacion">
                  <input type="hidden" name="csrf_token" value="<?= $h($csrfToken) ?>">
                  <input type="hidden" name="id" value="<?= (int) $clasificacion["id"] ?>">
                  <button type="submit" class="btn btn-sm btn-danger" title="Eliminar"><i class="fas fa-trash"></i></button>
                </form>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</section>

<div class="modal fade" id="modalClasificacion" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <form method="POST" class="modal-content" id="formClasificacion">
      <input type="hidden" name="csrf_token" value="<?= $h($csrfToken) ?>">
      <div class="modal-header"><h5 class="modal-title" id="tituloModalClasificacion">Nueva clasificación</h5><button type="button" class="close" data-dismiss="modal">&times;</button></div>
      <div class="modal-body">
        <div class="form-group"><label for="descripcionClasificacion">Descripción</label><input id="descripcionClasificacion" name="descripcion" class="form-control" maxlength="50" required></div>
        <div class="form-group mb-0"><label for="estadoClasificacion">Estado</label><select id="estadoClasificacion" name="estado" class="form-control"><option value="1">Activo</option><option value="0">Inactivo</option></select></div>
      </div>
      <div class="modal-footer"><button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button><button type="submit" class="btn btn-success">Guardar</button></div>
    </form>
  </div>
</div>
