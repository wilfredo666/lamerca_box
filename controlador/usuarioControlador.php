<?php
$ruta = parse_url($_SERVER['REQUEST_URI']);

if (isset($ruta["query"])) {
  if (
    $ruta["query"] == "ctrRegUsuario" ||
    $ruta["query"] == "ctrEditUsuario" ||
    $ruta["query"] == "ctrEliUsuario" ||
    $ruta["query"] == "ctrCambioEstado" ||
    $ruta["query"] == "ctrActualizarPermiso"
  ) {
    $metodo = $ruta["query"];
    $usuario = new ControladorUsuario();
    $usuario->$metodo();
  }
}

class ControladorUsuario
{
  static public function ctrVistaUsuarios()
  {
    return ["usuarios" => ModeloUsuario::mdlInfoUsuarios()];
  }

  static public function ctrNuevo()
  {
    if (($_SERVER["REQUEST_METHOD"] ?? "GET") === "POST") {
      self::validarCsrf($_POST["csrf_token"] ?? "");
      $datos = self::datosPanel(true);
      ModeloUsuario::mdlRegistrarDesdePanel($datos);
      self::redirigir("usuarios");
    }
    return [];
  }

  static public function ctrEditar()
  {
    $id = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT);
    if (!$id || !ModeloUsuario::mdlInfoUsuario($id)) {
      throw new InvalidArgumentException("Usuario no encontrado.");
    }
    if (($_SERVER["REQUEST_METHOD"] ?? "GET") === "POST") {
      self::validarCsrf($_POST["csrf_token"] ?? "");
      ModeloUsuario::mdlActualizarDesdePanel($id, self::datosPanel(false));
      self::redirigir("usuarios");
    }
    return [];
  }

  static public function ctrEliminar()
  {
    if (($_SERVER["REQUEST_METHOD"] ?? "GET") !== "POST") {
      throw new RuntimeException("Método no permitido.");
    }
    self::validarCsrf($_POST["csrf_token"] ?? "");
    $id = filter_input(INPUT_POST, "id", FILTER_VALIDATE_INT);
    if (!$id) {
      throw new InvalidArgumentException("Usuario no válido.");
    }
    ModeloUsuario::mdlEliUsuario($id);
    self::redirigir("usuarios");
  }

  private static function datosPanel($nuevo)
  {
    $nombre = trim((string) ($_POST["nombre"] ?? ""));
    $email = trim((string) ($_POST["email"] ?? ""));
    $categoria = trim((string) ($_POST["categoria"] ?? ""));
    $estado = isset($_POST["estado"]) ? (int) $_POST["estado"] : 1;
    $password = (string) ($_POST["password"] ?? "");
    if ($nombre === "" || !filter_var($email, FILTER_VALIDATE_EMAIL) ||
      $categoria === "" || !in_array($estado, [0, 1], true) ||
      ($nuevo && strlen($password) < 6)) {
      throw new InvalidArgumentException("Complete correctamente los datos del usuario.");
    }
    return [
      ":nombre" => $nombre,
      ":email" => $email,
      ":password" => $password === "" ? "" : password_hash($password, PASSWORD_DEFAULT),
      ":categoria" => $categoria,
      ":estado" => $estado
    ];
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
  static public function ctrIngresoUsuario()
  {
    if (isset($_POST["usuario"])) {
      $usuario = trim((string) ($_POST["usuario"] ?? ""));
      $password = (string) ($_POST["password"] ?? "");
      $almacenSeleccionado = (string) ($_POST["almacen"] ?? "");
      $separador = strrpos($almacenSeleccionado, "-");
      if ($usuario === "" || $password === "" || $separador === false) {
        echo "<p class='text-danger text-center bg-red mt-1 rounded-pill'>Complete los datos de acceso.</p>";
        return;
      }
      $nomAlmacen = trim(substr($almacenSeleccionado, 0, $separador));
      $idAlmacen = filter_var(substr($almacenSeleccionado, $separador + 1), FILTER_VALIDATE_INT);
      if ($nomAlmacen === "" || !$idAlmacen) {
        echo "<p class='text-danger text-center bg-red mt-1 rounded-pill'>Seleccione un almacén válido.</p>";
        return;
      }

      //comprobando validez y disponibilidad del usuario
      $respuesta = ModeloUsuario::mdlAccesoUsuario($usuario);

      if ($respuesta == false) {
        echo "<p class='text-danger text-center bg-red mt-1 rounded-pill'>Error de acceso, intente de nuevo</p>";
        return;
      }

      if ($usuario == $respuesta['email'] && password_verify($password, $respuesta['password']) && $respuesta["estado_usuario"] == 1) {
        $_SESSION["ingreso"] = "ok";
        $_SESSION["email"] = $respuesta["email"];
        $_SESSION["nombre"] = $respuesta["nombre"];
        $_SESSION["idUsuario"] = $respuesta["id_usuario"];
        $_SESSION["categoria"] = $respuesta["categoria"];

        //comprobando si el usuario tiene acceso al almacen
        $accAlmacen=ModeloUsuario::mdlAccesoAlmacen($respuesta["id_usuario"], $nomAlmacen);
        if($accAlmacen["permiso"]==1){
          
          //guardando informacion de almacen en sesion
          $almacen = ControladorAlmacen::ctrInfoAlmacen($idAlmacen);
          if (!$almacen) {
            echo "<p class='text-danger text-center bg-red mt-1 rounded-pill'>El almacén seleccionado no existe.</p>";
            return;
          }
          session_regenerate_id(true);
          $_SESSION["idAlmacen"] = $almacen["id_almacen"];
          $_SESSION["nomAlmacen"] = $almacen["nombre_almacen"];
          $_SESSION["descAlmacen"] = $almacen["descripcion"];
          
          echo '<script>
                 window.location="inicio";
                </script>';
        }else{
          unset(
            $_SESSION["ingreso"],
            $_SESSION["idUsuario"],
            $_SESSION["idAlmacen"],
            $_SESSION["nomAlmacen"],
            $_SESSION["descAlmacen"],
            $_SESSION["csrf_token"]
          );
          echo "<p class='text-danger text-center bg-red mt-1 rounded-pill'>Error de acceso, intente de nuevo</p>";
        }

      } else {

        echo "<p class='text-danger text-center bg-red mt-1 rounded-pill'>Error de acceso, intente de nuevo</p>";
      }
    }
  }

  static public function ctrInfoUsuarios()
  {
    $respuesta = ModeloUsuario::mdlInfoUsuarios();
    return $respuesta;
  }

  static public function ctrRegUsuario()
  {
    require "../modelo/usuarioModelo.php";

    $password = password_hash($_POST["passUsuario"], PASSWORD_DEFAULT);
    $data = array(
      "emailUsuario" => $_POST["emailUsuario"],
      "nomUsuario" => $_POST["nomUsuario"],
      "passUsuario" => $password
    );

    $respuesta = ModeloUsuario::mdlRegUsuario($data);
    echo $respuesta;
  }

  static public function ctrInfoUsuario($id)
  {
    $respuesta = ModeloUsuario::mdlInfoUsuario($id);
    return $respuesta;
  }

  static public function ctrEditUsuario()
  {
    require "../modelo/usuarioModelo.php";

    $passActual = $_POST["passActual"];
    if ($passActual == $_POST["passUsuario"]) {
      $password = $passActual;
    } else {
      $password = password_hash($_POST["passUsuario"], PASSWORD_DEFAULT);
    }

    $data = array(
      "idUsuario" => $_POST["idUsuario"],
      "nomUsuario" => $_POST["nomUsuario"],
      "passUsuario" => $password,
      "catUsuario" => $_POST["catUsuario"]
    );

    $respuesta = ModeloUsuario::mdlEditUsuario($data);
    echo $respuesta;
  }


  static public function ctrEliUsuario()
  {
    require "../modelo/usuarioModelo.php";

    $id = $_POST["id"];

    $respuesta = ModeloUsuario::mdlEliUsuario($id);
    echo $respuesta;
  }

  static public function ctrCambioEstado(){
    require_once "../modelo/usuarioModelo.php";

    $estado =$_POST["est"];
    $id =$_POST["id"];

    $respuesta=ModeloUsuario::mdlCambioEstado($estado, $id);

    echo $respuesta;

  }

  static public function ctrCantidadUsuarios()
  {
    $respuesta = ModeloUsuario::mdlCantidadUsuarios();
    return $respuesta;
  }

  // PERMISOS
  static public function ctrUsuarioPermiso($idUsuario, $idPermiso)
  {
    $respuesta = ModeloUsuario::mdlUsuarioPermiso($idUsuario, $idPermiso);
    return $respuesta;
  }

  static public function ctrActualizarPermiso()
  {
    require "../modelo/usuarioModelo.php";

    $data = array(
      "idUsuario" => $_POST["idUsuario"],
      "idPermiso" => $_POST["idPermiso"]
    );

    $respuesta = ModeloUsuario::mdlActualizarPermiso($data);
    echo $respuesta;
  }

  static public function ctrListaPermisos(){
    $respuesta = ModeloUsuario::mdlListaPermisos();
    return $respuesta;
  }
}
