<?php

class ControladorTraspaso
{
  public static function ctrVistaTraspasos()
  {
    $idAlmacen = self::idAlmacenSesion();
    return [
      "traspasos" => ModeloTraspaso::mdlListar($idAlmacen),
      "encomiendas" => ModeloTraspaso::mdlEncomiendasDisponibles($idAlmacen),
      "almacenes" => ModeloTraspaso::mdlAlmacenesActivos($idAlmacen)
    ];
  }

  public static function ctrRegistrar()
  {
    self::soloPost();
    self::validarCsrf($_POST["csrf_token"] ?? "");
    $idAlmacen = self::idAlmacenSesion();
    $idEncomienda = filter_var($_POST["id_encomienda"] ?? null, FILTER_VALIDATE_INT);
    $idDestino = filter_var($_POST["id_almacen_destino"] ?? null, FILTER_VALIDATE_INT);
    $concepto = trim((string) ($_POST["concepto"] ?? ""));

    if (!$idEncomienda || !$idDestino || $idDestino === $idAlmacen || mb_strlen($concepto) > 255) {
      throw new InvalidArgumentException("Complete correctamente los datos del traspaso.");
    }
    ModeloTraspaso::mdlRegistrar([
      "id_almacen_origen" => $idAlmacen,
      "id_almacen_destino" => $idDestino,
      "id_encomienda" => $idEncomienda,
      "concepto" => $concepto,
      "id_usuario" => self::idUsuarioSesion()
    ]);
    self::redirigir("traspasos");
  }

    public static function ctrRegistrarMultiple()
    {
      self::soloPost();
      self::validarCsrf($_POST["csrf_token"] ?? "");
      $origen = self::idAlmacenSesion();
      $destino = filter_var($_POST["id_almacen_destino"] ?? null, FILTER_VALIDATE_INT);
      $ids = $_POST["ids"] ?? [];
      $concepto = trim((string) ($_POST["concepto"] ?? ""));
      $ids = is_array($ids) ? array_values(array_unique(array_filter(
        array_map("intval", $ids),
        fn($id) => $id > 0
      ))) : [];
      if (!$destino || $destino === $origen || empty($ids) || count($ids) > 100 || mb_strlen($concepto) > 255) {
        throw new InvalidArgumentException("Complete correctamente los datos del traspaso.");
      }
      ModeloTraspaso::mdlRegistrarMultiple(
        $ids,
        $origen,
        $destino,
        $concepto,
        self::idUsuarioSesion()
      );
      self::redirigir("encomiendas/buscar");
    }

  private static function idAlmacenSesion()
  {
    $id = filter_var($_SESSION["idAlmacen"] ?? null, FILTER_VALIDATE_INT);
    if (!$id) {
      throw new RuntimeException("No se encontró el almacén de la sesión.");
    }
    return $id;
  }

  private static function idUsuarioSesion()
  {
    $id = filter_var($_SESSION["idUsuario"] ?? null, FILTER_VALIDATE_INT);
    if (!$id) {
      throw new RuntimeException("No se encontró el usuario de la sesión.");
    }
    return $id;
  }

  private static function validarCsrf($token)
  {
    if (!is_string($token) || !hash_equals($_SESSION["csrf_token"] ?? "", $token)) {
      throw new InvalidArgumentException("La sesión del formulario expiró.");
    }
  }

  private static function soloPost()
  {
    if (($_SERVER["REQUEST_METHOD"] ?? "GET") !== "POST") {
      throw new RuntimeException("Método no permitido.");
    }
  }

  private static function redirigir($ruta)
  {
    $base = rtrim(dirname($_SERVER["SCRIPT_NAME"]), "/") . "/";
    header("Location: " . $base . $ruta);
    exit;
  }
}
