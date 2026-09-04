<?php
$h = static fn($valor) => htmlspecialchars((string) ($valor ?? ""), ENT_QUOTES, "UTF-8");
?>
<div class="busqueda-encomiendas">
  <div class="encabezado-busqueda">
    <h1>Encomiendas <span><?= count($encomiendas) ?></span> <button type="button" id="botonEntregarSeleccionadas" class="boton-entregar-seleccionadas" hidden>✅ Entregar</button></h1>
    <form method="GET" action="<?= $base_url ?>encomiendas/buscar">
      <input type="hidden" name="ruta" value="encomiendas/buscar">
      <input type="search" name="buscar" value="<?= $h($buscar) ?>" placeholder="🔎 Buscar por nombre o celular..." autofocus>
    </form>
  </div>
  <div id="modalEntregaSeleccionadas" class="modal-entrega-lista" hidden>
    <div class="modal-entrega-lista-contenido" role="dialog" aria-modal="true" aria-labelledby="tituloModalEntrega">
      <button type="button" class="cerrar-modal-entrega" id="cerrarModalEntrega" aria-label="Cerrar">&times;</button>
      <h2 id="tituloModalEntrega">Entregar encomiendas</h2>
      <div id="detalleEntregaSeleccionadas" class="detalle-entrega-seleccionadas"></div>
      <div class="resumen-cobro-entrega">
        <div><span>Costo total:</span><strong><span id="costoBaseEntrega">0.00</span> Bs</strong></div>
        <label>Recargo <input type="number" id="recargoEntrega" min="0" step="0.01" value="0.00"></label>
        <label>Descuento <input type="number" id="descuentoEntrega" min="0" step="0.01" value="0.00"></label>
        <div class="total-final-entrega"><span>Total a cobrar:</span><strong><span id="totalFinalEntrega">0.00</span> Bs</strong></div>
        <label>Método de cobro
          <select id="metodoCobroEntrega"><option value="Efectivo">Efectivo</option><option value="QR">QR</option></select>
        </label>
      </div>
      <button type="button" id="cobrarEntregarSeleccionadas" class="boton-cobrar-entrega">💵 Cobrar y entregar</button>
    </div>
  </div>
  <script>window.entregaMultipleUrl = <?= json_encode($base_url . "entrega/multiple") ?>; window.entregaCsrfToken = <?= json_encode($csrfToken) ?>;</script>

  <div class="grid-encomiendas">
    <?php foreach ($encomiendas as $encomienda): ?>
      <?php
      $foto = !empty($encomienda["foto"])
        ? $base_url . "assets/img/paquetes/" . rawurlencode(basename($encomienda["foto"]))
        : "";
      ?>
      <article class="tarjeta-encomienda" data-id="<?= (int) $encomienda["id"] ?>" data-destinatario="<?= $h($encomienda["destinatario"]) ?>" data-descripcion="<?= $h($encomienda["descripcion"] ?: "Sin descripción") ?>" data-codigo="<?= $h($encomienda["codigo"]) ?>" data-precio="<?= number_format((float) ($encomienda["precio"] ?? 2), 2, ".", "") ?>">
        <div class="encabezado-tarjeta">
          <div class="identidad-encomienda">
            <label class="selector-encomienda" aria-label="Seleccionar encomienda">
              <input type="checkbox" class="selectorEncomienda" value="<?= (int) $encomienda["id"] ?>" <?= $encomienda["estado"] === "Pendiente" ? "" : "disabled" ?>>
            </label>
            <div>
              <strong>👤 <?= $h($encomienda["destinatario"]) ?></strong>
            <small>📦 <?= $h($encomienda["descripcion"] ?: "Sin descripción") ?></small>
          </div>
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
          <div>
            🏷️ <b>Clasificación:</b>
            <?php if (mb_strtolower(trim((string) $encomienda["tipo_recepcion"]), "UTF-8") === "otro"): ?>
              <span class="valor-clasificacion"><?= $h($encomienda["clasificacion"]) ?></span>
            <?php else: ?>
              <?= $h($encomienda["clasificacion"]) ?>
            <?php endif; ?>
          </div>
          <div>🏢 <b>Cliente:</b> <?= $h($encomienda["nombre_cliente"]) ?></div>
          <div>📱 <b>Contacto:</b> <?= $h($encomienda["contacto"] ?: "Sin registrar") ?></div>
          <div>📦 <b>Recepción:</b> <?= $h($encomienda["tipo_recepcion"]) ?></div>
          <div>📅 <b>Fecha:</b> <?= $h(date("d/m/Y H:i", strtotime($encomienda["fecha_recepcion"]))) ?></div>
          <div>🟡 <b>Estado:</b> <?= $h($encomienda["estado"]) ?></div>
        </div>
        <div class="foto-tarjeta">
          <?= $foto ? '<img src="' . $h($foto) . '" alt="Foto de la encomienda">' : "📷 Foto pendiente" ?>
        </div>
        <div class="acciones-tarjeta">
          <a class="accion ver" href="<?= $base_url ?>encomiendas/ver?id=<?= (int) $encomienda["id"] ?>">👁 Ver</a>
          <a class="accion editar" href="<?= $base_url ?>encomiendas/editar?id=<?= (int) $encomienda["id"] ?>">✏ Editar</a>
          <form method="POST" action="<?= $base_url ?>encomiendas/eliminar" onsubmit="return confirm('¿Eliminar esta encomienda?');">
            <input type="hidden" name="csrf_token" value="<?= $h($csrfToken) ?>">
            <input type="hidden" name="id" value="<?= (int) $encomienda["id"] ?>">
            <button class="accion eliminar" type="submit">🗑 Eliminar</button>
          </form>
          <a class="accion foto" href="<?= $base_url ?>entrega/detalle?id=<?= (int) $encomienda["id"] ?>">📷 Foto</a>
        </div>
      </article>
    <?php endforeach; ?>
  </div>
  <?php if (empty($encomiendas)): ?><p class="sin-resultados">No se encontraron encomiendas.</p><?php endif; ?>
</div>
