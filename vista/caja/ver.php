<!DOCTYPE html>

<html lang="es">

<head>

<meta charset="UTF-8">

<title>Ficha Caja TikTok</title>

<link rel="stylesheet" href="../../assets/css/modules/caja/ver.css">

</head>

<body>

<header>

📦 Ficha Caja TikTok

</header>

<div class="contenedor">

<div class="card">

<h2 style="margin-top:0;color:#16a34a;">

<?= $caja["nombre_tiktok"] ?>

</h2>

<hr>

<div class="item">

<b>Código:</b>

<?= $caja["codigo"] ?>

</div>

<div class="item">

<b>Propietaria:</b>

<?= $caja["propietaria"]=="" ? "No registrado" : $caja["propietaria"] ?>

</div>

<div class="item">

<b>Celular:</b>

<?= $caja["celular"]=="" ? "No registrado" : $caja["celular"] ?>

</div>

<div class="item">

<b>WhatsApp:</b>

<?= $caja["whatsapp"]=="" ? "No registrado" : $caja["whatsapp"] ?>

</div>

<div class="item">

<b>Observaciones:</b>

<?= $caja["observaciones"]=="" ? "Sin observaciones" : $caja["observaciones"] ?>

</div>

<div class="item">

<b>Fecha de creación:</b>

<?= $caja["fecha_creacion"] ?>

</div>

<hr>

<h3 style="color:#16a34a;">

📊 Resumen

</h3>

<div class="item">

📦 Total de paquetes:

<b><?= $resumen["total"] ?></b>

</div>

<div class="item">

🟡 Pendientes:

<b><?= $resumen["pendientes"] ?></b>

</div>

<div class="item">

✅ Entregados:

<b><?= $resumen["entregados"] ?></b>

</div>

<div class="item">

💰 Total registrado:

<b>Bs <?= number_format($resumen["dinero"],2) ?></b>

</div>

<hr>

<h3 style="color:#16a34a;">

📦 Historial de Paquetes

</h3>

<hr>

<table style="
width:100%;
border-collapse:collapse;
margin-top:15px;
">

<tr style="background:#16a34a;color:white;">

<th style="padding:10px;">Cliente</th>

<th>Estado</th>

<th>Precio</th>

<th>Fecha</th>

<th></th>

</tr>

<?php

while($p = $resPaquetes->fetch_assoc()){

?>

<tr>

<td style="padding:10px;">

<?= $p["cliente"] ?>

</td>

<td>

<?= $p["estado"] ?>

</td>

<td>

Bs <?= number_format($p["precio"],2) ?>

</td>

<td>

<?= date("d/m/Y",strtotime($p["fecha_registro"])) ?>

</td>

<td>

<a
href="../entregas/detalle_entrega.php?id=<?= $p["id"] ?>">

Ver

</a>

</td>

</tr>

<?php

}

?>

</table>

<h3 style="color:#16a34a;">

📦 Paquetes de esta Caja

</h3>

<table style="width:100%;border-collapse:collapse;">

<tr style="background:#16a34a;color:white;">

<th style="padding:12px;">Cliente</th>

<th>Estado</th>

<th>Ingreso</th>

<th></th>

</tr>

<?php

while($p = $resPaquetes->fetch_assoc()){

?>

<tr>

<td style="padding:12px;">

<?= $p["cliente"] ?>

</td>

<td>

<?= $p["estado"] ?>

</td>

<td>

<?= date("d/m/Y",strtotime($p["fecha_registro"])) ?>

</td>

<td>

<a
href="../entregas/detalle_entrega.php?id=<?= $p["id"] ?>">

Ver

</a>

</td>

</tr>

<?php

}

?>

</table>

<button class="boton">

✏ Editar Caja

</button>

</div>

</div>

</body>

</html>