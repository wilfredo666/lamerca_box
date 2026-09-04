<!DOCTYPE html>

<html lang="es">

<head>

<meta charset="UTF-8">

<title>Nueva Caja TikTok</title>

<link rel="stylesheet" href="../../assets/css/modules/caja/nueva.css">

</head>

<body>

<header>

📦 Nueva Caja TikTok

</header>

<div class="contenedor">

<div class="card">

<form action="guardar.php" method="POST">

<label>Nombre del TikTok</label>

<input
type="text"
name="nombre_tiktok"
required
autocomplete="off">

<label>Nombre de la Propietaria</label>

<input
type="text"
name="propietaria"
autocomplete="off">

<label>Celular</label>

<input
type="text"
name="celular"
autocomplete="off">

<label>WhatsApp</label>

<input
type="text"
name="whatsapp"
autocomplete="off">

<label>Observaciones</label>

<textarea
name="observaciones"
rows="4"></textarea>

<button>

💾 Guardar Caja

</button>

</form>

</div>

</div>

</body>

</html>