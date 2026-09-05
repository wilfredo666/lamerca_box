<?php
$base_url = $base_url ?? rtrim(dirname($_SERVER["SCRIPT_NAME"]), "/") . "/";
$nombreUsuario = $_SESSION["nombre"] ?? "Usuario";
$rutaActual = $_GET["ruta"] ?? "";
?>

<aside class="menu-lateral">
  <a class="menu-marca" href="<?= $base_url ?>inicio">
    <img class="menu-logo" src="<?= $base_url ?>assets/img/logo.jpg" alt="Logo La Merca Box">
    <span>La Merca Box</span>
  </a>

  <div class="menu-usuario">
    <img class="menu-usuario-imagen" src="<?= $base_url ?>assets/img/user.jpg" alt="Usuario">
    <span class="menu-usuario-nombre"><?= htmlspecialchars($nombreUsuario, ENT_QUOTES, "UTF-8") ?></span>
  </div>

  <nav class="menu-navegacion" aria-label="Navegación principal">
    <a class="menu-enlace <?= $rutaActual === "inicio" ? "menu-enlace-activo" : "" ?>" href="<?= $base_url ?>inicio">
      <i class="fas fa-home" aria-hidden="true"></i>
      <span>Inicio</span>
    </a>

    <a class="menu-enlace <?= $rutaActual === "clientes" ? "menu-enlace-activo" : "" ?>" href="<?= $base_url ?>clientes">
      <i class="fas fa-user-friends" aria-hidden="true"></i>
      <span>Clientes</span>
    </a>

    <a class="menu-enlace <?= $rutaActual === "almacenes" ? "menu-enlace-activo" : "" ?>" href="<?= $base_url ?>almacenes">
      <i class="fas fa-warehouse" aria-hidden="true"></i>
      <span>Almacenes</span>
    </a>

    <a class="menu-enlace <?= $rutaActual === "usuarios" ? "menu-enlace-activo" : "" ?>" href="<?= $base_url ?>usuarios">
      <i class="fas fa-users-cog" aria-hidden="true"></i>
      <span>Usuarios</span>
    </a>

    <a class="menu-enlace <?= $rutaActual === "traspasos" ? "menu-enlace-activo" : "" ?>" href="<?= $base_url ?>traspasos">
      <i class="fas fa-exchange-alt" aria-hidden="true"></i>
      <span>Traspasos</span>
    </a>

  </nav>

  <div class="menu-salir">
    <a class="menu-enlace" href="<?= $base_url ?>salir">
      <i class="fas fa-sign-out-alt" aria-hidden="true"></i>
      <span>Cerrar sesión</span>
    </a>
  </div>
</aside>
