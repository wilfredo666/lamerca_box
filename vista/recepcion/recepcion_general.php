<?php if (isset($errorVista)): ?>
  <div class="alerta-error" role="alert"><?= htmlspecialchars($errorVista, ENT_QUOTES, "UTF-8") ?></div>
<?php endif; ?>
<?php if (!empty($mensajeCliente)): ?>
  <div id="mensajeCliente" data-mensaje="<?= htmlspecialchars($mensajeCliente, ENT_QUOTES, "UTF-8") ?>" hidden></div>
<?php endif; ?>

<div class="contenedor recepcion-general">
  <div class="card">
    <?php if (empty($clientes) || empty($tiposRecepcion) || empty($clasificaciones)): ?>
      <div class="alerta-error" role="alert">
        Debe contar con clientes, tipos de recepción y clasificaciones activas antes de registrar una recepción.
      </div>
    <?php else: ?>
      <form method="POST" id="formRecepcion" class="layout-recepcion">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, "UTF-8") ?>">
        <aside class="panel-lateral">
          <div class="campo-formulario">
            <label for="buscarCliente">Buscar cliente</label>
            <input
              type="search"
              id="buscarCliente"
              autocomplete="off"
              placeholder="Escriba el nombre o celular..."
              aria-controls="resultadosClientes"
              aria-expanded="false"
              required
            >
            <input type="hidden" name="id_cliente" id="idClienteRecepcion">
            <div id="resultadosClientes" class="resultados-clientes" role="listbox" hidden>
              <?php foreach ($clientes as $cliente): ?>
                <button
                  type="button"
                  class="resultado-cliente"
                  role="option"
                  data-id="<?= (int) $cliente["id"] ?>"
                  data-nombre="<?= htmlspecialchars($cliente["nombre"], ENT_QUOTES, "UTF-8") ?>"
                  data-celular="<?= htmlspecialchars($cliente["celular"] ?? "", ENT_QUOTES, "UTF-8") ?>"
                >
                  <strong><?= htmlspecialchars($cliente["nombre"], ENT_QUOTES, "UTF-8") ?></strong>
                  <?php if (!empty($cliente["celular"])): ?>
                    <span><?= htmlspecialchars($cliente["celular"], ENT_QUOTES, "UTF-8") ?></span>
                  <?php endif; ?>
                </button>
              <?php endforeach; ?>
            </div>
          </div>

          <div id="clienteSeleccionado" class="cliente-seleccionado" hidden aria-live="polite"></div>

          <div class="campo-formulario">
            <label for="tipoRecepcion" class="form-label">Tipo de recepción <span aria-hidden="true">*</span></label>
            <select name="tipo_recepcion" id="tipoRecepcion" required>
              <option value="">Seleccione cómo llegó</option>
              <?php foreach ($tiposRecepcion as $tipo): ?>
                <option value="<?= htmlspecialchars($tipo["descripcion"], ENT_QUOTES, "UTF-8") ?>">
                  <?= htmlspecialchars($tipo["descripcion"], ENT_QUOTES, "UTF-8") ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="campo-formulario">
            <label for="empresa">Empresa o tienda <small>(opcional)</small></label>
            <input type="text" name="empresa" id="empresa" maxlength="50" placeholder="Nombre de la tienda">
          </div>

          <div class="campo-formulario">
            <label for="observaciones">Observaciones <small>(opcional)</small></label>
            <textarea name="observaciones" id="observaciones" rows="3" maxlength="5000" placeholder="Notas generales"></textarea>
          </div>
          <button type="button" class="boton-nuevo-cliente" data-toggle="modal" data-target="#modalCliente" data-accion="crear">
            <i class="fas fa-plus" aria-hidden="true"></i> Nuevo cliente
          </button>
        </aside>

        <section class="panel-encomiendas">
          <div class="titulo-paquetes">
            <div>
              <h2>Encomiendas</h2>
              <p><span id="contadorPaquetes">0</span> <span id="textoContador">encomiendas registrada</span></p>
            </div>
            <button type="button" class="boton-secundario" id="agregarEncomienda">
              <i class="fas fa-plus" aria-hidden="true"></i> Agregar encomienda
            </button>
          </div>

          <div class="tabla-responsive">
            <table>
              <thead>
                <tr>
                  <th>N°</th>
                  <th>Destinatario</th>
                  <th>Contacto</th>
                  <th>Clasificación</th>
                  <th>Descripción</th>
                  <th>Precio (Bs)</th>
                  <th>Paga</th>
                  <th><span class="sr-only">Quitar</span></th>
                </tr>
              </thead>
              <tbody id="tablaPaquetes"></tbody>
            </table>
          </div>
        </section>
        <div class="acciones-recepcion">
          <button type="submit" class="boton-guardar">
            <i class="fas fa-save" aria-hidden="true"></i> Guardar recepción
          </button>
        </div>
      </form>

      <template id="plantillaEncomienda">
        <tr>
          <td class="numero-encomienda"></td>
          <td><input type="text" name="destinatario[]" maxlength="150" required></td>
          <td><input type="text" name="contacto[]" maxlength="30" inputmode="tel"></td>
          <td>
            <select name="clasificacion[]" required>
              <option value="">Seleccione</option>
              <?php foreach ($clasificaciones as $clasificacion): ?>
                <option value="<?= htmlspecialchars($clasificacion["descripcion"], ENT_QUOTES, "UTF-8") ?>">
                  <?= htmlspecialchars($clasificacion["descripcion"], ENT_QUOTES, "UTF-8") ?>
                </option>
              <?php endforeach; ?>
            </select>
          </td>
          <td><input type="text" name="descripcion[]" maxlength="5000"></td>
          <td><input type="number" name="precio[]" min="0" step="0.01" value="2.00" required></td>
          <td>
            <select name="quien_paga[]" required>
              <option value="Destinatario">Destinatario</option>
              <option value="Remitente">Remitente</option>
            </select>
          </td>
          <td><button type="button" class="boton-quitar" aria-label="Quitar encomienda"><i class="fas fa-times" aria-hidden="true"></i></button></td>
        </tr>
      </template>
    <?php endif; ?>
  </div>
</div>

<div class="modal fade" id="modalCliente" tabindex="-1" aria-labelledby="tituloModalCliente" aria-hidden="true">
  <div class="modal-dialog">
    <form method="POST" action="<?= $base_url ?>clientes" class="modal-content" id="formCliente">
      <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, "UTF-8") ?>">
      <input type="hidden" name="accion" id="accionCliente" value="crear">
      <input type="hidden" name="id" id="idCliente">
      <input type="hidden" name="retorno" value="recepcion/general">
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

<script src="<?= $base_url ?>assets/js/modules/recepcion/recepcion_general.js"></script>