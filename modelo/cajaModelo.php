<?php
require_once "conexion.php";

class ModeloCaja
{
  public static function mdlMovimientos($idAlmacen, $fechaDesde, $fechaHasta, $idUsuario = null)
  {
    $filtroUsuario = $idUsuario !== null ? " AND c.id_usuario = :id_usuario" : "";
    $stmt = Conexion::conectar()->prepare(
      "SELECT c.*, u.nombre AS nombre_usuario
       FROM caja c
       INNER JOIN usuario u ON u.id_usuario = c.id_usuario
       WHERE c.id_almacen = :id_almacen
         AND c.fecha_movimiento >= :fecha_desde
         AND c.fecha_movimiento < DATE_ADD(:fecha_hasta, INTERVAL 1 DAY)
         " . $filtroUsuario . "
       ORDER BY c.fecha_movimiento DESC, c.id_caja DESC"
    );
    $parametros = [
      ":id_almacen" => $idAlmacen,
      ":fecha_desde" => $fechaDesde . " 00:00:00",
      ":fecha_hasta" => $fechaHasta
    ];
    if ($idUsuario !== null) {
      $parametros[":id_usuario"] = $idUsuario;
    }
    $stmt->execute($parametros);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }

  public static function mdlUsuariosConMovimientos($idAlmacen)
  {
    $stmt = Conexion::conectar()->prepare(
      "SELECT DISTINCT u.id_usuario, u.nombre
       FROM caja c
       INNER JOIN usuario u ON u.id_usuario = c.id_usuario
       WHERE c.id_almacen = :id_almacen
       ORDER BY u.nombre ASC, u.id_usuario ASC"
    );
    $stmt->execute([":id_almacen" => $idAlmacen]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }

  public static function mdlResumen($idAlmacen, $fechaDesde = null, $fechaHasta = null, $idUsuario = null)
  {
    $filtros = "";
    $parametros = [":id_almacen" => $idAlmacen];
    if ($fechaDesde !== null && $fechaHasta !== null) {
      $filtros .= " AND fecha_movimiento >= :fecha_desde AND fecha_movimiento < DATE_ADD(:fecha_hasta, INTERVAL 1 DAY)";
      $parametros[":fecha_desde"] = $fechaDesde . " 00:00:00";
      $parametros[":fecha_hasta"] = $fechaHasta;
    }
    if ($idUsuario !== null) {
      $filtros .= " AND id_usuario = :id_usuario";
      $parametros[":id_usuario"] = $idUsuario;
    }
    $stmt = Conexion::conectar()->prepare(
      "SELECT
        COALESCE(SUM(CASE WHEN tipo = 'Ingreso' AND estado_caja = 1 THEN cantidad ELSE 0 END), 0) AS total_ingresos,
        COALESCE(SUM(CASE WHEN tipo = 'Salida' AND estado_caja = 1 THEN cantidad ELSE 0 END), 0) AS total_salidas
       FROM caja
       WHERE id_almacen = :id_almacen" . $filtros
    );
    $stmt->execute($parametros);
    $resumen = $stmt->fetch(PDO::FETCH_ASSOC);
    $resumen["saldo_actual"] = (float) $resumen["total_ingresos"] - (float) $resumen["total_salidas"];
    return $resumen;
  }

  public static function mdlPagosPorMetodo($idAlmacen, $fechaDesde, $fechaHasta, $idUsuario = null)
  {
    $filtroUsuario = $idUsuario !== null ? " AND id_usuario = :id_usuario" : "";
    $stmt = Conexion::conectar()->prepare(
      "SELECT
        COALESCE(SUM(CASE WHEN metodo_cobro = 'QR' THEN total_cobrado ELSE 0 END), 0) AS qr,
        COALESCE(SUM(CASE WHEN metodo_cobro = 'Efectivo' THEN total_cobrado ELSE 0 END), 0) AS efectivo
       FROM entrega
       WHERE estado = 'Entregado' AND id_almacen = :id_almacen
         AND fecha_entrega >= :fecha_desde
         AND fecha_entrega < DATE_ADD(:fecha_hasta, INTERVAL 1 DAY)" . $filtroUsuario
    );
    $parametros = [
      ":id_almacen" => $idAlmacen,
      ":fecha_desde" => $fechaDesde . " 00:00:00",
      ":fecha_hasta" => $fechaHasta
    ];
    if ($idUsuario !== null) {
      $parametros[":id_usuario"] = $idUsuario;
    }
    $stmt->execute($parametros);
    $pagos = $stmt->fetch(PDO::FETCH_ASSOC);

    // Pagos del remitente: se cuentan el día de la recepción, no el de la entrega
    $filtroRecepcionista = $idUsuario !== null ? " AND r.id_usuario_recepcion = :id_usuario" : "";
    $stmtRemitente = Conexion::conectar()->prepare(
      "SELECT
        COALESCE(SUM(CASE WHEN e.medio_pago_remitente = 'QR' THEN e.precio ELSE 0 END), 0) AS qr,
        COALESCE(SUM(CASE WHEN e.medio_pago_remitente = 'Efectivo' THEN e.precio ELSE 0 END), 0) AS efectivo
       FROM encomiendas e
       INNER JOIN recepciones r ON r.id = e.id_recepcion
       WHERE e.quien_paga = 'Remitente' AND e.cobrado = 1 AND e.estado <> 'Eliminado'
         AND r.id_almacen = :id_almacen
         AND e.fecha_registro >= :fecha_desde
         AND e.fecha_registro < DATE_ADD(:fecha_hasta, INTERVAL 1 DAY)" . $filtroRecepcionista
    );
    $stmtRemitente->execute($parametros);
    $remitente = $stmtRemitente->fetch(PDO::FETCH_ASSOC);

    return [
      "qr" => (float) $pagos["qr"] + (float) $remitente["qr"],
      "efectivo" => (float) $pagos["efectivo"] + (float) $remitente["efectivo"]
    ];
  }

  public static function mdlRegistrar($datos)
  {
    $stmt = Conexion::conectar()->prepare(
      "INSERT INTO caja (tipo, concepto, descripcion, cantidad, id_usuario, id_almacen, fecha_movimiento)
       VALUES (:tipo, :concepto, :descripcion, :cantidad, :id_usuario, :id_almacen, :fecha_movimiento)"
    );
    $stmt->execute($datos);
  }

  public static function mdlAnular($idCaja, $idAlmacen)
  {
    $stmt = Conexion::conectar()->prepare(
      "UPDATE caja SET estado_caja = 0
       WHERE id_caja = :id_caja AND id_almacen = :id_almacen AND estado_caja = 1"
    );
    $stmt->execute([":id_caja" => $idCaja, ":id_almacen" => $idAlmacen]);
    if ($stmt->rowCount() !== 1) {
      throw new InvalidArgumentException("El movimiento no existe o ya fue anulado.");
    }
  }
}
