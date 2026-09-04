<?php

class ControladorCajasTikTok
{
  static public function ctrNueva()
  {
    if ($_SERVER["REQUEST_METHOD"] === "POST") {
      $id = ModeloCajasTikTok::mdlCrear(self::ctrDatosPost());
      header("Location: " . self::ctrUrlProyecto() . "recepcion/tiktok?id=" . $id);
      exit;
    }
    return [];
  }

  static public function ctrEditar()
  {
    $id = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT);
    if (!$id) {
      return ["errorVista" => "Caja no encontrada."];
    }
    if ($_SERVER["REQUEST_METHOD"] === "POST") {
      ModeloCajasTikTok::mdlActualizar($id, self::ctrDatosPost());
      header("Location: " . self::ctrUrlProyecto() . "recepcion/tiktok");
      exit;
    }
    return ["caja" => ModeloCajasTikTok::mdlObtener($id)];
  }

  private static function ctrDatosPost()
  {
    $nombre = trim($_POST["nombre_tiktok"] ?? "");
    if ($nombre === "") {
      throw new InvalidArgumentException("El nombre TikTok es obligatorio.");
    }
    return [
      ":nombre" => $nombre,
      ":propietaria" => trim($_POST["propietaria"] ?? ""),
      ":whatsapp" => trim($_POST["whatsapp"] ?? ""),
      ":observaciones" => trim($_POST["observaciones"] ?? "")
    ];
  }

  private static function ctrUrlProyecto()
  {
    $directorioProyecto = str_replace("\\", "/", realpath(dirname(__DIR__)));
    $documentRoot = str_replace("\\", "/", realpath($_SERVER["DOCUMENT_ROOT"] ?? ""));

    if ($documentRoot !== "" && str_starts_with($directorioProyecto, $documentRoot)) {
      return rtrim(substr($directorioProyecto, strlen($documentRoot)), "/") . "/";
    }

    return "/";
  }
}
