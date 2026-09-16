<?php $h = static fn($valor) => htmlspecialchars((string) ($valor ?? ""), ENT_QUOTES, "UTF-8"); ?>
<div class="busqueda-encomiendas">
  <?php if (!$encomienda): ?>
    <p class="sin-resultados">Encomienda no encontrada.</p>
  <?php else: ?>
    <div class="container mt-4 card shadow-sm">
      <div class="row">
        <!-- Columna de datos -->
        <div class="col-md-6">
          <div class="card-body">

            <article class="tarjeta-encomienda"
              data-id="<?= (int) $encomienda["id"] ?>"
              data-destinatario="<?= $h($encomienda["destinatario"]) ?>"
              data-descripcion="<?= $h($encomienda["descripcion"] ?: "Sin descripción") ?>"
              data-codigo="<?= $h($encomienda["codigo"]) ?>"
              data-precio="<?= number_format((float) ($encomienda["precio"] ?? 2), 2, ".", "") ?>"
              data-quien-paga="<?= $h($encomienda["quien_paga"]) ?>"
              data-fecha-registro="<?= $h($encomienda["fecha_registro"]) ?>">

              <label class="selector-encomienda" aria-label="Seleccionar encomienda"  style="display:none">
                <input type="checkbox" class="selectorEncomienda"
                  value="<?= (int) $encomienda["id"] ?>"
                  <?= $encomienda["estado"] === "Pendiente" ? "" : "disabled" ?> checked>
              </label>

              <div class="encabezado-tarjeta">
                <div class="identidad-encomienda">
                  <div><b>👤 Destinatario: </b><?= $h($encomienda["destinatario"]) ?></div>
                  <div class="etiquetas-encomienda">
                    <?php
                    $tipoRecepcion = mb_strtolower(trim((string) $encomienda["tipo_recepcion"]), "UTF-8");
                    $codigoClase = in_array($tipoRecepcion, ["otro", "alfabeto"], true)
                      ? "etiqueta-codigo"
                      : "etiqueta-codigo etiqueta-codigo-verde";
                    ?>
                    <?php if ($tipoRecepcion === "alfabeto"): ?>
                      <span class="etiqueta-inicial"><?= $h(mb_strtoupper(mb_substr(trim((string) $encomienda["destinatario"]), 0, 1, "UTF-8"), "UTF-8")) ?></span>
                    <?php endif; ?>
                    <b class="<?= $codigoClase ?>"><?= $h($encomienda["codigo"]) ?></b>
                  </div>
                </div>
              </div>

              <div class="datos-tarjeta">
                <div>📦 <b>Descripción:</b> <?= $h($encomienda["descripcion"] ?: "Sin descripción") ?></div>
                <div>🏷️ <b>Clasificación:</b> <?= $h($encomienda["clasificacion"]) ?></div>
                <div>🧑 <b>Remitente:</b> <?= $h($encomienda["nombre_cliente"]) ?></div>
                <div>📱 <b>Contacto:</b> <?= $h($encomienda["contacto"] ?: "Sin registrar") ?></div>
                <div>📦 <b>Recepción:</b> <?= $h($encomienda["tipo_recepcion"]) ?></div>
                <div>🏢 <b>Empresa:</b> <?= $h($encomienda["empresa"] ?: "Sin empresa registrada") ?></div>
                <div>💰 <b>Precio:</b> Bs <?= number_format($encomienda["precio"], 2) ?></div>
                <div>📅 <b>Fecha:</b> <?= $h(date("d/m/Y H:i", strtotime($encomienda["fecha_registro"]))) ?></div>
              </div>
            </article>

            <div class="mt-3">
              <a class="btn btn-warning me-2" href="<?= $base_url ?>encomiendas/editar?id=<?= (int) $encomienda["id"] ?>">✏ Editar</a>
              <button type="button" id="botonEntregarSeleccionadas" class="btn btn-success">✅ Entregar</button>
              <button type="button" id="botonTraspasarSeleccionadas" class="btn btn-primary">↔ Traspasar</button>
              <a class="btn btn-secondary" href="<?= $base_url ?>encomiendas/buscar">← Volver</a>
            </div>
          </div>
        </div>

        <!-- Columna de imagen -->
        <div class="col-md-6">
          <div class="card-body text-center">
            <?php if (!empty($encomienda["foto"])): ?>
              <div>
                <img src="<?= $base_url . "assets/img/paquetes/" . rawurlencode(basename($encomienda["foto"])) ?>"
                     alt="Foto de la encomienda"
                     class="w-100 rounded"
                     style="max-height:100%; object-fit:cover;">
              </div>
            <?php else: ?>
              <div class="foto-tarjeta">📷 Foto pendiente</div>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>
  <?php endif; ?>
</div>

<!-- Modal de entrega -->
<div id="modalEntregaSeleccionadas" class="modal-entrega-lista" hidden>
  <div class="modal-entrega-lista-contenido" role="dialog" aria-modal="true" aria-labelledby="tituloModalEntrega">
    <button type="button" class="cerrar-modal-entrega" id="cerrarModalEntrega" aria-label="Cerrar">&times;</button>
    <h2 id="tituloModalEntrega">Entregar encomiendas</h2>
    <div id="detalleEntregaSeleccionadas" class="detalle-entrega-seleccionadas"></div>
    <div class="resumen-cobro-entrega">
      <div><span>Costo total:</span><strong><span id="costoBaseEntrega">0.00</span> Bs</strong></div>
      <label>Recargo <input type="number" id="recargoEntrega" min="0" step="0.50" value="0.00"></label>
      <label>Descuento <input type="number" id="descuentoEntrega" min="0" step="0.50" value="0.00"></label>
      <div class="total-final-entrega"><span>Total a cobrar:</span><strong><span id="totalFinalEntrega">0.00</span> Bs</strong></div>
      <label>Método de cobro
        <select id="metodoCobroEntrega">
          <option value="Efectivo">Efectivo</option>
          <option value="QR">QR</option>
        </select>
      </label>
    </div>
    <button type="button" id="cobrarEntregarSeleccionadas" class="boton-cobrar-entrega">💵 Cobrar y entregar</button>
  </div>
</div>
<script>
  window.entregaMultipleUrl = <?= json_encode($base_url . "entrega/multiple") ?>;
  window.entregaCsrfToken = <?= json_encode($csrfToken) ?>;
</script>

<div id="modalTraspasoSeleccionadas" class="modal-entrega-lista" hidden>
  <div class="modal-entrega-lista-contenido" role="dialog" aria-modal="true" aria-labelledby="tituloModalTraspaso">
    <button type="button" class="cerrar-modal-entrega" id="cerrarModalTraspaso" aria-label="Cerrar">&times;</button>
    <h2 id="tituloModalTraspaso">Traspasar encomiendas</h2>
    <div id="detalleTraspasoSeleccionadas" class="detalle-entrega-seleccionadas"></div>
    <div class="resumen-cobro-entrega">
      <label>Almacén destino
        <select id="almacenDestinoTraspaso" required>
          <option value="">Seleccione un almacén</option>
          <?php foreach ($almacenesTraspaso as $almacen): ?>
            <option value="<?= (int) $almacen["id_almacen"] ?>">
              <?= $h($almacen["nombre_almacen"] . " - " . $almacen["ciudad"]) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </label>
      <label>Observaciones
        <textarea id="observacionesTraspaso" maxlength="255" rows="3"></textarea>
      </label>
    </div>
    <button type="button" id="registrarTraspasoSeleccionadas" class="boton-cobrar-entrega">↔ Registrar traspaso</button>
  </div>
</div>
<script>
  window.traspasoMultipleUrl = <?= json_encode($base_url . "traspaso/multiple") ?>;
  window.traspasoCsrfToken = <?= json_encode($csrfToken) ?>;
</script>

