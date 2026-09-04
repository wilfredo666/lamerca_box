<!DOCTYPE html>

<html lang="es">

<head>

<meta charset="UTF-8">

<title>Cajas TikTok</title>

<link rel="stylesheet" href="../../assets/css/modules/caja/listar.css">

</head>

<body>

<header>

📦 Cajas TikTok

</header>

<div class="contenedor">

<div class="card">

<input
type="text"
id="buscar"
placeholder="Buscar por TikTok, propietaria o celular...">

<table>

<tr>

<th>Código</th>

<th>TikTok</th>

<th>Propietaria</th>

<th>Celular</th>

<th>Acción</th>

</tr>

<?php while($fila=$res->fetch_assoc()){ ?>

<tr class="filaCaja">

<td><?= $fila["codigo"] ?></td>

<td><?= $fila["nombre_tiktok"] ?></td>

<td><?= $fila["propietaria"] ?></td>

<td><?= $fila["celular"] ?></td>

<td>

<a
class="btn"
href="ver.php?id=<?= $fila["id"] ?>"

Ver

</a>

</td>

</tr>

<?php } ?>

</table>

</div>

</div>

<script src="../../../assets/js/vista/modules/caja/listar.js"></script>

</body>

</html>