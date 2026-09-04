<?php

require_once "conexion.php";

class ModeloRecepcion
{
  static public function mdlBuscarRecepciones($termino = "")
  {
    $stmt = Conexion::conectar()->prepare(
      "SELECT r.id, r.codigo, r.empresa, r.tipo_recepcion, r.estado,
        r.fecha_registro, r.foto, r.observaciones,
        c.nombre AS nombre_cliente, c.celular AS celular_cliente,
        COUNT(e.id) AS total_encomiendas,
        SUM(e.estado = 'Pendiente') AS pendientes
      FROM recepciones r
      INNER JOIN clientes c ON c.id = r.id_cliente
      LEFT JOIN encomiendas e ON e.id_recepcion = r.id
      WHERE r.tipo_recepcion IN ('Caja TikTok', 'Caja general')
        AND (
          r.codigo LIKE :termino_codigo
          OR r.empresa LIKE :termino_empresa
          OR r.tipo_recepcion LIKE :termino_tipo
          OR c.nombre LIKE :termino_nombre
          OR c.celular LIKE :termino_celular
        )
      GROUP BY r.id, r.codigo, r.empresa, r.tipo_recepcion, r.estado,
        r.fecha_registro, r.foto, r.observaciones, c.nombre, c.celular
      ORDER BY r.fecha_registro DESC, r.id DESC"
    );
    $valor = "%" . trim($termino) . "%";
    $stmt->execute([
      ":termino_codigo" => $valor,
      ":termino_empresa" => $valor,
      ":termino_tipo" => $valor,
      ":termino_nombre" => $valor,
      ":termino_celular" => $valor
    ]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }

  static public function mdlDetalleRecepcion($id)
  {
    $conexion = Conexion::conectar();
    $stmt = $conexion->prepare(
      "SELECT r.*, c.nombre AS nombre_cliente, c.celular AS celular_cliente,
        a.nombre_almacen, u.nombre AS nombre_usuario
      FROM recepciones r
      INNER JOIN clientes c ON c.id = r.id_cliente
      LEFT JOIN almacen a ON a.id_almacen = r.id_almacen
      LEFT JOIN usuario u ON u.id_usuario = r.id_usuario_recepcion
      WHERE r.id = :id
      LIMIT 1"
    );
    $stmt->execute([":id" => $id]);
    $recepcion = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($recepcion === false) {
      return null;
    }

    $stmt = $conexion->prepare(
      "SELECT * FROM encomiendas WHERE id_recepcion = :id ORDER BY fecha_registro ASC, id ASC"
    );
    $stmt->execute([":id" => $id]);
    return ["recepcion" => $recepcion, "paquetes" => $stmt->fetchAll(PDO::FETCH_ASSOC)];
  }

  static public function mdlActualizarRecepcionYAgregar($id, $recepcion, $paquetes)
  {
    $conexion = Conexion::conectar();
    $conexion->beginTransaction();
    try {
      $stmt = $conexion->prepare(
        "UPDATE recepciones
        SET id_cliente = :id_cliente, empresa = :empresa,
          tipo_recepcion = :tipo_recepcion, observaciones = :observaciones
        WHERE id = :id AND estado = 'Abierta' AND DATE(fecha_registro) = CURDATE()"
      );
      $stmt->execute([
        ":id_cliente" => $recepcion["id_cliente"],
        ":empresa" => $recepcion["empresa"],
        ":tipo_recepcion" => $recepcion["tipo_recepcion"],
        ":observaciones" => $recepcion["observaciones"],
        ":id" => $id
      ]);

      if ($stmt->rowCount() === 0) {
        $check = $conexion->prepare("SELECT estado, DATE(fecha_registro) = CURDATE() AS es_hoy FROM recepciones WHERE id = :id");
        $check->execute([":id" => $id]);
        $estado = $check->fetch(PDO::FETCH_ASSOC);
        if ($estado === false || $estado["estado"] !== "Abierta") {
          throw new InvalidArgumentException("La caja está cerrada y no admite nuevas encomiendas.");
        }
        if ((int) $estado["es_hoy"] !== 1) {
          throw new InvalidArgumentException("Solo puede agregar encomiendas a una caja del día actual.");
        }
      }

      $stmt = $conexion->prepare(
        "INSERT INTO encomiendas
        (codigo, id_recepcion, clasificacion, descripcion, precio, destinatario, contacto, quien_paga, estado, cobrado)
        VALUES ('', :id_recepcion, :clasificacion, :descripcion, :precio, :destinatario, :contacto, :quien_paga, 'Pendiente', 0)"
      );
      $stmtCodigo = $conexion->prepare("UPDATE encomiendas SET codigo = :codigo WHERE id = :id");
      foreach ($paquetes as $paquete) {
        $stmt->execute([
          ":id_recepcion" => $id,
          ":clasificacion" => $paquete["clasificacion"],
          ":descripcion" => $paquete["descripcion"],
          ":precio" => $paquete["precio"],
          ":destinatario" => $paquete["destinatario"],
          ":contacto" => $paquete["contacto"],
          ":quien_paga" => $paquete["quien_paga"]
        ]);
        $idEncomienda = (int) $conexion->lastInsertId();
        $stmtCodigo->execute([
          ":codigo" => "ENC-" . str_pad((string) $idEncomienda, 6, "0", STR_PAD_LEFT),
          ":id" => $idEncomienda
        ]);
      }
      $conexion->commit();
    } catch (Throwable $error) {
      if ($conexion->inTransaction()) {
        $conexion->rollBack();
      }
      throw $error;
    }
  }

  static public function mdlEliminarRecepcion($id)
  {
    $conexion = Conexion::conectar();
    $stmt = $conexion->prepare(
      "SELECT COUNT(*) FROM entrega e
      INNER JOIN encomiendas en ON en.id = e.id_encomienda
      WHERE en.id_recepcion = :id"
    );
    $stmt->execute([":id" => $id]);
    if ((int) $stmt->fetchColumn() > 0) {
      throw new InvalidArgumentException("No se puede eliminar una caja que ya tiene entregas.");
    }

    $conexion->beginTransaction();
    try {
      $conexion->prepare("DELETE FROM encomiendas WHERE id_recepcion = :id")->execute([":id" => $id]);
      $stmt = $conexion->prepare("DELETE FROM recepciones WHERE id = :id");
      $stmt->execute([":id" => $id]);
      if ($stmt->rowCount() !== 1) {
        throw new InvalidArgumentException("La caja no existe o ya fue eliminada.");
      }
      $conexion->commit();
    } catch (Throwable $error) {
      if ($conexion->inTransaction()) {
        $conexion->rollBack();
      }
      throw $error;
    }
  }

  static public function mdlTiposRecepcionActivos()
  {
    $stmt = Conexion::conectar()->prepare(
      "SELECT descripcion
      FROM tipo_recepcion
      WHERE estado = 1
      ORDER BY descripcion ASC"
    );
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }

  static public function mdlClasificacionesActivas()
  {
    $stmt = Conexion::conectar()->prepare(
      "SELECT descripcion
      FROM clasificacion
      WHERE estado = 1
      ORDER BY descripcion ASC"
    );
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }

  static public function mdlClienteActivoExiste($idCliente)
  {
    $stmt = Conexion::conectar()->prepare(
      "SELECT 1 FROM clientes WHERE id = :id AND activo = 1 LIMIT 1"
    );
    $stmt->execute([":id" => $idCliente]);
    return $stmt->fetchColumn() !== false;
  }

  static public function mdlRegistrarRecepcionGeneral($recepcion, $paquetes)
  {
    $conexion = Conexion::conectar();
    $conexion->beginTransaction();

    try {
      $stmtRecepcion = $conexion->prepare(
        "INSERT INTO recepciones
        (codigo, id_cliente, empresa, id_almacen, id_usuario_recepcion, tipo_recepcion, observaciones, estado)
        VALUES ('', :id_cliente, :empresa, :id_almacen, :id_usuario, :tipo_recepcion, :observaciones, 'Abierta')"
      );
      $stmtRecepcion->execute([
        ":id_cliente" => $recepcion["id_cliente"],
        ":empresa" => $recepcion["empresa"],
        ":id_almacen" => $recepcion["id_almacen"],
        ":id_usuario" => $recepcion["id_usuario"],
        ":tipo_recepcion" => $recepcion["tipo_recepcion"],
        ":observaciones" => $recepcion["observaciones"]
      ]);
      $idRecepcion = (int) $conexion->lastInsertId();

      $conexion->prepare("UPDATE recepciones SET codigo = :codigo WHERE id = :id")
        ->execute([
          ":codigo" => "RG-" . str_pad((string) $idRecepcion, 6, "0", STR_PAD_LEFT),
          ":id" => $idRecepcion
        ]);

      $stmtEncomienda = $conexion->prepare(
        "INSERT INTO encomiendas
        (codigo, id_recepcion, clasificacion, descripcion, precio, destinatario, contacto, quien_paga, estado, cobrado)
        VALUES ('', :id_recepcion, :clasificacion, :descripcion, :precio, :destinatario, :contacto, :quien_paga, 'Pendiente', 0)"
      );
      $stmtCodigo = $conexion->prepare("UPDATE encomiendas SET codigo = :codigo WHERE id = :id");

      foreach ($paquetes as $paquete) {
        $stmtEncomienda->execute([
          ":id_recepcion" => $idRecepcion,
          ":clasificacion" => $paquete["clasificacion"],
          ":descripcion" => $paquete["descripcion"],
          ":precio" => $paquete["precio"],
          ":destinatario" => $paquete["destinatario"],
          ":contacto" => $paquete["contacto"],
          ":quien_paga" => $paquete["quien_paga"]
        ]);
        $idEncomienda = (int) $conexion->lastInsertId();
        $stmtCodigo->execute([
          ":codigo" => "ENC-" . str_pad((string) $idEncomienda, 6, "0", STR_PAD_LEFT),
          ":id" => $idEncomienda
        ]);
      }

      $conexion->commit();
      return $idRecepcion;
    } catch (Throwable $error) {
      if ($conexion->inTransaction()) {
        $conexion->rollBack();
      }
      throw $error;
    }
  }

  static public function mdlRegistrarRecepcionTikTok($idCajaCliente, $paquetes)
  {
    $conexion = Conexion::conectar();
    $conexion->beginTransaction();

    try {
      $stmtCliente = $conexion->prepare(
        "SELECT nombre_tiktok FROM cajas_clientes WHERE id = :id AND activo = 1"
      );
      $stmtCliente->execute([":id" => $idCajaCliente]);
      $cliente = $stmtCliente->fetch(PDO::FETCH_ASSOC);
      if ($cliente === false) {
        throw new InvalidArgumentException("La Caja TikTok seleccionada no existe.");
      }

      $stmtCaja = $conexion->prepare(
        "SELECT id, recepcion_id
        FROM cajas_tiktok
        WHERE empresa = :empresa AND DATE(fecha) = CURDATE() AND abierta = 1
        LIMIT 1"
      );
      $stmtCaja->execute([":empresa" => $cliente["nombre_tiktok"]]);
      $caja = $stmtCaja->fetch(PDO::FETCH_ASSOC);

      if ($caja === false) {
        $stmtRecepcion = $conexion->prepare(
          "INSERT INTO recepciones (codigo, tipo, origen, fecha, total_paquetes, estado, abierta)
          VALUES ('', 'TikTok', :origen, NOW(), 0, 'Pendiente', 1)"
        );
        $stmtRecepcion->execute([":origen" => $cliente["nombre_tiktok"]]);
        $idRecepcion = (int) $conexion->lastInsertId();
        $conexion->prepare("UPDATE recepciones SET codigo = :codigo WHERE id = :id")
          ->execute([
            ":codigo" => "TK-" . str_pad((string) $idRecepcion, 6, "0", STR_PAD_LEFT),
            ":id" => $idRecepcion
          ]);

        $stmtNuevaCaja = $conexion->prepare(
          "INSERT INTO cajas_tiktok (codigo, empresa, fecha, total_paquetes, estado, abierta, recepcion_id)
          VALUES ('', :empresa, NOW(), 0, 'Pendiente', 1, :recepcion)"
        );
        $stmtNuevaCaja->execute([
          ":empresa" => $cliente["nombre_tiktok"],
          ":recepcion" => $idRecepcion
        ]);
        $idCaja = (int) $conexion->lastInsertId();
        $conexion->prepare("UPDATE cajas_tiktok SET codigo = :codigo WHERE id = :id")
          ->execute([
            ":codigo" => "TK-" . str_pad((string) $idCaja, 6, "0", STR_PAD_LEFT),
            ":id" => $idCaja
          ]);
      } else {
        $idCaja = (int) $caja["id"];
        $idRecepcion = (int) $caja["recepcion_id"];
      }

      $stmtLote = $conexion->prepare(
        "SELECT COALESCE(MAX(lote_recepcion), 0) + 1 FROM encomiendas WHERE caja_id = :id"
      );
      $stmtLote->execute([":id" => $idCaja]);
      $lote = (int) $stmtLote->fetchColumn();

      self::mdlInsertarPaquetes(
        $conexion,
        $paquetes,
        "TikTok",
        $cliente["nombre_tiktok"],
        $idRecepcion,
        $lote,
        $idCaja
      );
      $total = self::mdlCantidadPaquetesCaja($conexion, $idCaja);
      $conexion->prepare("UPDATE cajas_tiktok SET total_paquetes = :total, ultima_actualizacion = NOW() WHERE id = :id")
        ->execute([":total" => $total, ":id" => $idCaja]);
      $conexion->prepare("UPDATE recepciones SET total_paquetes = :total, ultima_actualizacion = NOW() WHERE id = :id")
        ->execute([":total" => $total, ":id" => $idRecepcion]);

      $conexion->commit();
      return $idCaja;
    } catch (Throwable $error) {
      $conexion->rollBack();
      throw $error;
    }
  }

  private static function mdlInsertarPaquetes($conexion, $paquetes, $tipo, $empresa, $idRecepcion, $lote, $idCaja = null)
  {
    $stmt = $conexion->prepare(
      "INSERT INTO encomiendas
      (cliente, celular, tipo, empresa, observaciones, estado, precio, precio_base, pagado_por, recepcion_id, lote_recepcion, caja_id)
      VALUES (:cliente, :celular, :tipo, :empresa, :observaciones, 'Pendiente', :precio, :precio_base, :pagado_por, :recepcion, :lote, :caja)"
    );
    foreach ($paquetes as $paquete) {
      $stmt->execute([
        ":cliente" => $paquete["cliente"],
        ":celular" => $paquete["celular"],
        ":tipo" => $tipo,
        ":empresa" => $empresa,
        ":observaciones" => $paquete["detalle"],
        ":precio" => $paquete["precio"],
        ":precio_base" => $paquete["precio"],
        ":pagado_por" => $paquete["pagado_por"],
        ":recepcion" => $idRecepcion,
        ":lote" => $lote,
        ":caja" => $idCaja
      ]);
    }
  }

  private static function mdlCantidadPaquetesCaja($conexion, $idCaja)
  {
    $stmt = $conexion->prepare("SELECT COUNT(*) FROM encomiendas WHERE caja_id = :id");
    $stmt->execute([":id" => $idCaja]);
    return (int) $stmt->fetchColumn();
  }
  static public function mdlCajasTikTokActivas()
  {
    $stmt = Conexion::conectar()->prepare(
      "SELECT
        cliente.id,
        cliente.codigo,
        cliente.nombre_tiktok,
        cliente.propietaria,
        cliente.celular,
        cliente.whatsapp,
        cliente.observaciones,
        COUNT(encomienda.id) AS total_historico,
        COALESCE(SUM(encomienda.estado = 'Pendiente'), 0) AS pendientes
      FROM cajas_clientes cliente
      LEFT JOIN cajas_tiktok caja ON caja.empresa = cliente.nombre_tiktok
      LEFT JOIN encomiendas encomienda ON encomienda.caja_id = caja.id
      WHERE cliente.activo = 1
      GROUP BY
        cliente.id,
        cliente.codigo,
        cliente.nombre_tiktok,
        cliente.propietaria,
        cliente.celular,
        cliente.whatsapp,
        cliente.observaciones
      ORDER BY cliente.nombre_tiktok"
    );
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }

  static public function mdlHistorialCajas()
  {
    $stmt = Conexion::conectar()->prepare(
      "SELECT
        caja.id,
        caja.codigo,
        caja.empresa,
        caja.fecha,
        caja.total_paquetes,
        caja.estado,
        COALESCE(SUM(encomienda.estado = 'Pendiente'), 0) AS pendientes,
        COALESCE(SUM(encomienda.estado = 'Entregado'), 0) AS entregados
      FROM cajas_tiktok caja
      LEFT JOIN encomiendas encomienda ON encomienda.caja_id = caja.id
      GROUP BY caja.id, caja.codigo, caja.empresa, caja.fecha, caja.total_paquetes, caja.estado
      ORDER BY caja.fecha DESC"
    );
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }

  static public function mdlComprobanteCaja($idCaja)
  {
    $conexion = Conexion::conectar();

    $stmtCaja = $conexion->prepare(
      "SELECT
        recepcion.*,
        caja.empresa,
        cliente.propietaria,
        cliente.whatsapp,
        caja.codigo
      FROM cajas_tiktok caja
      LEFT JOIN recepciones recepcion ON recepcion.id = caja.recepcion_id
      LEFT JOIN cajas_clientes cliente ON cliente.nombre_tiktok = caja.empresa
      WHERE caja.id = :id
      LIMIT 1"
    );
    $stmtCaja->bindValue(":id", $idCaja, PDO::PARAM_INT);
    $stmtCaja->execute();
    $caja = $stmtCaja->fetch(PDO::FETCH_ASSOC);

    if ($caja === false) {
      return null;
    }

    $stmtPaquetes = $conexion->prepare(
      "SELECT *
      FROM encomiendas
      WHERE caja_id = :id
      ORDER BY lote_recepcion ASC, fecha_registro ASC"
    );
    $stmtPaquetes->bindValue(":id", $idCaja, PDO::PARAM_INT);
    $stmtPaquetes->execute();
    $paquetes = $stmtPaquetes->fetchAll(PDO::FETCH_ASSOC);

    $lotes = [];
    foreach ($paquetes as $paquete) {
      $lote = (int) $paquete["lote_recepcion"];
      if (!isset($lotes[$lote])) {
        $lotes[$lote] = [
          "cantidad" => 0,
          "hora_inicio" => $paquete["fecha_registro"]
        ];
      }
      $lotes[$lote]["cantidad"]++;
    }

    $resumen = [
      "total" => count($paquetes),
      "pendientes" => count(array_filter($paquetes, fn($paquete) => $paquete["estado"] === "Pendiente")),
      "entregados" => count(array_filter($paquetes, fn($paquete) => $paquete["estado"] === "Entregado"))
    ];

    return [
      "caja" => $caja,
      "paquetes" => $paquetes,
      "lotes" => $lotes,
      "ultimoLote" => empty($lotes) ? 0 : max(array_keys($lotes)),
      "resumen" => $resumen
    ];
  }

  static public function mdlComprobanteRecepcion($idRecepcion)
  {
    $conexion = Conexion::conectar();
    $stmtRecepcion = $conexion->prepare(
      "SELECT recepcion.*,
        cliente.nombre AS nombre_cliente,
        almacen.nombre_almacen,
        usuario.nombre AS nombre_usuario
      FROM recepciones recepcion
      LEFT JOIN clientes cliente ON cliente.id = recepcion.id_cliente
      LEFT JOIN almacen ON almacen.id_almacen = recepcion.id_almacen
      LEFT JOIN usuario ON usuario.id_usuario = recepcion.id_usuario_recepcion
      WHERE recepcion.id = :id
      LIMIT 1"
    );
    $stmtRecepcion->execute([":id" => $idRecepcion]);
    $recepcion = $stmtRecepcion->fetch(PDO::FETCH_ASSOC);
    if ($recepcion === false) {
      return null;
    }

    $stmtPaquetes = $conexion->prepare(
      "SELECT * FROM encomiendas WHERE id_recepcion = :id ORDER BY fecha_registro ASC"
    );
    $stmtPaquetes->execute([":id" => $idRecepcion]);

    return [
      "recepcion" => $recepcion,
      "paquetes" => $stmtPaquetes->fetchAll(PDO::FETCH_ASSOC)
    ];
  }
}
