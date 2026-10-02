<?php

class ControladorEncomiendas
{
  static public function ctrBuscar()
  {
    $buscar = trim((string) ($_GET["buscar"] ?? ""));
    $pagina = filter_input(INPUT_GET, "pagina", FILTER_VALIDATE_INT);
    $pagina = $pagina && $pagina > 0 ? $pagina : 1;
    $soloSinImagen = ($_GET["sin_imagen"] ?? "") === "1";
    $porPagina = 12;
    $idAlmacen = (int) ($_SESSION["idAlmacen"] ?? 0);
    $totalEncomiendas = ModeloEncomiendas::mdlContarBusqueda($buscar, $idAlmacen, $soloSinImagen);
    $totalPaginas = max(1, (int) ceil($totalEncomiendas / $porPagina));
    if ($pagina > $totalPaginas) {
      $pagina = $totalPaginas;
    }

    return [
      "encomiendas" => ModeloEncomiendas::mdlBuscar(
        $buscar,
        $idAlmacen,
        $pagina,
        $porPagina,
        $soloSinImagen
      ),
      "buscar" => $buscar,
      "soloSinImagen" => $soloSinImagen,
      "cantidadSinImagen" => ModeloEncomiendas::mdlContarSinImagen($buscar, $idAlmacen),
      "totalEncomiendas" => $totalEncomiendas,
      "paginaActual" => $pagina,
      "totalPaginas" => $totalPaginas,
      "almacenesTraspaso" => ModeloTraspaso::mdlAlmacenesActivos($idAlmacen)
    ];
  }

  static public function ctrEliminadas()
  {
    return [
      "encomiendasEliminadas" => ModeloEncomiendas::mdlEliminadas(
        (int) ($_SESSION["idAlmacen"] ?? 0)
      )
    ];
  }

  static public function ctrDetalleEliminada()
  {
    $id = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT);
    $idAlmacen = filter_var($_SESSION["idAlmacen"] ?? null, FILTER_VALIDATE_INT);
    $encomienda = $id && $idAlmacen
      ? ModeloEncomiendas::mdlDetalleEliminada($id, $idAlmacen)
      : null;

    return $encomienda
      ? ["encomiendaEliminada" => $encomienda]
      : ["errorVista" => "No se encontró la encomienda eliminada en este almacén."];
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
    $clasificaciones = ModeloRecepcion::mdlClasificacionesActivas();
    $clasificacionesValidas = array_column($clasificaciones, "descripcion");
    if (!in_array($encomienda["clasificacion"], $clasificacionesValidas, true)) {
      $clasificaciones[] = [
        "descripcion" => $encomienda["clasificacion"],
        "estado" => 0
      ];
      $clasificacionesValidas[] = $encomienda["clasificacion"];
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
        !in_array($datos[":clasificacion"], $clasificacionesValidas, true) ||
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

    return [
      "encomienda" => $encomienda,
      "clasificaciones" => $clasificaciones
    ];
  }

  static public function ctrEliminar()
  {
    if (($_SERVER["REQUEST_METHOD"] ?? "GET") !== "POST") {
      throw new RuntimeException("Método no permitido.");
    }
    try {
      self::ctrValidarCsrf($_POST["csrf_token"] ?? "");
      $id = filter_input(INPUT_POST, "id", FILTER_VALIDATE_INT);
      $motivoEntrada = $_POST["observacion"] ?? null;
      if (!$id) {
        throw new InvalidArgumentException("Encomienda no válida.");
      }
      if (!is_string($motivoEntrada)) {
        throw new InvalidArgumentException("Indique un motivo de eliminación válido.");
      }
      $motivo = trim($motivoEntrada);
      if ($motivo === "" || mb_strlen($motivo) > 250) {
        throw new InvalidArgumentException("Escriba un motivo de eliminación de 1 a 250 caracteres.");
      }
      $idAlmacen = filter_var($_SESSION["idAlmacen"] ?? null, FILTER_VALIDATE_INT);
      if (!$idAlmacen) {
        throw new RuntimeException("No se encontró el almacén de la sesión.");
      }
      ModeloEncomiendas::mdlEliminar($id, $motivo, $idAlmacen);
      header("Location: " . self::ctrUrlProyecto() . "encomiendas/buscar?mensaje=" . rawurlencode("Encomienda eliminada lógicamente."));
      exit;
    } catch (InvalidArgumentException | RuntimeException $error) {
      header("Location: " . self::ctrUrlProyecto() . "encomiendas/buscar?mensaje=" . rawurlencode($error->getMessage()));
      exit;
    }
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