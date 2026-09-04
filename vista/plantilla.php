<?php
session_start();

$rutas_validas = [
  "inicio" => "inicio.php",
  "salir" => "salir.php",
  "recepcion" => ["vista" => "recepcion/recepcion.php", "accion" => "ctrVistaRecepcion"],
  "recepcion/nueva" => "recepcion/nueva_encomienda.php",
  "recepcion/tiktok" => ["vista" => "recepcion/recepcion_tiktok.php", "accion" => "ctrVistaTikTok"],
  "recepcion/general" => ["vista" => "recepcion/recepcion_general.php", "accion" => "ctrVistaGeneral"],
  "recepcion/historial" => ["vista" => "recepcion/historial_cajas.php", "accion" => "ctrVistaHistorial"],
  "recepcion/comprobante" => ["vista" => "recepcion/comprobante_caja.php", "accion" => "ctrVistaComprobante"],
  "recepcion/comprobante-general" => ["vista" => "recepcion/comprobante_caja_respaldo.php", "accion" => "ctrVistaComprobanteGeneral"],
  "recepcion/cajas-buscar" => ["vista" => "recepcion/cajas_buscar.php", "accion" => "ctrBuscarCajas"],
  "recepcion/caja-ver" => ["vista" => "recepcion/caja_ver.php", "accion" => "ctrVerCaja"],
  "recepcion/caja-editar" => ["vista" => "recepcion/caja_editar.php", "accion" => "ctrEditarCaja"],
  "recepcion/caja-eliminar" => ["vista" => "recepcion/cajas_buscar.php", "accion" => "ctrEliminarCaja"],
  "encomiendas/buscar" => ["vista" => "encomiendas/buscar.php", "controlador" => "ControladorEncomiendas", "accion" => "ctrBuscar"],
  "encomiendas/ver" => ["vista" => "encomiendas/ver.php", "controlador" => "ControladorEncomiendas", "accion" => "ctrVer"],
  "encomiendas/editar" => ["vista" => "encomiendas/editar.php", "controlador" => "ControladorEncomiendas", "accion" => "ctrEditar"],
  "encomiendas/eliminar" => ["vista" => "encomiendas/buscar.php", "controlador" => "ControladorEncomiendas", "accion" => "ctrEliminar"],
  "entrega" => ["vista" => "entrega/entregar.php", "controlador" => "ControladorEntrega", "accion" => "ctrVistaEntrega"],
  "entrega/entregadas" => ["vista" => "entrega/entregadas.php", "controlador" => "ControladorEntrega", "accion" => "ctrVistaEntregadas"],
  "entrega/retirados" => ["vista" => "entrega/retirados.php", "controlador" => "ControladorEntrega", "accion" => "ctrVistaRetirados"],
  "entrega/fotos-pendientes" => ["vista" => "entrega/fotos_pendientes.php", "controlador" => "ControladorEntrega", "accion" => "ctrVistaFotosPendientes"],
  "entrega/detalle" => ["vista" => "entrega/detalle_entrega.php", "controlador" => "ControladorEntrega", "accion" => "ctrVistaDetalle"],
  "entrega/cobrar" => ["vista" => "entrega/detalle_entrega.php", "controlador" => "ControladorEntrega", "accion" => "ctrCobrarEntregar"],
  "entrega/retirar" => ["vista" => "entrega/detalle_entrega.php", "controlador" => "ControladorEntrega", "accion" => "ctrRetirar"],
  "entrega/foto" => ["vista" => "entrega/fotos_pendientes.php", "controlador" => "ControladorEntrega", "accion" => "ctrSubirFoto"],
  "entrega/multiple" => ["vista" => "entrega/entregar.php", "controlador" => "ControladorEntrega", "accion" => "ctrEntregaMultiple"],
  "caja" => "caja/listar.php",
  "caja/nueva" => "caja/nueva.php",
  "caja/dia" => "caja/caja_dia.php",
  "caja/detalle" => "caja/ver.php",
  "cajas-tiktok/nueva" => ["vista" => "cajas_tiktok/nuevo.php", "controlador" => "ControladorCajasTikTok", "accion" => "ctrNueva"],
  "cajas-tiktok/editar" => ["vista" => "cajas_tiktok/editar.php", "controlador" => "ControladorCajasTikTok", "accion" => "ctrEditar"],
  "clientes" => ["vista" => "clientes/index.php", "controlador" => "ControladorCliente", "accion" => "ctrVistaClientes"],
  "almacenes" => ["vista" => "almacenes/index.php", "controlador" => "ControladorAlmacen", "accion" => "ctrVistaAlmacenes"],
  "usuarios" => ["vista" => "usuarios/index.php", "controlador" => "ControladorUsuario", "accion" => "ctrVistaUsuarios"],
  "usuarios/nuevo" => ["vista" => "usuarios/index.php", "controlador" => "ControladorUsuario", "accion" => "ctrNuevo"],
  "usuarios/editar" => ["vista" => "usuarios/index.php", "controlador" => "ControladorUsuario", "accion" => "ctrEditar"],
  "usuarios/eliminar" => ["vista" => "usuarios/index.php", "controlador" => "ControladorUsuario", "accion" => "ctrEliminar"],
  "almacenes/nuevo" => ["vista" => "almacenes/formulario.php", "controlador" => "ControladorAlmacen", "accion" => "ctrNuevo"],
  "almacenes/editar" => ["vista" => "almacenes/formulario.php", "controlador" => "ControladorAlmacen", "accion" => "ctrEditar"],
  "almacenes/eliminar" => ["vista" => "almacenes/index.php", "controlador" => "ControladorAlmacen", "accion" => "ctrEliminar"],
  "reportes/cliente" => "reportes/cliente.php"
];

$estilos_vista = [
  "recepcion" => "assets/css/modules/recepcion/index.css",
  "recepcion/historial" => "assets/css/modules/recepcion/historial_cajas.css",
  "recepcion/comprobante" => "assets/css/modules/recepcion/comprobante_caja.css",
  "recepcion/comprobante-general" => "assets/css/modules/recepcion/comprobante_caja_respaldo.css",
  "recepcion/tiktok" => "assets/css/modules/recepcion/recepcion_tiktok.css",
  "recepcion/general" => "assets/css/modules/recepcion/recepcion_general.css"
  ,"recepcion/cajas-buscar" => "assets/css/modules/encomiendas/buscar.css"
  ,"recepcion/caja-ver" => "assets/css/modules/encomiendas/buscar.css"
  ,"recepcion/caja-editar" => "assets/css/modules/recepcion/recepcion_general.css"
  ,"encomiendas/buscar" => "assets/css/modules/encomiendas/buscar.css"
  ,"encomiendas/ver" => "assets/css/modules/encomiendas/buscar.css"
  ,"encomiendas/editar" => "assets/css/modules/encomiendas/buscar.css"
  ,"entrega" => "assets/css/modules/entrega/entregar.css"
  ,"entrega/entregadas" => [
    "assets/css/modules/entrega/entregadas.css",
    "assets/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css",
    "assets/plugins/datatables-responsive/css/responsive.bootstrap4.min.css"
  ]
  ,"entrega/retirados" => "assets/css/modules/entrega/retirados.css"
  ,"entrega/fotos-pendientes" => "assets/css/modules/entrega/fotos_pendientes.css"
  ,"entrega/detalle" => "assets/css/modules/entrega/detalle_entrega.css"
  ,"cajas-tiktok/nueva" => "assets/css/modules/cajas_tiktok/nuevo.css"
  ,"cajas-tiktok/editar" => "assets/css/modules/cajas_tiktok/editar.css"
  ,"clientes" => [
    "assets/css/modules/clientes/index.css",
    "assets/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css",
    "assets/plugins/datatables-responsive/css/responsive.bootstrap4.min.css",
    "assets/plugins/sweetalert2/sweetalert2.min.css"
  ]
  ,"almacenes" => [
    "assets/css/modules/almacenes/index.css",
    "assets/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css",
    "assets/plugins/datatables-responsive/css/responsive.bootstrap4.min.css"
  ]
  ,"usuarios" => [
    "assets/css/modules/usuarios/index.css",
    "assets/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css",
    "assets/plugins/datatables-responsive/css/responsive.bootstrap4.min.css",
    "assets/plugins/sweetalert2/sweetalert2.min.css"
  ]
];

$scripts_vista = [
  "recepcion/general" => [
    "assets/js/jquery.min.js",
    "assets/js/bootstrap.bundle.min.js",
    "assets/js/modules/clientes/index.js"
  ],
  "clientes" => [
    "assets/js/jquery.min.js",
    "assets/js/bootstrap.bundle.min.js",
    "assets/plugins/datatables/jquery.dataTables.min.js",
    "assets/plugins/datatables-bs4/js/dataTables.bootstrap4.min.js",
    "assets/plugins/datatables-responsive/js/dataTables.responsive.min.js",
    "assets/plugins/datatables-responsive/js/responsive.bootstrap4.min.js",
    "assets/plugins/sweetalert2/sweetalert2.all.min.js",
    "assets/js/modules/clientes/index.js"
  ],
  "almacenes" => [
    "assets/js/jquery.min.js",
    "assets/js/bootstrap.bundle.min.js",
    "assets/plugins/datatables/jquery.dataTables.min.js",
    "assets/plugins/datatables-bs4/js/dataTables.bootstrap4.min.js",
    "assets/plugins/datatables-responsive/js/dataTables.responsive.min.js",
    "assets/plugins/datatables-responsive/js/responsive.bootstrap4.min.js",
    "assets/plugins/sweetalert2/sweetalert2.all.min.js",
    "assets/js/modules/almacenes/index.js"
  ],
  "encomiendas/buscar" => [
    "assets/js/modules/encomiendas/buscar.js"
  ],
  "entrega/entregadas" => [
    "assets/js/jquery.min.js",
    "assets/plugins/datatables/jquery.dataTables.min.js",
    "assets/plugins/datatables-bs4/js/dataTables.bootstrap4.min.js",
    "assets/plugins/datatables-responsive/js/dataTables.responsive.min.js",
    "assets/plugins/datatables-responsive/js/responsive.bootstrap4.min.js",
    "assets/js/modules/entrega/entregadas.js"
  ],
  "usuarios" => [
    "assets/js/jquery.min.js",
    "assets/js/bootstrap.bundle.min.js",
    "assets/plugins/datatables/jquery.dataTables.min.js",
    "assets/plugins/datatables-bs4/js/dataTables.bootstrap4.min.js",
    "assets/plugins/datatables-responsive/js/dataTables.responsive.min.js",
    "assets/plugins/datatables-responsive/js/responsive.bootstrap4.min.js",
    "assets/plugins/sweetalert2/sweetalert2.all.min.js",
    "assets/js/modules/usuarios/index.js"
  ]
];

$ruta_solicitada = $_GET["ruta"] ?? null;
$datos_vista = [];

if (isset($_SESSION["ingreso"]) && $_SESSION["ingreso"] === "ok" && empty($_SESSION["csrf_token"])) {
  $_SESSION["csrf_token"] = bin2hex(random_bytes(32));
}
$csrfToken = $_SESSION["csrf_token"] ?? "";

if (
  isset($_SESSION["ingreso"]) &&
  $_SESSION["ingreso"] === "ok" &&
  isset($rutas_validas[$ruta_solicitada]) &&
  is_array($rutas_validas[$ruta_solicitada])
) {
  $controlador = $rutas_validas[$ruta_solicitada]["controlador"] ?? "ControladorRecepcion";
  $accion = $rutas_validas[$ruta_solicitada]["accion"];
  $datos_vista = $controlador::$accion();
}
?>
<!DOCTYPE html>
<html lang="en">

  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>La Merca Box</title>
    <link rel="shortcut icon" href="#">
    <!-- Base URL dinámica -->
    <?php $base_url = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/') . '/'; ?>
    <!-- Font Awesome -->
    <link rel="stylesheet" href="<?php echo $base_url; ?>assets/plugins/fontawesome-free/css/all.min.css">
    <!-- Theme style -->
    <link rel="stylesheet" href="<?php echo $base_url; ?>assets/css/index.css">
    <link rel="stylesheet" href="<?php echo $base_url; ?>assets/css/adminlte.css">
    <link rel="stylesheet" href="<?php echo $base_url; ?>assets/css/componentes/aside-menu.css">
    <?php foreach ((array) ($estilos_vista[$ruta_solicitada] ?? []) as $estilo) { ?>
      <link rel="stylesheet" href="<?php echo $base_url . $estilo; ?>">
    <?php } ?>

    <!--icono-->
    <link rel="icon" href="<?php echo $base_url; ?>assets/img/icon.jpg">

  </head>

  <body>
    <?php
    date_default_timezone_set("America/La_Paz");
    $fechaActual= date("Y-m-d");

    //comprobamos las sesiones
    if (isset($_SESSION["ingreso"]) && $_SESSION["ingreso"] == "ok") {
      include __DIR__ . "/componentes/asideMenu.php";
      echo '<main class="contenido-principal">';

      if ($ruta_solicitada !== null) {
        if (array_key_exists($ruta_solicitada, $rutas_validas)) {
          $ruta_vista = is_array($rutas_validas[$ruta_solicitada])
            ? $rutas_validas[$ruta_solicitada]["vista"]
            : $rutas_validas[$ruta_solicitada];
          extract($datos_vista, EXTR_SKIP);
          include __DIR__ . "/" . $ruta_vista;
        } else {
          echo "Ruta no válida.";
        }

        foreach ($scripts_vista[$ruta_solicitada] ?? [] as $script) {
          echo '<script src="' . htmlspecialchars($base_url . $script, ENT_QUOTES, "UTF-8") . '"></script>';
        }
        include __DIR__ . "/componentes/footer.php";
      }
    } else {
      include __DIR__ . "/login.php";
    }
    ?>