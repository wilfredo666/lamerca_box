<?php $h = static fn($valor) => htmlspecialchars((string) ($valor ?? ""), ENT_QUOTES, "UTF-8"); ?>

<div class="row">
  <div class="col-6">
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
          <?php if (!empty($_GET["error"])): ?>
            <div class="alert alert-warning" role="alert"><?= $h($_GET["error"]) ?></div>
          <?php endif; ?>


          <div class="row">
            <div class="col-6">
              <button
                type="button"
                class="accion whatsapp btnWhatsapp"
                data-whatsapp="<?= $h($numeroWhatsapp ?? "") ?>"
                data-mensaje="<?= $h($mensajeWhatsapp ?? "") ?>">
                📲 Compartir por WhatsApp
              </button>
            </div>
            <div class="col-6">
              <a class="accion editar" href="<?= $base_url ?>recepcion/caja-editar?id=<?= (int) $recepcion["id"] ?>">✏ Editar y agregar</a>
              <a class="accion ver" href="<?= $base_url ?>recepcion/cajas-buscar">← Volver</a>
            </div>
          </div>

        </div>
      <?php endif; ?>
    </div>

  </div>

  <div class="col-6">
    <!-- Columna de imagen -->
    <div class="busqueda-encomiendas text-center">
      
        <?php if (!empty($recepcion["foto"])): ?>
          <img src="<?= $h($base_url . "assets/img/recepciones/" . rawurlencode(basename($recepcion["foto"]))) ?>" alt="Foto de la caja" class="rounded mb-2" style="max-width:100%; max-height:360px; object-fit:contain;">
        <?php else: ?>
          <div class="foto-tarjeta">📷 Foto pendiente</div>
        <?php endif; ?>
        <br>
        <form method="POST" action="<?= $base_url ?>recepcion/caja-foto" enctype="multipart/form-data" class="formulario-foto" style="width:50%; margin:auto;">
          <input type="hidden" name="csrf_token" value="<?= $h($csrfToken) ?>">
          <input type="hidden" name="id" value="<?= (int) $recepcion["id"] ?>">
          <input type="hidden" name="retorno" value="detalle">
          <label class="accion foto">
            📷 <?= !empty($recepcion["foto"]) ? "Cambiar foto" : "Subir foto" ?>
            <input type="file" name="foto" accept="image/jpeg,image/png,image/gif,image/webp" capture="environment" required onchange="this.form.submit()">
          </label>
        </form>
      
    </div>
  </div>
</div>