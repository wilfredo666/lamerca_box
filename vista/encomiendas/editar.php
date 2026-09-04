<?php $h = static fn($valor) => htmlspecialchars((string) ($valor ?? ""), ENT_QUOTES, "UTF-8"); ?>
<div class="busqueda-encomiendas">
  <?php if (!empty($errorVista)): ?>
    <p class="sin-resultados"><?= $h($errorVista) ?></p>
  <?php else: ?>
    <form class="detalle-encomienda formulario-editar" method="POST">
      <h1>Editar <?= $h($encomienda["codigo"]) ?></h1>
      <input type="hidden" name="csrf_token" value="<?= $h($csrfToken) ?>">
      <label>Destinatario<input name="destinatario" required maxlength="150" value="<?= $h($encomienda["destinatario"]) ?>"></label>
      <label>Contacto<input name="contacto" maxlength="30" value="<?= $h($encomienda["contacto"]) ?>"></label>
      <label>Clasificación<input name="clasificacion" required maxlength="50" value="<?= $h($encomienda["clasificacion"]) ?>"></label>
      <label>Descripción<textarea name="descripcion" maxlength="5000"><?= $h($encomienda["descripcion"]) ?></textarea></label>
      <label>Precio<input type="number" name="precio" min="0" step="0.01" required value="<?= $h($encomienda["precio"]) ?>"></label>
      <label>Quién paga<select name="quien_paga"><option <?= $encomienda["quien_paga"] === "Destinatario" ? "selected" : "" ?>>Destinatario</option><option <?= $encomienda["quien_paga"] === "Remitente" ? "selected" : "" ?>>Remitente</option></select></label>
      <button class="accion editar" type="submit">Guardar cambios</button>
    </form>
  <?php endif; ?>
</div>
