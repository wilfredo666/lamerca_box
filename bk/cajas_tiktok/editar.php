<?php if(isset($errorVista) || empty($caja)): ?>
<p><?= htmlspecialchars($errorVista ?? "Caja no encontrada.", ENT_QUOTES, "UTF-8") ?></p>
<?php else: ?>
<div class="card">

<h2>✏️ Editar Caja TikTok</h2>

<form method="POST">

<label>Nombre TikTok</label>

<input
name="nombre_tiktok"
value="<?= htmlspecialchars($caja["nombre_tiktok"]) ?>"
required
autofocus>


<label>Propietaria</label>

<input
name="propietaria"
value="<?= htmlspecialchars($caja["propietaria"]) ?>">


<label>WhatsApp</label>

<input
name="whatsapp"
value="<?= htmlspecialchars($caja["whatsapp"]) ?>">


<label>Nota</label>

<textarea
name="observaciones"
placeholder="Ejemplo: Estante verde, caja grande, etc."
><?= htmlspecialchars($caja["observaciones"]) ?></textarea>


<button type="submit">

💾 Guardar cambios

</button>

</form>

<a
href="<?= $base_url ?>recepcion/tiktok"
class="volver"
>
← Volver a Recepción TikTok
</a>

</div>


<script src="<?= $base_url ?>assets/js/modules/cajas_tiktok/editar.js"></script>
<?php endif; ?>