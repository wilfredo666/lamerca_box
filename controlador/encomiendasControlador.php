<?php

class ControladorEncomiendas
{
  static public function ctrBuscar()
  {
    return [
      "encomiendas" => ModeloEncomiendas::mdlBuscar(
        $_GET["buscar"] ?? "",
        (int) ($_SESSION["idAlmacen"] ?? 0)
      ),
      "buscar" => trim((string) ($_GET["buscar"] ?? "")),
      "almacenesTraspaso" => ModeloTraspaso::mdlAlmacenesActivos((int) ($_SESSION["idAlmacen"] ?? 0))
    ];
  }

  static public function ctrVer()
  {
    $id = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT);
    return [
      "encomienda" => $id ? ModeloEncomiendas::mdlBuscarPorId($id) : null
    ];
  }

  static public function ctrEditar()
  {
    $id = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT);
    if (!$id) {
      return ["errorVista" => "Encomienda no encontrada."];
    }

    $encomienda = ModeloEncomiendas::mdlBuscarPorId($id);
    if ($encomienda === null) {
      return ["errorVista" => "Encomienda no encontrada."];
    }

    if (($_SERVER["REQUEST_METHOD"] ?? "GET") === "POST") {
      self::ctrValidarCsrf($_POST["csrf_token"] ?? "");
      $datos = [
        ":clasificacion" => trim((string) ($_POST["clasificacion"] ?? "")),
        ":descripcion" => trim((string) ($_POST["descripcion"] ?? "")),
        ":precio" => filter_var($_POST["precio"] ?? null, FILTER_VALIDATE_FLOAT),
        ":destinatario" => trim((string) ($_POST["destinatario"] ?? "")),
        ":contacto" => trim((string) ($_POST["contacto"] ?? "")),
        ":quien_paga" => trim((string) ($_POST["quien_paga"] ?? ""))
      ];
      if (
        $datos[":destinatario"] === "" ||
        $datos[":precio"] === false ||
        $datos[":precio"] < 0 ||
        !in_array($datos[":quien_paga"], ["Destinatario", "Remitente"], true)
      ) {
        throw new InvalidArgumentException("Complete correctamente los datos de la encomienda.");
      }
      ModeloEncomiendas::mdlActualizar($id, $datos);
      header("Location: " . self::ctrUrlProyecto() . "encomiendas/ver?id=" . $id);
      exit;
    }

    return ["encomienda" => $encomienda];
  }

  static public function ctrEliminar()
  {
    if (($_SERVER["REQUEST_METHOD"] ?? "GET") !== "POST") {
      throw new RuntimeException("Método no permitido.");
    }
    self::ctrValidarCsrf($_POST["csrf_token"] ?? "");
    $id = filter_input(INPUT_POST, "id", FILTER_VALIDATE_INT);
    if (!$id) {
      throw new InvalidArgumentException("Encomienda no válida.");
    }
    ModeloEncomiendas::mdlEliminar($id);
    header("Location: " . self::ctrUrlProyecto() . "encomiendas/buscar");
    exit;
  }

  private static function ctrValidarCsrf($token)
  {
    if (!is_string($token) || !hash_equals($_SESSION["csrf_token"] ?? "", $token)) {
      throw new InvalidArgumentException("La sesión del formulario expiró.");
    }
  }

  private static function ctrUrlProyecto()
  {
    $directorio = str_replace("\\", "/", realpath(dirname(__DIR__)));
    $documentRoot = str_replace("\\", "/", realpath($_SERVER["DOCUMENT_ROOT"] ?? ""));
    return $documentRoot !== "" && str_starts_with($directorio, $documentRoot)
      ? rtrim(substr($directorio, strlen($documentRoot)), "/") . "/"
      : "/";
  }

  static public function ctrTotalCobradoHoy()
  {
    return ModeloEncomiendas::mdlTotalCobradoHoy();
  }

  static public function ctrCantidadEncomiendasHoy()
  {
    return ModeloEncomiendas::mdlCantidadEncomiendasHoy();
  }

  static public function ctrCantidadPendientes()
  {
    return ModeloEncomiendas::mdlCantidadPendientes();
  }

  static public function ctrCantidadEntregadasHoy()
  {
    return ModeloEncomiendas::mdlCantidadEntregadasHoy();
  }
}