<?php $h = static fn($valor) => htmlspecialchars((string) ($valor ?? ""), ENT_QUOTES, "UTF-8"); ?>
<div class="busqueda-encomiendas">
  <?php if (!empty($errorVista)): ?>
    <p class="sin-resultados"><?= $h($errorVista) ?></p>
  <?php else: ?>
    <div class="detalle-encomienda">
      <h1><?= $h($recepcion["codigo"]) ?></h1>
      <p><b>Cliente:</b> <?= $h($recepcion["nombre_cliente"]) ?></p>
      <p><b>Tipo:</b> <?= $h($recepcion["tipo_recepcion"]) ?></p>
      <p><b>Empresa:</b> <?= $h($recepcion["empresa"]) ?></p>
      <p><b>Fecha:</b> <?= $h($recepcion["fecha_registro"]) ?></p>
      <p><b>Estado:</b> <?= $h($recepcion["estado"]) ?></p>
      <hr>
      <?php foreach ($paquetes as $indice => $paquete): ?>
        <p><b><?= $indice + 1 ?>. <?= $h($paquete["codigo"]) ?></b> -
          <?= $h($paquete["destinatario"]) ?> -
          <?= $h($paquete["clasificacion"]) ?> -
          <?= $h($paquete["estado"]) ?></p>
      <?php endforeach; ?>
      <a class="accion editar" href="<?= $base_url ?>recepcion/caja-editar?id=<?= (int) $recepcion["id"] ?>">✏ Editar y agregar</a>
      <a class="accion ver" href="<?= $base_url ?>recepcion/cajas-buscar">← Volver</a>
    </div>
  <?php endif; ?>
</div>
