<?php
$h = static fn($valor) => htmlspecialchars((string) ($valor ?? ""), ENT_QUOTES, "UTF-8");
?>
<div class="busqueda-encomiendas">
  <div class="encabezado-busqueda">
    <h1>Cajas recibidas <span><?= count($cajas) ?></span></h1>
    <form method="GET" action="<?= $base_url ?>recepcion/cajas-buscar">
      <input type="hidden" name="ruta" value="recepcion/cajas-buscar">
      <input type="search" name="buscar" value="<?= $h($buscar) ?>" placeholder="🔎 Buscar por cliente, empresa, código o tipo..." autofocus>
    </form>
  </div>

  <div class="grid-encomiendas">
    <?php foreach ($cajas as $caja): ?>
      <article class="tarjeta-encomienda">
        <div class="encabezado-tarjeta">
          <div>
            <strong>📦 <?= $h($caja["nombre_cliente"]) ?></strong>
            <small><?= $h($caja["empresa"] ?: "Recepción general") ?></small>
          </div>
          <b><?= $h($caja["codigo"]) ?></b>
        </div>
        <div class="datos-tarjeta">
          <div>📦 <b>Tipo:</b> <?= $h($caja["tipo_recepcion"]) ?></div>
          <div>📱 <b>Celular:</b> <?= $h($caja["celular_cliente"] ?: "Sin registrar") ?></div>
          <div>📅 <b>Recepción:</b> <?= $h(date("d/m/Y H:i", strtotime($caja["fecha_registro"]))) ?></div>
          <div>📦 <b>Encomiendas:</b> <?= (int) $caja["total_encomiendas"] ?></div>
          <div>🟡 <b>Pendientes:</b> <?= (int) $caja["pendientes"] ?></div>
          <div>🔵 <b>Estado:</b> <?= $h($caja["estado"]) ?></div>
        </div>
        <div class="foto-tarjeta">
          <?= !empty($caja["foto"]) ? '<img src="' . $h($base_url . "assets/img/recepciones/" . rawurlencode(basename($caja["foto"]))) . '" alt="Foto de la caja">' : "📷 Foto pendiente" ?>
        </div>
        <div class="acciones-tarjeta">
          <a class="accion ver" href="<?= $base_url ?>recepcion/caja-ver?id=<?= (int) $caja["id"] ?>">👁 Ver</a>
          <a class="accion editar" href="<?= $base_url ?>recepcion/caja-editar?id=<?= (int) $caja["id"] ?>">✏ Editar</a>
          <form method="POST" action="<?= $base_url ?>recepcion/caja-eliminar" onsubmit="return confirm('¿Eliminar esta caja y sus encomiendas?');">
            <input type="hidden" name="csrf_token" value="<?= $h($csrfToken) ?>">
            <input type="hidden" name="id" value="<?= (int) $caja["id"] ?>">
            <button class="accion eliminar" type="submit">🗑 Eliminar</button>
          </form>
          <a class="accion foto" href="<?= $base_url ?>recepcion/caja-editar?id=<?= (int) $caja["id"] ?>#foto">📷 Foto</a>
        </div>
      </article>
    <?php endforeach; ?>
  </div>
  <?php if (empty($cajas)): ?><p class="sin-resultados">No se encontraron cajas TikTok o cajas generales.</p><?php endif; ?>
</div>
