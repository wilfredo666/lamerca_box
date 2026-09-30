<?php
$base_url = $base_url ?? rtrim(dirname($_SERVER["SCRIPT_NAME"]), "/") . "/";
$nombreUsuario = $_SESSION["nombre"] ?? "Usuario";
$nombreAlmacen = $_SESSION["nomAlmacen"] ?? "Almacén no seleccionado";
$rutaActual = $_GET["ruta"] ?? "";
?>

<aside class="menu-lateral">
  <a class="menu-marca" href="<?= $base_url ?>inicio">
    <img class="menu-logo" src="<?= $base_url ?>assets/img/logo.jpg" alt="Logo La Merca Box">
    <span>La Merca Box</span>
  </a>

  <div class="menu-usuario">
    <img class="menu-usuario-imagen" src="<?= $base_url ?>assets/img/user.jpg" alt="Usuario">
    <div class="menu-usuario-datos">
      <span class="menu-usuario-nombre"><?= htmlspecialchars($nombreUsuario, ENT_QUOTES, "UTF-8") ?></span>
      <span class="menu-almacen-activo" title="<?= htmlspecialchars($nombreAlmacen, ENT_QUOTES, "UTF-8") ?>">
        <i class="fas fa-warehouse" aria-hidden="true"></i>
        <?= htmlspecialchars($nombreAlmacen, ENT_QUOTES, "UTF-8") ?>
      </span>
    </div>
  </div>

  <nav class="menu-navegacion" aria-label="Navegación principal">
    <a class="menu-enlace <?= $rutaActual === "inicio" ? "menu-enlace-activo" : "" ?>" href="<?= $base_url ?>inicio">
      <i class="fas fa-home" aria-hidden="true"></i>
      <span>Inicio</span>
    </a>
    <?php
    if (ControladorUsuario::ctrUsuarioPermiso($_SESSION["idUsuario"], 4)) {
    ?>
      <a class="menu-enlace <?= $rutaActual === "clientes" ? "menu-enlace-activo" : "" ?>" href="<?= $base_url ?>clientes">
        <i class="fas fa-user-friends" aria-hidden="true"></i>
        <span>Clientes</span>
      </a>
    <?php
    }
    ?>

    <?php
    if (ControladorUsuario::ctrUsuarioPermiso($_SESSION["idUsuario"], 15)) {
    ?>
      <a class="menu-enlace <?= $rutaActual === "caja" ? "menu-enlace-activo" : "" ?>" href="<?= $base_url ?>caja">
        <i class="fas fa-cash-register" aria-hidden="true"></i>
        <span>Caja</span>
      </a>
    <?php
    }
    ?>

    <a class="menu-enlace <?= $rutaActual === "recepcion/general" ? "menu-enlace-activo" : "" ?>" href="<?= $base_url ?? '' ?>recepcion/general">
        <i class="fas fa-box" aria-hidden="true"></i>
        <span>Nueva Recepción</span>
      </a>

      <a class="menu-enlace <?= $rutaActual === "encomiendas/buscar" ? "menu-enlace-activo" : "" ?>" href="<?= $base_url ?? '' ?>encomiendas/buscar">
        <i class="fas fa-search" aria-hidden="true"></i>
        <span>Buscar Encomienda</span>
      </a>
  </nav>

  <div class="menu-salir">
    <a class="menu-enlace" href="<?= $base_url ?>salir">
      <i class="fas fa-sign-out-alt" aria-hidden="true"></i>
      <span>Cerrar sesión</span>
    </a>
  </div>
</aside>