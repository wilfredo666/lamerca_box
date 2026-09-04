<!DOCTYPE html>
<html lang="es">

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Nueva Encomienda</title>

<link rel="stylesheet" href="../../assets/css/modules/recepcion/nueva_encomienda.css">

</head>

<body>

<header>

📦 Nueva Encomienda

</header>

<div class="contenedor">

<div class="card">

<form method="POST">

<label>Nombre del Cliente</label>

<input type="text" name="cliente" placeholder="Ejemplo: Karen Jasmyl Claros" required>

<label>Celular</label>

<input type="text" name="celular">

<label>Precio</label>

<select name="precio">

<option value="2">2 Bs</option>

<option value="3">3 Bs</option>

<option value="5">5 Bs</option>

</select>

<label>Paga</label>

<select name="pagado_por">

<option value="Cliente">Cliente</option>

<option value="Vendedor">Vendedor</option>

</select>

<label>Observaciones</label>

<textarea name="observaciones" rows="4"></textarea>

<button>

Guardar Encomienda

</button>

</form>

</div>

</div>

</body>

</html>