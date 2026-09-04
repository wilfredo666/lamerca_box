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
      "mensajeCliente" => $_SESSION["mensaje_cliente"] ?? ""
    ];
    unset($_SESSION["mensaje_cliente"]);

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

      $idRecepcion = ModeloRecepcion::mdlRegistrarRecepcionGeneral($recepcion, $paquetes);
      header("Location: " . self::ctrUrlProyecto() . "recepcion/comprobante-general?id=" . $idRecepcion);
      exit;
    }

    return $datosVista;
  }

    static public function ctrBuscarCajas()
    {
      return [
        "cajas" => ModeloRecepcion::mdlBuscarRecepciones($_GET["buscar"] ?? ""),
        "buscar" => trim((string) ($_GET["buscar"] ?? ""))
      ];
    }

    static public function ctrVerCaja()
    {
      $id = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT);
      $detalle = $id ? ModeloRecepcion::mdlDetalleRecepcion($id) : null;
      return $detalle ?? ["errorVista" => "Caja no encontrada."];
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
          ModeloRecepcion::mdlActualizarRecepcionYAgregar($id, $recepcion, $paquetes);
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
      $token = $_POST["csrf_token"] ?? "";
      if (!is_string($token) || !hash_equals($_SESSION["csrf_token"] ?? "", $token)) {
        throw new InvalidArgumentException("La sesión del formulario expiró.");
      }
      $id = filter_input(INPUT_POST, "id", FILTER_VALIDATE_INT);
      if (!$id) {
        throw new InvalidArgumentException("Caja no válida.");
      }
      ModeloRecepcion::mdlEliminarRecepcion($id);
      header("Location: " . self::ctrUrlProyecto() . "recepcion/cajas-buscar");
      exit;
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
        "quien_paga" => $quienPaga
      ];
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
    $comprobante["mensajeWhatsapp"] = "TU MERCA ENCOMIENDAS\n\n"
      . "COMPROBANTE DE RECEPCIÓN\n\n"
      . "Empresa: " . ($caja["empresa"] ?? "") . "\n"
      . "Propietaria: " . ($caja["propietaria"] ?? "") . "\n"
      . "Código: " . ($caja["codigo"] ?? "") . "\n"
      . "Fecha: " . date("d/m/Y", strtotime($caja["fecha"])) . "\n\n"
      . "RECEPCIONES\n" . $textoLotes . "\n"
      . "TOTAL ACUMULADO: " . $comprobante["resumen"]["total"] . " PAQUETES\n"
      . "Pendientes: " . $comprobante["resumen"]["pendientes"] . "\n"
      . "Entregados: " . $comprobante["resumen"]["entregados"];

    return $comprobante;
  }

  static public function ctrVistaComprobanteGeneral()
  {
    $idRecepcion = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT);
    if (!$idRecepcion) {
      return ["errorVista" => "Recepción no encontrada."];
    }

    $comprobante = ModeloRecepcion::mdlComprobanteRecepcion($idRecepcion);
    return $comprobante ?? ["errorVista" => "Recepción no encontrada."];
  }
}
