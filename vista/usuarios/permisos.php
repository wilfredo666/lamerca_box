<?php
$h = function ($valor) { return htmlspecialchars((string) ($valor ?? ""), ENT_QUOTES, "UTF-8"); };
$permisosAsignados = array_flip($permisosAsignados);
?>
<section class="permisos-contenedor">
  <div class="permisos-encabezado">
    <div>
      <h1>Permisos de usuario</h1>
      <p>Administre los accesos de <?= $h($usuario["nombre"]) ?>.</p>
    </div>
    <a href="<?= $base_url ?>usuarios" class="btn btn-secondary">Volver a usuarios</a>
  </div>

  <form method="POST" class="card permisos-card">
    <input type="hidden" name="csrf_token" value="<?= $h($csrfToken) ?>">
    <div class="permisos-titulo">Permisos habilitados para: <?= $h($usuario["nombre"]) ?></div>
    <div class="card-body">
      <div class="permisos-lista">
        <?php foreach ($permisos as $permiso): ?>
          <?php $idPermiso = (int) $permiso["id_permiso"]; ?>
          <label class="permiso-opcion">
            <input type="checkbox" name="permisos[]" value="<?= $idPermiso ?>" <?= isset($permisosAsignados[$idPermiso]) ? "checked" : "" ?>>
            <span><?= $h($permiso["desc_permiso"]) ?></span>
          </label>
        <?php endforeach; ?>
      </div>
    </div>
    <div class="card-footer permisos-pie">
      <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Guardar permisos</button>
    </div>
  </form>
</section>
