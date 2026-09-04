<?php if(isset($errorVista)): ?>
<p><?= htmlspecialchars($errorVista, ENT_QUOTES, "UTF-8") ?></p>
<?php endif; ?>

<div class="contenedor">

<div class="card">

<div class="layoutRecepcion">

<div class="columnaIzquierda">

<form method="POST" id="formRecepcion">

<label for="buscarCajaTikTok">Buscar Caja TikTok</label>

<input
    type="text"
    id="buscarCajaTikTok"
    autocomplete="off"
    placeholder="Escriba nombre, propietaria, código o WhatsApp"
    aria-controls="resultadosCajasTikTok"
    aria-expanded="false"
>
<input type="hidden" name="caja_cliente_id" id="cajaCliente" required>

<div id="cajaSeleccionada" class="caja-seleccionada" hidden></div>

<div id="resultadosCajasTikTok" class="resultados-cajas" role="listbox" aria-label="Resultados de cajas TikTok" hidden>
    <?php foreach($cajasTikTok as $cajaTikTok): ?>
        <button
            type="button"
            class="resultado-caja"
            role="option"
            data-id="<?= $cajaTikTok["id"] ?>"
            data-nombre="<?= htmlspecialchars($cajaTikTok["nombre_tiktok"], ENT_QUOTES, "UTF-8") ?>"
            data-propietaria="<?= htmlspecialchars($cajaTikTok["propietaria"] ?? "", ENT_QUOTES, "UTF-8") ?>"
            data-whatsapp="<?= htmlspecialchars($cajaTikTok["whatsapp"] ?? "", ENT_QUOTES, "UTF-8") ?>"
            data-codigo="<?= htmlspecialchars($cajaTikTok["codigo"] ?? "", ENT_QUOTES, "UTF-8") ?>"
            data-observaciones="<?= htmlspecialchars($cajaTikTok["observaciones"] ?? "", ENT_QUOTES, "UTF-8") ?>"
            data-total-historico="<?= (int) $cajaTikTok["total_historico"] ?>"
            data-pendientes="<?= (int) $cajaTikTok["pendientes"] ?>"
            data-url-editar="<?= htmlspecialchars($base_url, ENT_QUOTES, "UTF-8") ?>cajas-tiktok/editar?id=<?= $cajaTikTok["id"] ?>"
            <?= $nuevaCajaId === (int) $cajaTikTok["id"] ? "data-nueva-caja=\"true\"" : "" ?>
        >
            <strong>📦 <?= htmlspecialchars($cajaTikTok["nombre_tiktok"], ENT_QUOTES, "UTF-8") ?></strong>
            <span>👩 <?= htmlspecialchars($cajaTikTok["propietaria"] ?: "Sin propietaria", ENT_QUOTES, "UTF-8") ?></span>
            <span>📱 <?= htmlspecialchars($cajaTikTok["whatsapp"] ?: "Sin WhatsApp", ENT_QUOTES, "UTF-8") ?></span>
        </button>
    <?php endforeach; ?>
</div>

<a class="btnNuevaCajaTikTok" href="<?= htmlspecialchars($base_url, ENT_QUOTES, "UTF-8") ?>cajas-tiktok/nueva">
    ➕ Nueva Caja TikTok
</a>

</div>

<div class="columnaDerecha">

<div class="info resumenRecepcion">

    <div>
        <b>Fecha:</b><br>
        <?=date("d/m/Y")?>
    </div>

    <div>
        <b>Hora:</b><br>
        <?=date("H:i")?>
    </div>

    <div>
        <b>Paquetes registrados:</b><br>
        <span id="contadorPaquetes">1</span>
    </div>

</div>

<table>

<tr>

<th>N°</th>

<th>Cliente</th>

<th>Celular</th>

<th>Detalle</th>

<th>Precio</th>

<th>Paga</th>

</tr>

<tbody id="tablaPaquetes">

<tr>

<td>1</td>

<td>
<input
type="text"
name="cliente[]"
class="cliente"
onkeydown="enterCliente(event,this)">
</td>

<td>
<input
type="text"
name="celular[]"
class="celular"
onkeydown="enterCelular(event,this)">
</td>

<td>
<input
type="text"
name="detalle[]"
class="detalle"
onkeydown="detectarEnter(event,this)">
</td>

<td>

<input
    type="number"
    name="precio_base[]"
    value="2"
    min="0"
    step="0.50"
    class="precioBase"
>

</td>

<td>

<select name="pagado_por[]">

    <option value="Cliente">Cliente</option>

    <option value="Vendedor">Vendedor</option>

</select>

</td>

</tr>

</tbody>

</table>

<button>

Guardar Caja

</button>

</form>

</div>

</div>

</div>

<script src="<?= $base_url ?>assets/js/modules/recepcion/recepcion_tiktok.js"></script>