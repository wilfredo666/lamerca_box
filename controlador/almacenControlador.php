<?php
$ruta = parse_url($_SERVER['REQUEST_URI']);

if (isset($ruta["query"])) {
  if (
    $ruta["query"] == "ctrRegAlmacen" ||
    $ruta["query"] == "ctrEditAlmacen" ||
    $ruta["query"] == "ctrEliAlmacen"
  ) {
    $metodo = $ruta["query"];
    $Almacen = new ControladorAlmacen();
    $Almacen->$metodo();
  }
}


class ControladorAlmacen {
  public static function ctrVistaAlmacenes() {
    return ["almacenes" => ModeloAlmacen::mdlMostrarRegistros()];
  }

  public static function ctrNuevo() {
    if (($_SERVER["REQUEST_METHOD"] ?? "GET") === "POST") {
      self::validarCsrf($_POST["csrf_token"] ?? "");
      ModeloAlmacen::mdlRegAlmacen(self::datosFormulario());
      self::redirigir("almacenes");
    }
    return ["almacen" => null, "modo" => "nuevo"];
  }

  public static function ctrEditar() {
    $id = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT);
    if (!$id) {
      throw new InvalidArgumentException("Almacén no válido.");
    }
    $almacen = ModeloAlmacen::mdlInfoAlmacen($id);
    if (!$almacen) {
      throw new InvalidArgumentException("Almacén no encontrado.");
    }
    if (($_SERVER["REQUEST_METHOD"] ?? "GET") === "POST") {
      self::validarCsrf($_POST["csrf_token"] ?? "");
      $datos = self::datosFormulario();
      $datos["id"] = $id;
      ModeloAlmacen::mdlEditAlmacen($datos);
      self::redirigir("almacenes");
    }
    return ["almacen" => $almacen, "modo" => "editar"];
  }

  public static function ctrEliminar() {
    if (($_SERVER["REQUEST_METHOD"] ?? "GET") !== "POST") {
      throw new RuntimeException("Método no permitido.");
    }
    self::validarCsrf($_POST["csrf_token"] ?? "");
    $id = filter_input(INPUT_POST, "id", FILTER_VALIDATE_INT);
    if (!$id) {
      throw new InvalidArgumentException("Almacén no válido.");
    }
    ModeloAlmacen::mdlEliAlmacen($id);
    self::redirigir("almacenes");
  }

  private static function datosFormulario() {
    $datos = [];
    foreach (["nombre", "descripcion", "ciudad", "direccion", "encargado", "contacto"] as $campo) {
      $datos[$campo] = trim((string) ($_POST[$campo] ?? ""));
    }
    $datos["estado"] = isset($_POST["estado"]) ? (int) $_POST["estado"] : 1;
    if ($datos["nombre"] === "" || $datos["ciudad"] === "" || !in_array($datos["estado"], [0, 1], true)) {
      throw new InvalidArgumentException("Complete el nombre, la ciudad y el estado.");
    }
    return $datos;
  }

  private static function validarCsrf($token) {
    if (!is_string($token) || !hash_equals($_SESSION["csrf_token"] ?? "", $token)) {
      throw new InvalidArgumentException("La sesión del formulario expiró.");
    }
  }

  private static function redirigir($ruta) {
    $base = rtrim(dirname($_SERVER["SCRIPT_NAME"]), "/") . "/";
    header("Location: " . $base . $ruta);
    exit;
  }

  public static function ctrMostrarRegistros() {

    $respuesta = ModeloAlmacen::mdlMostrarRegistros();
    return $respuesta;
  }

  public static function ctrInfoAlmacen($id) {

    $respuesta = ModeloAlmacen::mdlInfoAlmacen($id);
    return $respuesta;
  }

  public static function ctrRegAlmacen() {
    require "../modelo/almacenModelo.php";

    $data = $_POST;
    $respuesta = ModeloAlmacen::mdlRegAlmacen($data);
    echo $respuesta;
  }

  public static function ctrEditAlmacen() {
    require "../modelo/almacenModelo.php";
    
    // recuperando los datos del formulario
    $data = $_POST;
    $respuesta = ModeloAlmacen::mdlEditAlmacen($data);
    echo $respuesta;
  }

  public static function ctrEliAlmacen() {
    require "../modelo/almacenModelo.php";
    $id = $_POST["id"];
    $respuesta = ModeloAlmacen::mdlEliAlmacen($id);
    echo $respuesta;
  }
}
?>