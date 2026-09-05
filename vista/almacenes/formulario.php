<?php
$h = function ($valor) { return htmlspecialchars((string) ($valor ?? ""), ENT_QUOTES, "UTF-8"); };
$editar = $modo === "editar";
?>
<section class="almacenes-contenedor">
  <div class="almacenes-encabezado">
    <div><h1><?= $editar ? "Editar almacén" : "Nuevo almacén" ?></h1><p>Complete los datos del almacén.</p></div>
    <a href="<?= $base_url ?>almacenes" class="btn btn-secondary">Volver</a>
  </div>
  <div class="card almacenes-card"><div class="card-body">
    <form method="POST">
      <input type="hidden" name="csrf_token" value="<?= $h($csrfToken) ?>">
      <div class="form-row">
        <div class="form-group col-md-6"><label>Nombre</label><input name="nombre" class="form-control" maxlength="50" required value="<?= $h($almacen["nombre_almacen"] ?? "") ?>"></div>
        <div class="form-group col-md-6"><label>Ciudad</label><input name="ciudad" class="form-control" maxlength="100" required value="<?= $h($almacen["ciudad"] ?? "") ?>"></div>
      </div>
      <div class="form-group"><label>Descripción</label><input name="descripcion" class="form-control" maxlength="100" value="<?= $h($almacen["descripcion"] ?? "") ?>"></div>
      <div class="form-group"><label>Dirección</label><input name="direccion" class="form-control" maxlength="100" value="<?= $h($almacen["direccion"] ?? "") ?>"></div>
      <div class="form-row">
        <div class="form-group col-md-6"><label>Encargado</label><input name="encargado" class="form-control" maxlength="50" value="<?= $h($almacen["encargado"] ?? "") ?>"></div>
        <div class="form-group col-md-6"><label>Contacto</label><input name="contacto" class="form-control" maxlength="50" value="<?= $h($almacen["contacto"] ?? "") ?>"></div>
      </div>
      <div class="form-group"><label>Estado</label><select name="estado" class="form-control"><option value="1" <?= (int) ($almacen["estado_almacen"] ?? 1) === 1 ? "selected" : "" ?>>Activo</option><option value="0" <?= (int) ($almacen["estado_almacen"] ?? 1) === 0 ? "selected" : "" ?>>Inactivo</option></select></div>
      <button type="submit" class="btn btn-success"><?= $editar ? "Actualizar" : "Guardar" ?></button>
    </form>
  </div></div>
</section>
