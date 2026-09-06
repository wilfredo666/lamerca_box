<?php
class ControladorCaja
{
  public static function ctrVistaCaja()
  {
    $idAlmacen = self::idAlmacenSesion();
    return [
      "movimientos" => ModeloCaja::mdlMovimientos($idAlmacen),
      "resumen" => ModeloCaja::mdlResumen($idAlmacen)
    ];
  }

  public static function ctrRegistrar()
  {
    if (($_SERVER["REQUEST_METHOD"] ?? "GET") !== "POST") {
      throw new RuntimeException("Método no permitido.");
    }
    self::validarCsrf($_POST["csrf_token"] ?? "");

    $tipo = trim((string) ($_POST["tipo"] ?? ""));
    $concepto = trim((string) ($_POST["concepto"] ?? ""));
    $descripcion = trim((string) ($_POST["descripcion"] ?? ""));
    $cantidad = filter_var($_POST["cantidad"] ?? null, FILTER_VALIDATE_FLOAT);
    $fechaMovimiento = trim((string) ($_POST["fecha_movimiento"] ?? ""));
    $fecha = DateTime::createFromFormat("Y-m-d\TH:i", $fechaMovimiento);

    if (!in_array($tipo, ["Ingreso", "Salida"], true) || $concepto === "" ||
      $cantidad === false || $cantidad <= 0 || !$fecha ||
      $fecha->format("Y-m-d\TH:i") !== $fechaMovimiento) {
      throw new InvalidArgumentException("Complete correctamente los datos del movimiento.");
    }

    ModeloCaja::mdlRegistrar([
      ":tipo" => $tipo,
      ":concepto" => $concepto,
      ":descripcion" => $descripcion === "" ? null : $descripcion,
      ":cantidad" => $cantidad,
      ":id_usuario" => self::idUsuarioSesion(),
      ":id_almacen" => self::idAlmacenSesion(),
      ":fecha_movimiento" => $fecha->format("Y-m-d H:i:s")
    ]);
    self::redirigir("caja");
  }

  public static function ctrAnular()
  {
    if (($_SERVER["REQUEST_METHOD"] ?? "GET") !== "POST") {
      throw new RuntimeException("Método no permitido.");
    }
    self::validarCsrf($_POST["csrf_token"] ?? "");
    $idCaja = filter_input(INPUT_POST, "id_caja", FILTER_VALIDATE_INT);
    if (!$idCaja) {
      throw new InvalidArgumentException("Movimiento no válido.");
    }
    ModeloCaja::mdlAnular($idCaja, self::idAlmacenSesion());
    self::redirigir("caja");
  }

  private static function idUsuarioSesion()
  {
    $id = filter_var($_SESSION["idUsuario"] ?? null, FILTER_VALIDATE_INT);
    if (!$id) {
      throw new RuntimeException("La sesión no tiene un usuario válido.");
    }
    return $id;
  }

  private static function idAlmacenSesion()
  {
    $id = filter_var($_SESSION["idAlmacen"] ?? null, FILTER_VALIDATE_INT);
    if (!$id) {
      throw new RuntimeException("La sesión no tiene un almacén válido.");
    }
    return $id;
  }

  private static function validarCsrf($token)
  {
    if (!is_string($token) || !hash_equals($_SESSION["csrf_token"] ?? "", $token)) {
      throw new InvalidArgumentException("La sesión del formulario expiró.");
    }
  }

  private static function redirigir($ruta)
  {
    $base = rtrim(dirname($_SERVER["SCRIPT_NAME"]), "/") . "/";
    header("Location: " . $base . $ruta);
    exit;
  }
}
