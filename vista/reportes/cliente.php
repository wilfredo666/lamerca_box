<!DOCTYPE html>

<html lang="es">

<head>

<meta charset="UTF-8">

<title>Ficha del Cliente</title>

<link rel="stylesheet" href="../../assets/css/modules/reportes/cliente.css">

</head>

<body>

<header>

👤 Ficha del Cliente

</header>

<div class="contenedor">

<div class="card">

<input
type="text"
id="buscar"
placeholder="Nombre o celular..."
autocomplete="off">

<?php

echo "<hr>";

echo "<h2>Resultados para: ".$buscar."</h2>";

$sql = "SELECT
encomiendas.*,
cajas_tiktok.codigo
FROM encomiendas
LEFT JOIN cajas_tiktok
ON encomiendas.caja_id = cajas_tiktok.id
ORDER BY fecha_registro DESC";

$res = $conn->query($sql);

$total = 0;
$pendientes = 0;
$entregados = 0;
$totalCobrado = 0;
$ultimaFecha = "";

while($fila = $res->fetch_assoc()){

$total++;

if($fila["estado"]=="Pendiente"){

    $pendientes++;

}else{

    $entregados++;

}

$totalCobrado += $fila["precio"];

if($ultimaFecha==""){

    $ultimaFecha = $fila["fecha_registro"];

}

?>

<div class="cliente-card" style="
margin-top:20px;
padding:20px;
border:1px solid #ddd;
border-radius:12px;
background:#fafafa;
">

<h3 style="margin:0;color:#16a34a;">

👤 <?= $fila["cliente"] ?>

</h3>

<p>

📱 <?= $fila["celular"]=="" ? "Sin registrar" : $fila["celular"] ?>

</p>

<p>

🏢 <?= $fila["empresa"] ?>

</p>

<p>

📦 Caja:

<?= $fila["codigo"] ?>

</p>

<p>

📝 <?= $fila["observaciones"] ?>

</p>

<p>

Estado:

<?= $fila["estado"] ?>

</p>

</div>

<?php
}
?>

</div>

</div>

<script src="../../../assets/js/vista/modules/reportes/cliente.js"></script>

</body>

</html>