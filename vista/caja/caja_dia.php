<!DOCTYPE html>

<html lang="es">

<head>

<meta charset="UTF-8">

<title>Caja del Día</title>

<link rel="stylesheet" href="../../assets/css/modules/caja/caja_dia.css">

</head>

<body>

<header>

💰 Caja del Día

</header>

<div class="contenedor">

<div class="card">

<table>

<tr>

<th>Hora</th>

<th>Cliente</th>

<th>Empresa</th>

<th>Monto</th>

</tr>

<?php

$total = 0;

while($fila = $res->fetch_assoc()){

    $total += $fila["precio"];

?>

<tr>

<td>

<?= date("H:i",strtotime($fila["fecha_cobro"])) ?>

</td>

<td>

<?= $fila["cliente"] ?>

</td>

<td>

<?= $fila["empresa"] ?>

</td>

<td>

Bs <?= number_format($fila["precio"],2) ?>

</td>

</tr>

<?php

}

?>

<tr>

<th colspan="3">

TOTAL

</th>

<th>

Bs <?= number_format($total,2) ?>

</th>

</tr>

</table>

</div>

</div>

</body>

</html>