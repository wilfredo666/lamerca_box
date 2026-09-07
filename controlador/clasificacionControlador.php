<?php
class ControladorClasificacion
{
  public static function ctrVistaClasificaciones()
  {
    return ["clasificaciones" => ModeloClasificacion::mdlMostrarRegistros()];
  }

  public static function ctrNuevo()
  {
    if (($_SERVER["REQUEST_METHOD"] ?? "GET") === "POST") {
      self::validarCsrf($_POST["csrf_token"] ?? "");
      ModeloClasificacion::mdlRegistrar(self::datosFormulario());
      self::redirigir("clasificaciones");
    }
    return ["clasificacion" => null, "modo" => "nuevo"];
  }

  public static function ctrEditar()
  {
    $id = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT);
    if (!$id) {
      throw new InvalidArgumentException("Clasificación no válida.");
    }
    $clasificacion = ModeloClasificacion::mdlInfo($id);
    if (!$clasificacion) {
      throw new InvalidArgumentException("Clasificación no encontrada.");
    }
    if (($_SERVER["REQUEST_METHOD"] ?? "GET") === "POST") {
      self::validarCsrf($_POST["csrf_token"] ?? "");
      ModeloClasificacion::mdlActualizar($id, self::datosFormulario());
      self::redirigir("clasificaciones");
    }
    return ["clasificacion" => $clasificacion, "modo" => "editar"];
  }

  public static function ctrEliminar()
  {
    if (($_SERVER["REQUEST_METHOD"] ?? "GET") !== "POST") {
      throw new RuntimeException("Método no permitido.");
    }
    self::validarCsrf($_POST["csrf_token"] ?? "");
    $id = filter_input(INPUT_POST, "id", FILTER_VALIDATE_INT);
    if (!$id) {
      throw new InvalidArgumentException("Clasificación no válida.");
    }
    ModeloClasificacion::mdlEliminar($id);
    self::redirigir("clasificaciones");
  }

  private static function datosFormulario()
  {
    $descripcion = trim((string) ($_POST["descripcion"] ?? ""));
    $estado = isset($_POST["estado"]) ? (int) $_POST["estado"] : 1;
    if ($descripcion === "" || strlen($descripcion) > 50 || !in_array($estado, [0, 1], true)) {
      throw new InvalidArgumentException("Complete correctamente la clasificación.");
    }
    return ["descripcion" => $descripcion, "estado" => $estado];
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
