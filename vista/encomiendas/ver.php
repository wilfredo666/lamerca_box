<?php $h = static fn($valor) => htmlspecialchars((string) ($valor ?? ""), ENT_QUOTES, "UTF-8"); ?>
<div class="busqueda-encomiendas">
  <?php if (!$encomienda): ?>
    <p class="sin-resultados">Encomienda no encontrada.</p>
  <?php else: ?>
    <div class="detalle-encomienda">
      <h1><?= $h($encomienda["codigo"]) ?></h1>
      <p><b>Destinatario:</b> <?= $h($encomienda["destinatario"]) ?></p>
      <p><b>Contacto:</b> <?= $h($encomienda["contacto"]) ?></p>
      <p><b>Cliente:</b> <?= $h($encomienda["nombre_cliente"]) ?></p>
      <p><b>Clasificación:</b> <?= $h($encomienda["clasificacion"]) ?></p>
      <p><b>Descripción:</b> <?= $h($encomienda["descripcion"]) ?></p>
      <p><b>Precio:</b> Bs <?= number_format((float) $encomienda["precio"], 2) ?></p>
      <p><b>Estado:</b> <?= $h($encomienda["estado"]) ?></p>
      <a class="accion editar" href="<?= $base_url ?>encomiendas/editar?id=<?= (int) $encomienda["id"] ?>">✏ Editar</a>
      <a class="accion ver" href="<?= $base_url ?>encomiendas/buscar">← Volver</a>
    </div>
  <?php endif; ?>
</div>
