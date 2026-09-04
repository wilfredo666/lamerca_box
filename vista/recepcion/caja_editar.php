<?php $h = static fn($valor) => htmlspecialchars((string) ($valor ?? ""), ENT_QUOTES, "UTF-8"); ?>
<div class="contenedor recepcion-general">
  <div class="card layout-recepcion">
    <?php if (!empty($errorVista)): ?><div class="alerta-error" role="alert"><?= $h($errorVista) ?></div><?php endif; ?>
    <aside class="panel-lateral resumen-caja">
      <div class="encabezado-tarjeta">
        <div>
          <strong>📦 <?= $h($recepcion["nombre_cliente"]) ?></strong>
          <small><?= $h($recepcion["empresa"] ?: "Recepción general") ?></small>
        </div>
        <b><?= $h($recepcion["codigo"]) ?></b>
      </div>
      <div class="datos-tarjeta">
        <div>📦 <b>Tipo:</b> <?= $h($recepcion["tipo_recepcion"]) ?></div>
        <div>📱 <b>Celular:</b> <?= $h($recepcion["celular_cliente"] ?: "Sin registrar") ?></div>
        <div>📅 <b>Recepción:</b> <?= $h(date("d/m/Y H:i", strtotime($recepcion["fecha_registro"]))) ?></div>
        <div>📦 <b>Encomiendas:</b> <?= count($paquetes) ?></div>
        <div>🔵 <b>Estado:</b> <?= $h($recepcion["estado"]) ?></div>
      </div>
      <div class="foto-tarjeta">
        <?php if (!empty($recepcion["foto"])): ?>
          <img src="<?= $h($base_url . "assets/img/recepciones/" . rawurlencode(basename($recepcion["foto"]))) ?>" alt="Foto de la caja">
        <?php else: ?>
          <span>📷 Foto pendiente</span>
        <?php endif; ?>
      </div>
      <?php if ($recepcion["estado"] === "Abierta"): ?>
        <button type="submit" form="formRecepcion" class="boton-guardar boton-actualizar">
          <i class="fas fa-sync-alt" aria-hidden="true"></i> Actualizar recepción
        </button>
      <?php endif; ?>
    </aside>
    <section class="panel-encomiendas">
    <?php if ($recepcion["estado"] !== "Abierta"): ?>
      <div class="alerta-error">Esta caja está cerrada y no permite agregar encomiendas.</div>
    <?php else: ?>
      <div class="titulo-paquetes">
        <div>
          <h2>Encomiendas</h2>
          <p><span id="contadorPaquetes">1</span> <span id="textoContador">encomienda registrada</span></p>
        </div>
        <button type="button" class="boton-secundario" id="agregarEncomienda"><i class="fas fa-plus"></i> Agregar encomienda</button>
      </div>
      <form method="POST" id="formRecepcion">
        <input type="hidden" name="csrf_token" value="<?= $h($csrfToken) ?>">
        <input type="hidden" name="id_cliente" value="<?= (int) $recepcion["id_cliente"] ?>">
        <input type="hidden" name="empresa" value="<?= $h($recepcion["empresa"]) ?>">
        <input type="hidden" name="tipo_recepcion" value="<?= $h($recepcion["tipo_recepcion"]) ?>">
        <input type="hidden" name="observaciones" value="<?= $h($recepcion["observaciones"]) ?>">
        <div class="tabla-responsive">
          <table>
            <thead><tr><th>N°</th><th>Destinatario</th><th>Contacto</th><th>Clasificación</th><th>Descripción</th><th>Precio (Bs)</th><th>Paga</th><th></th></tr></thead>
            <tbody id="tablaPaquetes">
              <tr>
                <td class="numero-encomienda">1</td>
                <td><input type="text" name="destinatario[]" maxlength="150" required></td>
                <td><input type="text" name="contacto[]" maxlength="30"></td>
                <td><select name="clasificacion[]" required><option value="">Seleccione</option><?php foreach ($clasificaciones as $clasificacion): ?><option value="<?= $h($clasificacion["descripcion"]) ?>"><?= $h($clasificacion["descripcion"]) ?></option><?php endforeach; ?></select></td>
                <td><input type="text" name="descripcion[]" maxlength="5000"></td>
                <td><input type="number" name="precio[]" min="0" step="0.01" value="2.00" required></td>
                <td><select name="quien_paga[]" required><option value="Destinatario">Destinatario</option><option value="Remitente">Remitente</option></select></td>
                <td><button type="button" class="boton-quitar" aria-label="Quitar encomienda"><i class="fas fa-times"></i></button></td>
              </tr>
            </tbody>
          </table>
        </div>
      </form>
      <template id="plantillaEncomienda">
        <tr>
          <td class="numero-encomienda"></td>
          <td><input type="text" name="destinatario[]" maxlength="150" required></td>
          <td><input type="text" name="contacto[]" maxlength="30"></td>
          <td><select name="clasificacion[]" required><option value="">Seleccione</option><?php foreach ($clasificaciones as $clasificacion): ?><option value="<?= $h($clasificacion["descripcion"]) ?>"><?= $h($clasificacion["descripcion"]) ?></option><?php endforeach; ?></select></td>
          <td><input type="text" name="descripcion[]" maxlength="5000"></td>
          <td><input type="number" name="precio[]" min="0" step="0.01" value="2.00" required></td>
          <td><select name="quien_paga[]" required><option>Destinatario</option><option>Remitente</option></select></td>
          <td><button type="button" class="boton-quitar" aria-label="Quitar encomienda"><i class="fas fa-times"></i></button></td>
        </tr>
      </template>
    <?php endif; ?>
    </section>
  </div>
</div>
<script src="<?= $base_url ?>assets/js/modules/recepcion/recepcion_general.js"></script>
