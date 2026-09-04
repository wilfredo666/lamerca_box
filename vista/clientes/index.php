<?php if ($mensajeCliente !== ""): ?>
  <div id="mensajeCliente" data-mensaje="<?= htmlspecialchars($mensajeCliente, ENT_QUOTES, "UTF-8") ?>" hidden></div>
  <?php unset($_SESSION["mensaje_cliente"]); ?>
<?php endif; ?>

<section class="clientes-contenedor">
  <div class="clientes-encabezado">
    <div>
      <h1>Clientes</h1>
      <p>Administra los datos de los clientes registrados.</p>
    </div>
    <button type="button" class="btn btn-success" data-toggle="modal" data-target="#modalCliente" data-accion="crear">
      <i class="fas fa-plus"></i> Nuevo cliente
    </button>
  </div>

  <div class="card clientes-card">
    <div class="card-body">
      <div class="table-responsive">
        <table id="tablaClientes" class="table table-striped table-bordered">
          <thead>
            <tr>
              <th>Nombre</th>
              <th>Celular</th>
              <th>País</th>
              <th>Ciudad</th>
              <th>Estado</th>
              <th>Registro</th>
              <th>Acciones</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach($clientes as $cliente): ?>
              <tr>
                <td><?= htmlspecialchars($cliente["nombre"], ENT_QUOTES, "UTF-8") ?></td>
                <td><?= htmlspecialchars($cliente["celular"] ?: "Sin registrar", ENT_QUOTES, "UTF-8") ?></td>
                <td><?= htmlspecialchars($cliente["pais"], ENT_QUOTES, "UTF-8") ?></td>
                <td><?= htmlspecialchars($cliente["ciudad"], ENT_QUOTES, "UTF-8") ?></td>
                <td>
                  <span class="badge badge-<?= $cliente["activo"] ? "success" : "secondary" ?>">
                    <?= $cliente["activo"] ? "Activo" : "Inactivo" ?>
                  </span>
                </td>
                <td><?= date("d/m/Y H:i", strtotime($cliente["fecha_registro"])) ?></td>
                <td class="clientes-acciones">
                  <button type="button" class="btn btn-sm btn-primary btn-editar-cliente"
                    data-toggle="modal" data-target="#modalCliente"
                    data-id="<?= $cliente["id"] ?>"
                    data-nombre="<?= htmlspecialchars($cliente["nombre"], ENT_QUOTES, "UTF-8") ?>"
                    data-celular="<?= htmlspecialchars($cliente["celular"] ?? "", ENT_QUOTES, "UTF-8") ?>"
                    data-pais="<?= htmlspecialchars($cliente["pais"], ENT_QUOTES, "UTF-8") ?>"
                    data-ciudad="<?= htmlspecialchars($cliente["ciudad"], ENT_QUOTES, "UTF-8") ?>"
                    data-observaciones="<?= htmlspecialchars($cliente["observaciones"] ?? "", ENT_QUOTES, "UTF-8") ?>">
                    <i class="fas fa-edit"></i><span class="sr-only"> Editar</span>
                  </button>
                  <form method="POST" class="form-accion-cliente form-estado-cliente"
                    data-mensaje-confirmacion="<?= $cliente["activo"] ? "¿Desea desactivar este cliente?" : "¿Desea activar este cliente?" ?>">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, "UTF-8") ?>">
                    <input type="hidden" name="accion" value="cambiar_estado">
                    <input type="hidden" name="id" value="<?= $cliente["id"] ?>">
                    <input type="hidden" name="activo" value="<?= $cliente["activo"] ? 0 : 1 ?>">
                    <button type="submit" class="btn btn-sm btn-<?= $cliente["activo"] ? "warning" : "success" ?>">
                      <i class="fas fa-<?= $cliente["activo"] ? "ban" : "check" ?>"></i>
                      <span class="sr-only"><?= $cliente["activo"] ? " Desactivar" : " Activar" ?></span>
                    </button>
                  </form>
                  <form method="POST" class="form-accion-cliente form-eliminar-cliente">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, "UTF-8") ?>">
                    <input type="hidden" name="accion" value="eliminar">
                    <input type="hidden" name="id" value="<?= $cliente["id"] ?>">
                    <button type="submit" class="btn btn-sm btn-danger">
                      <i class="fas fa-trash"></i><span class="sr-only"> Eliminar</span>
                    </button>
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

<div class="modal fade" id="modalCliente" tabindex="-1" aria-labelledby="tituloModalCliente" aria-hidden="true">
  <div class="modal-dialog">
    <form method="POST" class="modal-content" id="formCliente">
      <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, "UTF-8") ?>">
      <input type="hidden" name="accion" id="accionCliente" value="crear">
      <input type="hidden" name="id" id="idCliente">
      <div class="modal-header">
        <h5 class="modal-title" id="tituloModalCliente">Nuevo cliente</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar"><span aria-hidden="true">&times;</span></button>
      </div>
      <div class="modal-body">
        <div class="form-group">
          <label for="nombreCliente">Nombre completo</label>
          <input type="text" class="form-control" name="nombre" id="nombreCliente" maxlength="150" required>
        </div>
        <div class="form-group">
          <label for="celularCliente">Celular</label>
          <input type="text" class="form-control" name="celular" id="celularCliente" maxlength="30">
        </div>
        <div class="form-row">
          <div class="form-group col-md-6">
            <label for="paisCliente">País</label>
            <input type="text" class="form-control" name="pais" id="paisCliente" value="Bolivia" maxlength="100" required>
          </div>
          <div class="form-group col-md-6">
            <label for="ciudadCliente">Ciudad</label>
            <select class="form-control" name="ciudad" id="ciudadCliente" required>
              <option value="" selected disabled>Seleccione una ciudad</option>
              <option value="La Paz">La Paz</option>
              <option value="El Alto">El Alto</option>
              <option value="Cochabamba">Cochabamba</option>
              <option value="Santa Cruz">Santa Cruz</option>
              <option value="Oruro">Oruro</option>
              <option value="Potosi">Potosi</option>
              <option value="Pando">Pando</option>
              <option value="Beni">Beni</option>
              <option value="Sucre">Sucre</option>
              <option value="Tarija">Tarija</option>
            </select>
          </div>
        </div>
        <div class="form-group mb-0">
          <label for="observacionesCliente">Observaciones</label>
          <textarea class="form-control" name="observaciones" id="observacionesCliente" rows="3" maxlength="5000"></textarea>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
        <button type="submit" class="btn btn-success">Guardar</button>
      </div>
    </form>
  </div>
</div>
