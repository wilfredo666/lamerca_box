<?php

class ControladorRecepcion
{
  static public function ctrVistaRecepcion()
  {
    return [];
  }

  static public function ctrVistaTikTok()
  {
    if (($_SERVER["REQUEST_METHOD"] ?? "GET") === "POST") {
      $idCajaCliente = filter_input(INPUT_POST, "caja_cliente_id", FILTER_VALIDATE_INT);
      $paquetes = self::ctrPaquetesDesdePost();
      if (!$idCajaCliente || empty($paquetes)) {
        return ["errorVista" => "Seleccione una caja y registre al menos un paquete."];
      }
      $idCaja = ModeloRecepcion::mdlRegistrarRecepcionTikTok($idCajaCliente, $paquetes);
      header("Location: " . self::ctrUrlProyecto() . "recepcion/comprobante?id=" . $idCaja);
      exit;
    }

    return [
      "cajasTikTok" => ModeloRecepcion::mdlCajasTikTokActivas(),
      "nuevaCajaId" => filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT) ?: 0,
      "nuevaCajaNombre" => ""
    ];
  }

  static public function ctrVistaGeneral()
  {
    $tiposRecepcion = ModeloRecepcion::mdlTiposRecepcionActivos();
    $clasificaciones = ModeloRecepcion::mdlClasificacionesActivas();
    $datosVista = [
      "clientes" => array_values(array_filter(
        ModeloCliente::mdlListar(),
        fn($cliente) => (int) $cliente["activo"] === 1
      )),
      "tiposRecepcion" => $tiposRecepcion,
      "clasificaciones" => $clasificaciones,
      "mensajeCliente" => $_SESSION["mensaje_cliente"] ?? "",
      "clienteRecepcionSeleccionado" => $_SESSION["cliente_recepcion_seleccionado"] ?? null
    ];
    unset($_SESSION["mensaje_cliente"]);
    unset($_SESSION["cliente_recepcion_seleccionado"]);

    if (($_SERVER["REQUEST_METHOD"] ?? "GET") === "POST") {
      $token = $_POST["csrf_token"] ?? "";
      if (!is_string($token) || !hash_equals($_SESSION["csrf_token"] ?? "", $token)) {
        return $datosVista + ["errorVista" => "La sesión del formulario expiró. Intente nuevamente."];
      }

      try {
        $recepcion = self::ctrDatosRecepcionGeneral($tiposRecepcion);
        $paquetes = self::ctrPaquetesGeneralesDesdePost($clasificaciones);
      } catch (InvalidArgumentException | RuntimeException $error) {
        return $datosVista + ["errorVista" => $error->getMessage()];
      }

      if (empty($paquetes)) {
        return $datosVista + ["errorVista" => "Registre al menos una encomienda completa."];
      }

      try {
        $idRecepcion = ModeloRecepcion::mdlRegistrarRecepcionGeneral($recepcion, $paquetes);
      } catch (Throwable $error) {
        self::ctrEliminarFotosPaquetes($paquetes);
        throw $error;
      }
      header("Location: " . self::ctrUrlProyecto() . "recepcion/comprobante-general?id=" . $idRecepcion);
      exit;
    }

    return $datosVista;
  }

    static public function ctrBuscarCajas()
    {
      $buscar = trim((string) ($_GET["buscar"] ?? ""));
      $pagina = filter_input(INPUT_GET, "pagina", FILTER_VALIDATE_INT);
      $pagina = $pagina && $pagina > 0 ? $pagina : 1;
      $porPagina = 12;
      ModeloRecepcion::mdlCerrarRecepcionesCompletadas();
      $totalCajas = ModeloRecepcion::mdlContarRecepciones($buscar);
      $totalPaginas = max(1, (int) ceil($totalCajas / $porPagina));
      if ($pagina > $totalPaginas) {
        $pagina = $totalPaginas;
      }

      return [
        "cajas" => ModeloRecepcion::mdlBuscarRecepciones($buscar, $pagina, $porPagina),
        "buscar" => $buscar,
        "totalCajas" => $totalCajas,
        "paginaActual" => $pagina,
        "totalPaginas" => $totalPaginas
      ];
    }

    static public function ctrVerCaja()
    {
      $id = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT);
      $detalle = $id ? ModeloRecepcion::mdlDetalleRecepcion($id) : null;
      if ($detalle === null) {
        return ["errorVista" => "Caja no encontrada."];
      }

      $recepcion = $detalle["recepcion"];
      $paquetes = $detalle["paquetes"];
      $numeroWhatsapp = preg_replace("/[^0-9]/", "", $recepcion["celular_cliente"] ?? "");
      $mensajeWhatsapp = "📦 *COMPROBANTE DE RECEPCIÓN*\n\n"
        . "👤 Cliente: *" . ($recepcion["nombre_cliente"] ?? "") . "*\n"
        . "🏢 Empresa: *" . ($recepcion["empresa"] ?: "Sin empresa registrada") . "*\n"
        . "🔖 Código: *" . ($recepcion["codigo"] ?? "") . "*\n"
        . "📅 Fecha: " . date("d/m/Y", strtotime($recepcion["fecha_registro"])) . "\n\n"
        . "*ENCOMIENDAS*\n";
      foreach ($paquetes as $paquete) {
        $mensajeWhatsapp .= "• " . ($paquete["codigo"] ?? "")
          . " - " . ($paquete["destinatario"] ?? "")
          . " (" . ($paquete["estado"] ?? "") . ")\n";
      }
      $mensajeWhatsapp .= "\n*TOTAL: " . count($paquetes) . " ENCOMIENDA"
        . (count($paquetes) === 1 ? "" : "S") . "*\n"
        . "Revisa el seguimiento aquí: https://info.lamercabolivia.com/"
        . rawurlencode((string) ($recepcion["codigo"] ?? "")) . "\n\n"
        . "Gracias por confiar en *Tu Merca Encomiendas*.";

      return $detalle + [
        "numeroWhatsapp" => $numeroWhatsapp,
        "mensajeWhatsapp" => $mensajeWhatsapp
      ];
    }

    static public function ctrEditarCaja()
    {
      $id = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT);
      $detalle = $id ? ModeloRecepcion::mdlDetalleRecepcion($id) : null;
      if ($detalle === null) {
        return ["errorVista" => "Caja no encontrada."];
      }

      $tiposRecepcion = ModeloRecepcion::mdlTiposRecepcionActivos();
      $clasificaciones = ModeloRecepcion::mdlClasificacionesActivas();
      $datosVista = $detalle + [
        "tiposRecepcion" => $tiposRecepcion,
        "clasificaciones" => $clasificaciones
      ];

      if (($_SERVER["REQUEST_METHOD"] ?? "GET") === "POST") {
        $token = $_POST["csrf_token"] ?? "";
        if (!is_string($token) || !hash_equals($_SESSION["csrf_token"] ?? "", $token)) {
          return $datosVista + ["errorVista" => "La sesión del formulario expiró."];
        }
        try {
          $recepcion = self::ctrDatosRecepcionGeneral($tiposRecepcion);
          $paquetes = self::ctrPaquetesGeneralesDesdePost($clasificaciones);
          if (empty($paquetes)) {
            throw new InvalidArgumentException("Registre al menos una nueva encomienda.");
          }
          try {
            ModeloRecepcion::mdlActualizarRecepcionYAgregar($id, $recepcion, $paquetes);
          } catch (Throwable $error) {
            self::ctrEliminarFotosPaquetes($paquetes);
            throw $error;
          }
          header("Location: " . self::ctrUrlProyecto() . "recepcion/caja-ver?id=" . $id);
          exit;
        } catch (InvalidArgumentException | RuntimeException $error) {
          return $datosVista + ["errorVista" => $error->getMessage()];
        }
      }
      return $datosVista;
    }

    static public function ctrEliminarCaja()
    {
      if (($_SERVER["REQUEST_METHOD"] ?? "GET") !== "POST") {
        throw new RuntimeException("Método no permitido.");
      }
      $buscar = trim((string) ($_POST["buscar"] ?? ""));
      $pagina = filter_input(INPUT_POST, "pagina", FILTER_VALIDATE_INT);
      $pagina = $pagina && $pagina > 0 ? $pagina : 1;
      $rutaRetorno = "recepcion/cajas-buscar?" . http_build_query([
        "buscar" => $buscar,
        "pagina" => $pagina
      ]);
      try {
        $token = $_POST["csrf_token"] ?? "";
        if (!is_string($token) || !hash_equals($_SESSION["csrf_token"] ?? "", $token)) {
          throw new InvalidArgumentException("La sesión del formulario expiró.");
        }
        $id = filter_input(INPUT_POST, "id", FILTER_VALIDATE_INT);
        if (!$id) {
          throw new InvalidArgumentException("Caja no válida.");
        }
        ModeloRecepcion::mdlEliminarRecepcion($id);
      } catch (InvalidArgumentException $error) {
        header(
          "Location: " . self::ctrUrlProyecto() . $rutaRetorno . "&tipo=error&mensaje="
          . rawurlencode($error->getMessage())
        );
        exit;
      }
      header("Location: " . self::ctrUrlProyecto() . $rutaRetorno);
      exit;
    }

    static public function ctrSubirFotoCaja()
    {
      if (($_SERVER["REQUEST_METHOD"] ?? "GET") !== "POST") {
        throw new RuntimeException("Método no permitido.");
      }

      $id = filter_input(INPUT_POST, "id", FILTER_VALIDATE_INT);
      $buscar = trim((string) ($_POST["buscar"] ?? ""));
      $pagina = filter_input(INPUT_POST, "pagina", FILTER_VALIDATE_INT);
      $pagina = $pagina && $pagina > 0 ? $pagina : 1;
      $rutaRetorno = "recepcion/cajas-buscar?" . http_build_query([
        "buscar" => $buscar,
        "pagina" => $pagina
      ]);
      try {
        $token = $_POST["csrf_token"] ?? "";
        if (!is_string($token) || !hash_equals($_SESSION["csrf_token"] ?? "", $token)) {
          throw new InvalidArgumentException("La sesión del formulario expiró.");
        }
        if (!$id || !isset($_FILES["foto"]) || $_FILES["foto"]["error"] !== UPLOAD_ERR_OK) {
          throw new InvalidArgumentException("Seleccione una fotografía válida.");
        }

        $archivo = $_FILES["foto"];
        if ($archivo["size"] < 1 || $archivo["size"] > 5 * 1024 * 1024) {
          throw new InvalidArgumentException("La fotografía debe pesar menos de 5 MB.");
        }
        $mime = (new finfo(FILEINFO_MIME_TYPE))->file($archivo["tmp_name"]);
        $extensiones = [
          "image/jpeg" => "jpg",
          "image/png" => "png",
          "image/gif" => "gif",
          "image/webp" => "webp"
        ];
        if (!isset($extensiones[$mime]) || @getimagesize($archivo["tmp_name"]) === false) {
          throw new InvalidArgumentException("El archivo debe ser una imagen JPG, PNG, GIF o WEBP.");
        }

        $directorio = dirname(__DIR__) . DIRECTORY_SEPARATOR . "assets" . DIRECTORY_SEPARATOR
          . "img" . DIRECTORY_SEPARATOR . "recepciones";
        if (!is_dir($directorio) && !mkdir($directorio, 0755, true)) {
          throw new RuntimeException("No se pudo preparar el directorio de imágenes.");
        }
        $nombre = "recepcion_" . $id . "_" . bin2hex(random_bytes(12))
          . "." . $extensiones[$mime];
        $ruta = $directorio . DIRECTORY_SEPARATOR . $nombre;
        if (!move_uploaded_file($archivo["tmp_name"], $ruta)) {
          throw new RuntimeException("No se pudo guardar la fotografía.");
        }

        try {
          ModeloRecepcion::mdlActualizarFoto($id, $nombre);
        } catch (Throwable $error) {
          @unlink($ruta);
          throw $error;
        }
        header("Location: " . self::ctrUrlProyecto() . $rutaRetorno);
        exit;
      } catch (InvalidArgumentException | RuntimeException $error) {
        header(
          "Location: " . self::ctrUrlProyecto()
          . $rutaRetorno . "&error=" . rawurlencode($error->getMessage())
        );
        exit;
      }
    }

  private static function ctrDatosRecepcionGeneral($tiposRecepcion)
  {
    $idCliente = filter_input(INPUT_POST, "id_cliente", FILTER_VALIDATE_INT);
    $tipoRecepcion = trim((string) ($_POST["tipo_recepcion"] ?? ""));
    $empresa = trim((string) ($_POST["empresa"] ?? ""));
    $observaciones = trim((string) ($_POST["observaciones"] ?? ""));
    $tiposValidos = array_column($tiposRecepcion, "descripcion");
    $idAlmacen = filter_var($_SESSION["idAlmacen"] ?? null, FILTER_VALIDATE_INT);
    $idUsuario = filter_var($_SESSION["idUsuario"] ?? null, FILTER_VALIDATE_INT);

    if (!$idCliente || !ModeloRecepcion::mdlClienteActivoExiste($idCliente)) {
      throw new InvalidArgumentException("Seleccione un cliente activo.");
    }
    if (!in_array($tipoRecepcion, $tiposValidos, true)) {
      throw new InvalidArgumentException("El tipo de recepción seleccionado no es válido.");
    }
    if (!$idAlmacen || !$idUsuario) {
      throw new RuntimeException("No se encontró el almacén o usuario de la sesión.");
    }
    if (mb_strlen($empresa) > 50 || mb_strlen($observaciones) > 5000) {
      throw new InvalidArgumentException("Uno o más datos de la recepción superan el tamaño permitido.");
    }

    return [
      "id_cliente" => $idCliente,
      "empresa" => $empresa !== "" ? $empresa : null,
      "id_almacen" => $idAlmacen,
      "id_usuario" => $idUsuario,
      "tipo_recepcion" => $tipoRecepcion,
      "observaciones" => $observaciones !== "" ? $observaciones : null
    ];
  }

  private static function ctrPaquetesGeneralesDesdePost($clasificaciones)
  {
    $destinatarios = $_POST["destinatario"] ?? [];
    $contactos = $_POST["contacto"] ?? [];
    $descripciones = $_POST["descripcion"] ?? [];
    $precios = $_POST["precio"] ?? [];
    $quienesPagan = $_POST["quien_paga"] ?? [];
    $clasificacionesPaquete = $_POST["clasificacion"] ?? [];
    $fotosEncomienda = $_FILES["foto_encomienda"] ?? ["name" => [], "tmp_name" => [], "size" => [], "error" => []];
    if (!is_array($fotosEncomienda) || !is_array($fotosEncomienda["error"] ?? null)) {
      throw new InvalidArgumentException("La información de las fotos de encomiendas no es válida.");
    }
    $clasificacionesValidas = array_column($clasificaciones, "descripcion");
    $paquetes = [];

    foreach ($destinatarios as $indice => $destinatario) {
      $destinatario = trim((string) $destinatario);
      $contacto = trim((string) ($contactos[$indice] ?? ""));
      $descripcion = trim((string) ($descripciones[$indice] ?? ""));
      $clasificacion = trim((string) ($clasificacionesPaquete[$indice] ?? ""));
      $quienPaga = trim((string) ($quienesPagan[$indice] ?? ""));
      $precio = filter_var($precios[$indice] ?? null, FILTER_VALIDATE_FLOAT);

      if ($destinatario === "" && $contacto === "" && $descripcion === "") {
        continue;
      }
      if (
        $destinatario === "" ||
        !in_array($clasificacion, $clasificacionesValidas, true) ||
        $precio === false ||
        $precio < 0 ||
        !in_array($quienPaga, ["Destinatario", "Remitente"], true)
      ) {
        throw new InvalidArgumentException("Complete correctamente los datos de cada encomienda.");
      }
      if (
        mb_strlen($destinatario) > 150 ||
        mb_strlen($contacto) > 30 ||
        mb_strlen($descripcion) > 5000
      ) {
        throw new InvalidArgumentException("Uno o más datos de una encomienda superan el tamaño permitido.");
      }

      $paquetes[] = [
        "destinatario" => $destinatario,
        "contacto" => $contacto !== "" ? $contacto : null,
        "descripcion" => $descripcion !== "" ? $descripcion : null,
        "clasificacion" => $clasificacion,
        "precio" => $precio,
        "quien_paga" => $quienPaga,
        "foto" => null,
        "indice_foto" => $indice
      ];
    }

    try {
      foreach ($paquetes as &$paquete) {
        $paquete["foto"] = self::ctrGuardarFotoEncomienda($fotosEncomienda, $paquete["indice_foto"]);
        unset($paquete["indice_foto"]);
      }
      unset($paquete);
    } catch (Throwable $error) {
      unset($paquete);
      self::ctrEliminarFotosPaquetes($paquetes);
      throw $error;
    }

    return $paquetes;
  }

  private static function ctrPaquetesDesdePost()
  {
    $clientes = $_POST["cliente"] ?? [];
    $celulares = $_POST["celular"] ?? [];
    $detalles = $_POST["detalle"] ?? [];
    $precios = $_POST["precio_base"] ?? [];
    $pagadosPor = $_POST["pagado_por"] ?? [];
    $paquetes = [];

    foreach ($clientes as $indice => $cliente) {
      $cliente = trim((string) $cliente);
      $celular = trim((string) ($celulares[$indice] ?? ""));
      $detalle = trim((string) ($detalles[$indice] ?? ""));
      if ($cliente === "" && $celular === "" && $detalle === "") {
        continue;
      }
      $precio = filter_var($precios[$indice] ?? null, FILTER_VALIDATE_FLOAT);
      $pagadoPor = $pagadosPor[$indice] ?? "Cliente";
      if ($cliente === "" || $precio === false || $precio < 0 || !in_array($pagadoPor, ["Cliente", "Vendedor"], true)) {
        throw new InvalidArgumentException("Los datos de cada paquete son inválidos.");
      }
      $paquetes[] = compact("cliente", "celular", "detalle", "precio", "pagadoPor") + ["pagado_por" => $pagadoPor];
    }

    return $paquetes;
  }

  private static function ctrGuardarFotoEncomienda($fotosEncomienda, $indice)
  {
    $error = (int) ($fotosEncomienda["error"][$indice] ?? UPLOAD_ERR_NO_FILE);
    if ($error === UPLOAD_ERR_NO_FILE) {
      return null;
    }
    if ($error !== UPLOAD_ERR_OK) {
      throw new InvalidArgumentException("No se pudo subir una de las fotos de las encomiendas.");
    }

    $tmpName = $fotosEncomienda["tmp_name"][$indice] ?? "";
    if ($tmpName === "" || !is_uploaded_file($tmpName)) {
      throw new InvalidArgumentException("La foto subida no es válida.");
    }
    $tamaño = filesize($tmpName);
    if ($tamaño === false || $tamaño < 1 || $tamaño > 5 * 1024 * 1024) {
      throw new InvalidArgumentException("Cada foto debe pesar como máximo 5 MB.");
    }

    $extensiones = ["image/jpeg" => "jpg", "image/png" => "png", "image/gif" => "gif", "image/webp" => "webp"];
    $tipo = (new finfo(FILEINFO_MIME_TYPE))->file($tmpName);
    if (!isset($extensiones[$tipo]) || @getimagesize($tmpName) === false) {
      throw new InvalidArgumentException("La foto debe ser una imagen JPG, PNG, GIF o WEBP válida.");
    }

    $directorioFotos = dirname(__DIR__) . DIRECTORY_SEPARATOR . "assets" . DIRECTORY_SEPARATOR . "img" . DIRECTORY_SEPARATOR . "paquetes";
    if (!is_dir($directorioFotos) && !mkdir($directorioFotos, 0755, true) && !is_dir($directorioFotos)) {
      throw new RuntimeException("No se pudo preparar el directorio de fotos de encomiendas.");
    }

    $nombreArchivo = "paquete_" . bin2hex(random_bytes(16)) . "." . $extensiones[$tipo];
    $rutaDestino = $directorioFotos . DIRECTORY_SEPARATOR . $nombreArchivo;
    if (!move_uploaded_file($tmpName, $rutaDestino)) {
      throw new RuntimeException("No se pudo guardar la foto de la encomienda.");
    }

    return $nombreArchivo;
  }

  private static function ctrEliminarFotosPaquetes($paquetes)
  {
    $directorioFotos = dirname(__DIR__) . DIRECTORY_SEPARATOR . "assets" . DIRECTORY_SEPARATOR . "img" . DIRECTORY_SEPARATOR . "paquetes";
    foreach ($paquetes as $paquete) {
      $nombreArchivo = basename((string) ($paquete["foto"] ?? ""));
      if (str_starts_with($nombreArchivo, "paquete_") && is_file($directorioFotos . DIRECTORY_SEPARATOR . $nombreArchivo)) {
        unlink($directorioFotos . DIRECTORY_SEPARATOR . $nombreArchivo);
      }
    }
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

  static public function ctrVistaHistorial()
  {
    return [
      "cajas" => ModeloRecepcion::mdlHistorialCajas()
    ];
  }

  static public function ctrVistaComprobante()
  {
    $idCaja = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT);
    if (!$idCaja) {
      return ["errorVista" => "Caja no encontrada."];
    }

    $comprobante = ModeloRecepcion::mdlComprobanteCaja($idCaja);
    if ($comprobante === null) {
      return ["errorVista" => "Caja no encontrada."];
    }

    $textoLotes = "";
    foreach ($comprobante["lotes"] as $numeroLote => $lote) {
      $cantidad = (int) $lote["cantidad"];
      $textoLotes .= "Recepción {$numeroLote} - "
        . date("H:i", strtotime($lote["hora_inicio"])) . " - {$cantidad} paquete"
        . ($cantidad !== 1 ? "s" : "") . "\n";
    }

    $caja = $comprobante["caja"];
    $comprobante["numeroWhatsapp"] = preg_replace(
      "/[^0-9]/",
      "",
      $caja["whatsapp"] ?? ""
    );
    $iconoCaja = json_decode('"\uD83D\uDCE6"');
    $iconoEmpresa = json_decode('"\uD83C\uDFEA"');
    $iconoPersona = json_decode('"\uD83D\uDC64"');
    $iconoCodigo = json_decode('"\uD83D\uDD16"');
    $iconoFecha = json_decode('"\uD83D\uDCC5"');
    $iconoPendiente = json_decode('"\uD83D\uDFE1"');
    $iconoEntregado = json_decode('"\u2705"');
    $comprobante["mensajeWhatsapp"] = $iconoCaja . " *COMPROBANTE DE RECEPCIÓN*\n\n"
      . $iconoEmpresa . " [EMPRESA] *" . ($caja["empresa"] ?? "") . "*\n"
      . $iconoPersona . " [PROPIETARIA] *" . ($caja["propietaria"] ?? "") . "*\n"
      . $iconoCodigo . " [CODIGO] *" . ($caja["codigo"] ?? "") . "*\n"
      . $iconoFecha . " [FECHA] " . date("d/m/Y", strtotime($caja["fecha"])) . "\n\n"
      . "*RECEPCIONES*\n" . $textoLotes . "\n"
      . "*TOTAL ACUMULADO: " . $comprobante["resumen"]["total"] . " PAQUETES*\n"
      . $iconoPendiente . " [PENDIENTES] " . $comprobante["resumen"]["pendientes"] . "\n"
      . $iconoEntregado . " [ENTREGADOS] " . $comprobante["resumen"]["entregados"] . "\n\n"
      . "Gracias por confiar en *Tu Merca Encomiendas*.";

    return $comprobante;
  }

  static public function ctrVistaComprobanteGeneral()
  {
    $idRecepcion = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT);
    if (!$idRecepcion) {
      return ["errorVista" => "Recepción no encontrada."];
    }

    $comprobante = ModeloRecepcion::mdlComprobanteRecepcion($idRecepcion);
    if ($comprobante === null) {
      return ["errorVista" => "Recepción no encontrada."];
    }

    $paquetes = $comprobante["paquetes"];
    $pendientes = count(array_filter($paquetes, function ($paquete) {
      return $paquete["estado"] === "Pendiente";
    }));
    $entregados = count(array_filter($paquetes, function ($paquete) {
      return $paquete["estado"] === "Entregado";
    }));
    $hora = date("H:i", strtotime($comprobante["recepcion"]["fecha_registro"]));
    $cantidad = count($paquetes);
    $empresa = $comprobante["recepcion"]["empresa"] ?: "Sin empresa registrada";
    $comprobante["numeroWhatsapp"] = preg_replace(
      "/[^0-9]/",
      "",
      $comprobante["recepcion"]["celular_cliente"] ?? ""
    );
    $iconoCaja = json_decode('"\uD83D\uDCE6"');
    $iconoEmpresa = json_decode('"\uD83C\uDFEA"');
    $iconoPersona = json_decode('"\uD83D\uDC64"');
    $iconoCodigo = json_decode('"\uD83D\uDD16"');
    $iconoFecha = json_decode('"\uD83D\uDCC5"');
    $iconoPendiente = json_decode('"\uD83D\uDFE1"');
    $iconoEntregado = json_decode('"\u2705"');
    $comprobante["mensajeWhatsapp"] = $iconoCaja . " *COMPROBANTE DE RECEPCIÓN*\n\n"
      . $iconoEmpresa . " [EMPRESA] *" . $empresa . "*\n"
      . $iconoPersona . " [CLIENTE] *" . ($comprobante["recepcion"]["nombre_cliente"] ?? "") . "*\n"
      . $iconoCodigo . " [CODIGO] *" . ($comprobante["recepcion"]["codigo"] ?? "") . "*\n"
      . $iconoFecha . " [FECHA] " . date("d/m/Y", strtotime($comprobante["recepcion"]["fecha_registro"])) . "\n\n"
      . "*RECEPCIONES*\n"
      . "[RECEPCION 1] - {$hora} - {$cantidad} paquete" . ($cantidad === 1 ? "" : "s") . "\n\n"
      . "*TOTAL ACUMULADO: {$cantidad} PAQUETES*\n"
      . $iconoPendiente . " [PENDIENTES] {$pendientes}\n"
      . $iconoEntregado . " [ENTREGADOS] {$entregados}\n\n"
      . "Gracias por confiar en *Tu Merca Encomiendas*.";
    return $comprobante;
  }
}
