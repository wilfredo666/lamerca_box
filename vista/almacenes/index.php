<?php $h = function ($valor) { return htmlspecialchars((string) ($valor ?? ""), ENT_QUOTES, "UTF-8"); }; ?>
<section class="almacenes-contenedor">
  <div class="almacenes-encabezado">
    <div>
      <h1>Almacenes</h1>
      <p>Consulta los almacenes registrados.</p>
    </div>
    <script>window.almacenesBaseUrl = <?= json_encode($base_url) ?>;</script>
    <button type="button" class="btn btn-success" data-toggle="modal" data-target="#modalAlmacen" data-accion="nuevo">
      <i class="fas fa-plus"></i> Nuevo almacén
    </button>
  </div>

  <div class="card almacenes-card">
    <div class="card-body">
      <div class="table-responsive">
        <table id="tablaAlmacenes" class="table table-striped table-bordered">
          <thead>
            <tr>
              <th>Nombre</th>
              <th>Descripción</th>
              <th>Ciudad</th>
              <th>Dirección</th>
              <th>Encargado</th>
              <th>Contacto</th>
              <th>Estado</th>
              <th>Acciones</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($almacenes as $almacen): ?>
              <tr>
                <td><?= $h($almacen["nombre_almacen"]) ?></td>
                <td><?= $h($almacen["descripcion"] ?: "Sin descripción") ?></td>
                <td><?= $h($almacen["ciudad"]) ?></td>
                <td><?= $h($almacen["direccion"] ?: "Sin registrar") ?></td>
                <td><?= $h($almacen["encargado"] ?: "Sin registrar") ?></td>
                <td><?= $h($almacen["contacto"] ?: "Sin registrar") ?></td>
                <td>
                  <span class="badge badge-<?= (int) $almacen["estado_almacen"] === 1 ? "success" : "secondary" ?>">
                    <?= (int) $almacen["estado_almacen"] === 1 ? "Activo" : "Inactivo" ?>
                  </span>
                </td>
                <td class="almacenes-acciones">
                  <button type="button" class="btn btn-sm btn-primary btn-editar-almacen"
                    data-toggle="modal" data-target="#modalAlmacen"
                    data-id="<?= (int) $almacen["id_almacen"] ?>"
                    data-nombre="<?= $h($almacen["nombre_almacen"]) ?>"
                    data-descripcion="<?= $h($almacen["descripcion"]) ?>"
                    data-ciudad="<?= $h($almacen["ciudad"]) ?>"
                    data-direccion="<?= $h($almacen["direccion"]) ?>"
                    data-encargado="<?= $h($almacen["encargado"]) ?>"
                    data-contacto="<?= $h($almacen["contacto"]) ?>"
                    data-estado="<?= (int) $almacen["estado_almacen"] ?>">
                    <i class="fas fa-edit"></i>
                  </button>
                  <form method="POST" action="<?= $base_url ?>almacenes/eliminar" class="form-eliminar-almacen">
                    <input type="hidden" name="csrf_token" value="<?= $h($csrfToken) ?>">
                    <input type="hidden" name="id" value="<?= (int) $almacen["id_almacen"] ?>">
                    <button type="submit" class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
                  </form>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</section>

<div class="modal fade" id="modalAlmacen" tabindex="-1" aria-labelledby="tituloModalAlmacen" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <form method="POST" class="modal-content" id="formAlmacen">
      <input type="hidden" name="csrf_token" value="<?= $h($csrfToken) ?>">
      <div class="modal-header">
        <h5 class="modal-title" id="tituloModalAlmacen">Nuevo almacén</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar"><span aria-hidden="true">&times;</span></button>
      </div>
      <div class="modal-body">
        <div class="form-row">
          <div class="form-group col-md-6"><label for="nombreAlmacen">Nombre</label><input id="nombreAlmacen" name="nombre" class="form-control" maxlength="50" required></div>
          <div class="form-group col-md-6"><label for="ciudadAlmacen">Ciudad</label><input id="ciudadAlmacen" name="ciudad" class="form-control" maxlength="100" required></div>
        </div>
        <div class="form-group"><label for="descripcionAlmacen">Descripción</label><input id="descripcionAlmacen" name="descripcion" class="form-control" maxlength="100"></div>
        <div class="form-group"><label for="direccionAlmacen">Dirección</label><input id="direccionAlmacen" name="direccion" class="form-control" maxlength="100"></div>
        <div class="form-row">
          <div class="form-group col-md-6"><label for="encargadoAlmacen">Encargado</label><input id="encargadoAlmacen" name="encargado" class="form-control" maxlength="50"></div>
          <div class="form-group col-md-6"><label for="contactoAlmacen">Contacto</label><input id="contactoAlmacen" name="contacto" class="form-control" maxlength="50"></div>
        </div>
        <div class="form-group mb-0"><label for="estadoAlmacen">Estado</label><select id="estadoAlmacen" name="estado" class="form-control"><option value="1">Activo</option><option value="0">Inactivo</option></select></div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
        <button type="submit" class="btn btn-success">Guardar</button>
      </div>
    </form>
  </div>
</div>
