<div class="contenedor">

<div style="margin-bottom:20px;">

<input
type="text"
id="buscar"
placeholder="Buscar por código o empresa..."
style="
width:100%;
padding:15px;
font-size:18px;
border-radius:10px;
border:1px solid #ccc;
">

</div>

<table id="tablaCajas">

<tr>

<th>Código</th>

<th>Empresa</th>

<th>Fecha</th>

<th>Paquetes</th>

<th>Estado</th>

<th></th>

</tr>

<?php

foreach($cajas as $caja){

?>

<tr>

<td>

<a
class="codigoCaja"
href="<?= $base_url ?>recepcion/comprobante?id=<?= $caja["id"] ?>">

<?= $caja["codigo"] ?>

</a>

</td>

<td><?= $caja["empresa"] ?></td>

<td><?= $caja["fecha"] ?></td>

<td><?= $caja["total_paquetes"] ?></td>

<td>

🟢 <?= $caja["entregados"] ?>

&nbsp;&nbsp;

🟡 <?= $caja["pendientes"] ?>

</td>

<td>

<a
class="btnAbrir"
href="<?= $base_url ?>recepcion/comprobante?id=<?= $caja["id"] ?>">

👁 Ver

</a>

</td>

</tr>

<?php

}

?>

</table>

</div>

<script src="<?= $base_url ?>assets/js/modules/recepcion/historial_cajas.js"></script>