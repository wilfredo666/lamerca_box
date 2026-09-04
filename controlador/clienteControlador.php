<?php

class ControladorCliente
{
  public static function ctrVistaClientes()
  {
    if (($_SERVER["REQUEST_METHOD"] ?? "GET") === "POST") {
      self::ctrProcesarFormulario();
    }

    $mensajeCliente = $_SESSION["mensaje_cliente"] ?? "";
    unset($_SESSION["mensaje_cliente"]);

    return [
      "clientes" => ModeloCliente::mdlListar(),
      "mensajeCliente" => $mensajeCliente
    ];
  }

  private static function ctrProcesarFormulario()
  {
    self::ctrValidarCsrf($_POST["csrf_token"] ?? "");
    $accion = $_POST["accion"] ?? "";
    $id = filter_input(INPUT_POST, "id", FILTER_VALIDATE_INT);

    if ($accion === "crear") {
      ModeloCliente::mdlCrear(self::ctrDatosCliente());
      self::ctrRedirigir("Cliente registrado correctamente.");
    }

    if ($accion === "editar" && $id) {
      ModeloCliente::mdlActualizar($id, self::ctrDatosCliente());
      self::ctrRedirigir("Cliente actualizado correctamente.");
    }

    if ($accion === "cambiar_estado" && $id) {
      $activo = filter_input(INPUT_POST, "activo", FILTER_VALIDATE_INT);
      if ($activo === null || !in_array($activo, [0, 1], true)) {
        throw new InvalidArgumentException("Estado de cliente inválido.");
      }
      ModeloCliente::mdlCambiarEstado($id, $activo);
      self::ctrRedirigir("Estado del cliente actualizado.");
    }

    if ($accion === "eliminar" && $id) {
      ModeloCliente::mdlEliminar($id);
      self::ctrRedirigir("Cliente eliminado correctamente.");
    }

    throw new InvalidArgumentException("Acción de cliente inválida.");
  }

  private static function ctrDatosCliente()
  {
    $nombre = trim((string) ($_POST["nombre"] ?? ""));
    $pais = trim((string) ($_POST["pais"] ?? ""));
    $ciudad = trim((string) ($_POST["ciudad"] ?? ""));
    $celular = trim((string) ($_POST["celular"] ?? ""));
    $observaciones = trim((string) ($_POST["observaciones"] ?? ""));

    if ($nombre === "" || $pais === "" || $ciudad === "") {
      throw new InvalidArgumentException("Nombre, país y ciudad son obligatorios.");
    }

    $ciudadesPermitidas = [
      "La Paz", "El Alto", "Cochabamba", "Santa Cruz", "Oruro",
      "Potosi", "Pando", "Beni", "Sucre", "Tarija"
    ];

    if (!in_array($ciudad, $ciudadesPermitidas, true)) {
      throw new InvalidArgumentException("La ciudad seleccionada no es válida.");
    }

    if (mb_strlen($nombre) > 150 || mb_strlen($pais) > 100 || mb_strlen($ciudad) > 100
      || mb_strlen($celular) > 30 || mb_strlen($observaciones) > 5000) {
      throw new InvalidArgumentException("Uno o más campos superan el tamaño permitido.");
    }

    return [
      ":nombre" => $nombre,
      ":celular" => $celular !== "" ? $celular : null,
      ":observaciones" => $observaciones !== "" ? $observaciones : null,
      ":pais" => $pais,
      ":ciudad" => $ciudad
    ];
  }

  private static function ctrValidarCsrf($token)
  {
    if (!is_string($token) || !hash_equals($_SESSION["csrf_token"] ?? "", $token)) {
      throw new InvalidArgumentException("La sesión del formulario expiró. Intente nuevamente.");
    }
  }

  private static function ctrRedirigir($mensaje)
  {
    $_SESSION["mensaje_cliente"] = $mensaje;
    $rutaRetorno = $_POST["retorno"] ?? "clientes";
    $rutasPermitidas = ["clientes", "recepcion/general"];
    if (!in_array($rutaRetorno, $rutasPermitidas, true)) {
      $rutaRetorno = "clientes";
    }
    header("Location: " . self::ctrUrlProyecto() . $rutaRetorno);
    exit;
  }

  private static function ctrUrlProyecto()
  {
    $directorio = str_replace("\\", "/", realpath(dirname(__DIR__)));
    $documentRoot = str_replace("\\", "/", realpath($_SERVER["DOCUMENT_ROOT"] ?? ""));
    return $documentRoot !== "" && str_starts_with($directorio, $documentRoot)
      ? rtrim(substr($directorio, strlen($documentRoot)), "/") . "/"
      : "/";
  }
}
