<?php $h = fn($valor) => htmlspecialchars((string) $valor, ENT_QUOTES, "UTF-8"); ?>
<div class="entrega-encabezado">📸 Fotos Pendientes</div>
<div class="contenedor"><div class="cardPrincipal">
  <?php if (!empty($_GET["mensaje"])) { ?><p class="alerta"><?= $h($_GET["mensaje"]) ?></p><?php } ?>
  <div class="titulo">📸 Fotografías pendientes <span class="contador"><?= (int) $totalPendientes ?></span></div>
  <?php if ($totalPendientes > 0): ?><input type="text" id="buscar" placeholder="🔎 Buscar por nombre o celular..."><div id="resultados">
  <?php foreach ($paquetes as $paquete):
    $tipo = trim((string) $paquete["tipo"]);
    $etiqueta = strtolower($tipo) === "alfabeto" ? mb_strtoupper(mb_substr(trim($paquete["cliente"]), 0, 1, "UTF-8"), "UTF-8") : (strtolower($tipo) === "tiktok" ? ($paquete["codigo"] ?? "") : mb_strtoupper($tipo, "UTF-8"));
  ?><div class="paquete" data-id="<?= (int) $paquete["id"] ?>"><div><div class="filaSuperior"><div><div class="nombreCliente">👤 <?= $h($paquete["cliente"]) ?></div><div class="detalle">📦 <?= $h($paquete["observaciones"]) ?></div></div><div class="codigoCaja"><?= $h($etiqueta) ?></div></div><div class="contenidoPaquete"><div class="datosExtra">🏷️ <b>Tipo:</b> <?= $h($tipo) ?><?php if (strtolower($tipo) === "tiktok"): ?><br>🏢 <b>Empresa:</b> <?= $h($paquete["empresa"]) ?><?php if (!empty($paquete["nota_caja"])): ?><br>📝 <b>Nota:</b> <?= $h($paquete["nota_caja"]) ?><?php endif; ?><?php endif; ?><br>📱 <b>Celular:</b> <?= $h($paquete["celular"] ?: "Sin registrar") ?><br>📅 <b>Recepción:</b> <?= date("d/m/Y H:i", strtotime($paquete["fecha_registro"])) ?><?php if (date("Y-m-d", strtotime($paquete["fecha_registro"])) === date("Y-m-d")): ?><br><span class="hoy">HOY</span><?php endif; ?><br>🟡 <b>Estado:</b> Pendiente</div><div class="fotoPendiente">📷 Foto pendiente</div></div></div><form method="POST" action="<?= $base_url ?>entrega/foto" enctype="multipart/form-data"><input type="hidden" name="csrf_token" value="<?= $h($csrfToken) ?>"><input type="hidden" name="id" value="<?= (int) $paquete["id"] ?>"><label class="botonFoto">📷 SACAR FOTO<input type="file" name="foto" class="inputFoto" accept="image/jpeg,image/png,image/gif,image/webp" capture="environment" onchange="this.form.submit()"></label></form></div>
  <?php endforeach; ?></div>
  <?php else: ?><div class="sin-pendientes">✅ ¡No tienes fotografías pendientes!<div>Todos los paquetes tienen su fotografía.</div></div><?php endif; ?>
</div></div>
<script src="<?= $base_url ?>assets/js/modules/entrega/fotos_pendientes.js"></script>
