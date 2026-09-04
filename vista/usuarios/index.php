<?php $h = fn($v) => htmlspecialchars((string) ($v ?? ""), ENT_QUOTES, "UTF-8"); ?>
<section class="usuarios-contenedor">
  <div class="usuarios-encabezado">
    <div><h1>Usuarios</h1><p>Administra los usuarios del sistema.</p></div>
    <button class="btn btn-success" data-toggle="modal" data-target="#modalUsuario"><i class="fas fa-plus"></i> Nuevo usuario</button>
  </div>
  <div class="card usuarios-card"><div class="card-body"><div class="table-responsive">
    <table id="tablaUsuarios" class="table table-striped table-bordered">
      <thead><tr><th>Nombre</th><th>Email</th><th>Categoría</th><th>Estado</th><th>Acciones</th></tr></thead>
      <tbody><?php foreach ($usuarios as $usuario): ?>
        <tr>
          <td><?= $h($usuario["nombre"]) ?></td><td><?= $h($usuario["email"]) ?></td><td><?= $h($usuario["categoria"]) ?></td>
          <td><span class="badge badge-<?= (int) $usuario["estado_usuario"] ? "success" : "secondary" ?>"><?= (int) $usuario["estado_usuario"] ? "Activo" : "Inactivo" ?></span></td>
          <td class="usuarios-acciones">
            <button class="btn btn-sm btn-primary btn-editar-usuario" data-toggle="modal" data-target="#modalUsuario"
              data-id="<?= (int) $usuario["id_usuario"] ?>" data-nombre="<?= $h($usuario["nombre"]) ?>"
              data-email="<?= $h($usuario["email"]) ?>" data-categoria="<?= $h($usuario["categoria"]) ?>"
              data-estado="<?= (int) $usuario["estado_usuario"] ?>"><i class="fas fa-edit"></i></button>
            <form method="POST" action="<?= $base_url ?>usuarios/eliminar" class="form-eliminar-usuario">
              <input type="hidden" name="csrf_token" value="<?= $h($csrfToken) ?>"><input type="hidden" name="id" value="<?= (int) $usuario["id_usuario"] ?>">
              <button class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?></tbody>
    </table>
  </div></div></div>
</section>
<script>window.usuariosBaseUrl = <?= json_encode($base_url) ?>;</script>
<div class="modal fade" id="modalUsuario" tabindex="-1" aria-hidden="true"><div class="modal-dialog">
  <form method="POST" class="modal-content" id="formUsuario">
    <input type="hidden" name="csrf_token" value="<?= $h($csrfToken) ?>">
    <div class="modal-header"><h5 class="modal-title" id="tituloModalUsuario">Nuevo usuario</h5><button type="button" class="close" data-dismiss="modal">&times;</button></div>
    <div class="modal-body">
      <div class="form-group"><label>Nombre</label><input id="nombreUsuario" name="nombre" class="form-control" maxlength="255" required></div>
      <div class="form-group"><label>Email</label><input id="emailUsuario" type="email" name="email" class="form-control" maxlength="255" required></div>
      <div class="form-group"><label>Contraseña <small>(mínimo 6 caracteres; vacía para conservarla al editar)</small></label><input id="passwordUsuario" type="password" name="password" class="form-control" minlength="6"></div>
      <div class="form-group"><label>Categoría</label><input id="categoriaUsuario" name="categoria" class="form-control" maxlength="50" required></div>
      <div class="form-group mb-0"><label>Estado</label><select id="estadoUsuario" name="estado" class="form-control"><option value="1">Activo</option><option value="0">Inactivo</option></select></div>
    </div>
    <div class="modal-footer"><button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button><button class="btn btn-success">Guardar</button></div>
  </form>
</div></div>
