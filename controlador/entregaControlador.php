<?php

class ControladorEntrega
{
  public static function ctrVistaEntrega()
  {
    $seleccionados = $_GET["seleccionados"] ?? "";
    $ids = array_values(array_filter(
      array_map("intval", is_string($seleccionados) ? explode(",", $seleccionados) : []),
      fn($id) => $id > 0
    ));
    return [
      "paquetes" => ModeloEntrega::mdlPaquetesPorEstado("Pendiente"),
      "seleccionados" => $ids
    ];
  }

  public static function ctrVistaEntregadas()
  {
    return ["paquetes" => ModeloEntrega::mdlEntregasRegistradas()];
  }

  public static function ctrVistaRetirados()
  {
    return ["paquetes" => ModeloEntrega::mdlPaquetesPorEstado("Retirado")];
  }

  public static function ctrVistaFotosPendientes()
  {
    $paquetes = ModeloEntrega::mdlFotosPendientes();
    return ["paquetes" => $paquetes, "totalPendientes" => count($paquetes)];
  }

  public static function ctrVistaDetalle()
  {
    $id = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT);
    $paquete = $id ? ModeloEntrega::mdlPaquete($id) : null;
    if ($paquete === null) {
      return ["errorVista" => "Paquete no encontrado."];
    }
    $precioBase = (float) ($paquete["precio_base"] ?: $paquete["precio"]);
    $precioBase = $precioBase > 0 ? $precioBase : 2;
    $dias = max(0, (new DateTime($paquete["fecha_registro"]))->diff(new DateTime())->days);
    $recargo = $dias > 7 ? 1 : 0;
    return [
      "paquete" => $paquete,
      "dias" => $dias,
      "precioBase" => $precioBase,
      "recargo" => $recargo,
      "totalCobrar" => $precioBase + $recargo,
      "cobrado" => $paquete["cobrado"] === "Si",
      "medioCobro" => $paquete["medio_cobro"] ?? ""
    ];
  }

  public static function ctrCobrarEntregar()
  {
    self::ctrSoloPost();
    $id = filter_input(INPUT_POST, "id", FILTER_VALIDATE_INT);
    $medio = $_POST["medio_cobro"] ?? "";
    try {
      self::ctrValidarCsrf($_POST["csrf_token"] ?? "");
      if (!$id || !in_array($medio, ["Efectivo", "QR"], true)) {
        throw new InvalidArgumentException("Datos de cobro inválidos.");
      }
      ModeloEntrega::mdlRegistrarEntregaIndividual($id, $medio);
      self::ctrRedirigirDetalle($id, "Paquete cobrado y entregado.");
    } catch (Throwable $error) {
      self::ctrRedirigirDetalle($id, $error->getMessage());
    }
  }

  public static function ctrRetirar()
  {
    self::ctrSoloPost();
    $id = filter_input(INPUT_POST, "id", FILTER_VALIDATE_INT);
    $motivo = trim((string) ($_POST["motivo_retiro"] ?? ""));
    try {
      self::ctrValidarCsrf($_POST["csrf_token"] ?? "");
      if (!$id || $motivo === "" || mb_strlen($motivo) > 1000) {
        throw new InvalidArgumentException("Indique un motivo de retiro válido.");
      }
      ModeloEntrega::mdlRetirarPaquete($id, $motivo);
      self::ctrRedirigir("entrega/retirados", "Paquete retirado.");
    } catch (Throwable $error) {
      self::ctrRedirigirDetalle($id, $error->getMessage());
    }
  }

  public static function ctrSubirFoto()
  {
    self::ctrSoloPost();
    $id = filter_input(INPUT_POST, "id", FILTER_VALIDATE_INT);
    try {
      self::ctrValidarCsrf($_POST["csrf_token"] ?? "");
      if (!$id || !isset($_FILES["foto"])) {
        throw new InvalidArgumentException("Seleccione una fotografía válida.");
      }
      $archivo = $_FILES["foto"];
      if ($archivo["error"] !== UPLOAD_ERR_OK || $archivo["size"] < 1 || $archivo["size"] > 5 * 1024 * 1024) {
        throw new InvalidArgumentException("La fotografía debe pesar menos de 5 MB.");
      }
      $info = (new finfo(FILEINFO_MIME_TYPE))->file($archivo["tmp_name"]);
      $extensiones = ["image/jpeg" => "jpg", "image/png" => "png", "image/gif" => "gif", "image/webp" => "webp"];
      if (!isset($extensiones[$info]) || @getimagesize($archivo["tmp_name"]) === false) {
        throw new InvalidArgumentException("El archivo debe ser una imagen JPG, PNG, GIF o WEBP.");
      }
      $directorio = dirname(__DIR__) . DIRECTORY_SEPARATOR . "assets" . DIRECTORY_SEPARATOR . "img" . DIRECTORY_SEPARATOR . "paquetes";
      if (!is_dir($directorio) && !mkdir($directorio, 0755, true)) {
        throw new RuntimeException("No se pudo preparar el directorio de imágenes.");
      }
      $nombre = "paquete_" . $id . "_" . bin2hex(random_bytes(12)) . "." . $extensiones[$info];
      if (!move_uploaded_file($archivo["tmp_name"], $directorio . DIRECTORY_SEPARATOR . $nombre)) {
        throw new RuntimeException("No se pudo guardar la fotografía.");
      }
      try {
        ModeloEntrega::mdlActualizarFoto($id, $nombre);
      } catch (Throwable $error) {
        @unlink($directorio . DIRECTORY_SEPARATOR . $nombre);
        throw $error;
      }
      self::ctrRedirigir("entrega/fotos-pendientes", "Fotografía guardada.");
    } catch (Throwable $error) {
      self::ctrRedirigir("entrega/fotos-pendientes", $error->getMessage());
    }
  }

  public static function ctrEntregaMultiple()
  {
    self::ctrSoloPost(true);
    header("Content-Type: application/json; charset=utf-8");
    try {
      $entrada = json_decode(file_get_contents("php://input"), true, 512, JSON_THROW_ON_ERROR);
      self::ctrValidarCsrf($entrada["csrf_token"] ?? "");
      $ids = $entrada["ids"] ?? [];
      $recargo = filter_var($entrada["recargo"] ?? 0, FILTER_VALIDATE_FLOAT);
      $descuento = filter_var($entrada["descuento"] ?? null, FILTER_VALIDATE_FLOAT);
      $medio = $entrada["medio_cobro"] ?? "";
      if (!is_array($ids) || !in_array($medio, ["Efectivo", "QR"], true) || $recargo === false || $descuento === false) {
        throw new InvalidArgumentException("Datos de entrega inválidos.");
      }
      $ids = array_values(array_unique(array_filter($ids, fn($id) => filter_var($id, FILTER_VALIDATE_INT) !== false && (int) $id > 0)));
      if (empty($ids) || count($ids) > 100 || $recargo < 0 || $descuento < 0) {
        throw new InvalidArgumentException("Seleccione paquetes e indique recargos y descuentos válidos.");
      }
      ModeloEntrega::mdlRegistrarEntregaMultiple(array_map("intval", $ids), (float) $recargo, (float) $descuento, $medio);
      echo json_encode(["ok" => true]);
    } catch (Throwable $error) {
      http_response_code(422);
      echo json_encode(["ok" => false, "error" => $error->getMessage()]);
    }
    exit;
  }

  private static function ctrSoloPost($json = false)
  {
    if (($_SERVER["REQUEST_METHOD"] ?? "GET") !== "POST") {
      if ($json) {
        http_response_code(405);
        header("Content-Type: application/json; charset=utf-8");
        echo json_encode(["ok" => false, "error" => "Método no permitido."]);
      }
      exit;
    }
  }

  private static function ctrValidarCsrf($token)
  {
    $esperado = $_SESSION["csrf_token"] ?? "";
    if (!is_string($token) || $esperado === "" || !hash_equals($esperado, $token)) {
      throw new InvalidArgumentException("La solicitud no es válida. Actualice la página e intente nuevamente.");
    }
  }

  private static function ctrRedirigirDetalle($id, $mensaje)
  {
    self::ctrRedirigir("entrega/detalle" . ($id ? "?id=" . urlencode((string) $id) : ""), $mensaje);
  }

  private static function ctrRedirigir($ruta, $mensaje)
  {
    $separador = strpos($ruta, "?") === false ? "?" : "&";
    $baseUrl = rtrim(dirname($_SERVER["SCRIPT_NAME"]), "/\\") . "/";
    header("Location: " . $baseUrl . $ruta . $separador . "mensaje=" . urlencode($mensaje));
    exit;
  }
}
