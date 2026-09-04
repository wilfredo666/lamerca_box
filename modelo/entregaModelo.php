<?php

require_once "conexion.php";

class ModeloEntrega
{
  public static function mdlPaquetesPorEstado($estado)
  {
    $stmt = Conexion::conectar()->prepare(
      "SELECT e.*, r.codigo AS codigo_recepcion, r.tipo_recepcion AS tipo,
          r.empresa, r.fecha_registro AS fecha_recepcion,
          c.nombre AS cliente, c.celular AS celular,
          e.descripcion AS observaciones
       FROM encomiendas e
       INNER JOIN recepciones r ON r.id = e.id_recepcion
       LEFT JOIN clientes c ON c.id = r.id_cliente
       WHERE e.estado = :estado
       ORDER BY e.fecha_registro DESC, e.id DESC"
    );
    $stmt->execute([":estado" => $estado]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }

  public static function mdlEntregasRegistradas()
  {
    $stmt = Conexion::conectar()->prepare(
      "SELECT en.id, en.codigo, en.destinatario, en.descripcion,
          r.tipo_recepcion AS tipo, c.nombre AS cliente,
          et.fecha_entrega, et.recargo, et.descuento,
          et.total_cobrado, et.metodo_cobro, et.estado
       FROM entrega et
       INNER JOIN encomiendas en ON en.id = et.id_encomienda
       INNER JOIN recepciones r ON r.id = en.id_recepcion
       LEFT JOIN clientes c ON c.id = r.id_cliente
       WHERE et.estado = 'Entregado'
       ORDER BY et.fecha_entrega DESC, et.id DESC"
    );
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }

  public static function mdlFotosPendientes()
  {
    $stmt = Conexion::conectar()->prepare(
      "SELECT e.*, r.codigo AS codigo_recepcion, r.tipo_recepcion AS tipo,
          r.empresa, r.fecha_registro AS fecha_recepcion,
          c.nombre AS cliente, c.celular AS celular,
          e.descripcion AS observaciones
       FROM encomiendas e
       INNER JOIN recepciones r ON r.id = e.id_recepcion
       LEFT JOIN clientes c ON c.id = r.id_cliente
       WHERE e.estado = 'Pendiente' AND (e.foto IS NULL OR e.foto = '')
       ORDER BY e.fecha_registro DESC, e.id DESC"
    );
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }

  public static function mdlPaquete($id)
  {
    $stmt = Conexion::conectar()->prepare(
      "SELECT e.*, r.codigo AS codigo_recepcion, r.tipo_recepcion AS tipo,
          r.empresa, r.fecha_registro AS fecha_recepcion,
          c.nombre AS cliente, c.celular AS celular,
          e.descripcion AS observaciones
       FROM encomiendas e
       INNER JOIN recepciones r ON r.id = e.id_recepcion
       LEFT JOIN clientes c ON c.id = r.id_cliente
       WHERE e.id = :id LIMIT 1"
    );
    $stmt->execute([":id" => $id]);
    return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
  }

  public static function mdlRegistrarEntregaIndividual($id, $medioCobro)
  {
    $conexion = Conexion::conectar();
    $conexion->beginTransaction();
    try {
      $paquete = self::mdlPaqueteBloqueado($conexion, $id);
      if ($paquete === null || $paquete["estado"] !== "Pendiente") {
        throw new InvalidArgumentException("El paquete ya no está disponible para entrega.");
      }

      $cobro = self::mdlCobroPaquete($paquete);
      $idUsuario = filter_var($_SESSION["idUsuario"] ?? null, FILTER_VALIDATE_INT);
      $idAlmacen = filter_var($_SESSION["idAlmacen"] ?? null, FILTER_VALIDATE_INT);
      if (!$idUsuario || !$idAlmacen) {
        throw new RuntimeException("No se encontró el usuario o almacén de la sesión.");
      }
      $stmt = $conexion->prepare(
        "INSERT INTO entrega
         (id_encomienda, id_usuario, id_almacen, recargo, descuento, total_cobrado, metodo_cobro, estado)
         VALUES (:id_encomienda, :id_usuario, :id_almacen, :recargo, 0, :total, :medio, 'Entregado')"
      );
      $stmt->execute([
        ":id_encomienda" => $id,
        ":id_usuario" => $idUsuario,
        ":id_almacen" => $idAlmacen,
        ":recargo" => $cobro["recargo"],
        ":total" => $cobro["total"],
        ":medio" => $medioCobro
      ]);
      $stmt = $conexion->prepare("UPDATE encomiendas SET estado = 'Entregado', cobrado = 1 WHERE id = :id AND estado = 'Pendiente'");
      $stmt->execute([":id" => $id]);
      if ($stmt->rowCount() !== 1) {
        throw new RuntimeException("No se pudo actualizar el paquete.");
      }
      $conexion->commit();
      return $cobro;
    } catch (Throwable $error) {
      if ($conexion->inTransaction()) {
        $conexion->rollBack();
      }
      throw $error;
    }
  }

  public static function mdlRetirarPaquete($id, $motivo)
  {
    $stmt = Conexion::conectar()->prepare(
      "UPDATE encomiendas
       SET estado = 'Retirado', fecha_retiro = NOW(), motivo_retiro = :motivo
       WHERE id = :id AND estado = 'Pendiente'"
    );
    $stmt->execute([":id" => $id, ":motivo" => $motivo]);
    if ($stmt->rowCount() !== 1) {
      throw new InvalidArgumentException("El paquete ya no está disponible para retiro.");
    }
  }

  public static function mdlActualizarFoto($id, $foto)
  {
    $stmt = Conexion::conectar()->prepare(
      "UPDATE encomiendas SET foto = :foto
       WHERE id = :id AND estado = 'Pendiente'"
    );
    $stmt->execute([":foto" => $foto, ":id" => $id]);
    if ($stmt->rowCount() !== 1) {
      throw new InvalidArgumentException("El paquete ya no está disponible para actualizar.");
    }
  }

  public static function mdlRegistrarEntregaMultiple($ids, $recargo, $descuento, $medioCobro)
  {
    $conexion = Conexion::conectar();
    $conexion->beginTransaction();
    try {
      $paquetes = [];
      foreach ($ids as $id) {
        $paquete = self::mdlPaqueteBloqueado($conexion, $id);
        if ($paquete === null || $paquete["estado"] !== "Pendiente") {
          throw new InvalidArgumentException("Uno o más paquetes ya no están disponibles.");
        }
        $paquetes[] = $paquete;
      }

      $cobros = array_map(fn($paquete) => self::mdlCobroPaquete($paquete, false), $paquetes);
      $subtotalCentavos = array_sum(array_column($cobros, "centavos"));
      $recargoCentavos = (int) round($recargo * 100);
      $descuentoCentavos = (int) round($descuento * 100);
      if ($recargoCentavos < 0 || $descuentoCentavos < 0 || $descuentoCentavos > $subtotalCentavos + $recargoCentavos) {
        throw new InvalidArgumentException("El descuento no es válido.");
      }

      $idUsuario = filter_var($_SESSION["idUsuario"] ?? null, FILTER_VALIDATE_INT);
      $idAlmacen = filter_var($_SESSION["idAlmacen"] ?? null, FILTER_VALIDATE_INT);
      if (!$idUsuario || !$idAlmacen) {
        throw new RuntimeException("No se encontró el usuario o almacén de la sesión.");
      }
      $stmtEntrega = $conexion->prepare(
        "INSERT INTO entrega
         (id_encomienda, id_usuario, id_almacen, recargo, descuento, total_cobrado, metodo_cobro, estado)
         VALUES (:id_encomienda, :id_usuario, :id_almacen, :recargo, :descuento, :total, :medio, 'Entregado')"
      );
      $stmtEstado = $conexion->prepare(
        "UPDATE encomiendas SET estado = 'Entregado', cobrado = 1
         WHERE id = :id AND estado = 'Pendiente'"
      );

      $descuentoRestante = $descuentoCentavos;
      $recargoRestante = $recargoCentavos;
      $subtotalRestante = $subtotalCentavos;
      foreach ($paquetes as $indice => $paquete) {
        $cobro = $cobros[$indice];
        $recargoPaquete = $indice === count($paquetes) - 1
          ? $recargoRestante
          : (int) floor($recargoRestante * $cobro["centavos"] / $subtotalRestante);
        $descuentoPaquete = $indice === count($paquetes) - 1
          ? $descuentoRestante
          : (int) floor($descuentoRestante * $cobro["centavos"] / $subtotalRestante);
        $totalCentavos = $cobro["centavos"] + $recargoPaquete - $descuentoPaquete;
        $stmtEntrega->execute([
          ":id_encomienda" => $paquete["id"],
          ":id_usuario" => $idUsuario,
          ":id_almacen" => $idAlmacen,
          ":recargo" => $recargoPaquete / 100,
          ":descuento" => $descuentoPaquete / 100,
          ":total" => $totalCentavos / 100,
          ":medio" => $medioCobro
        ]);
        $stmtEstado->execute([":id" => $paquete["id"]]);
        if ($stmtEstado->rowCount() !== 1) {
          throw new RuntimeException("No se pudo actualizar un paquete.");
        }
        $recargoRestante -= $recargoPaquete;
        $descuentoRestante -= $descuentoPaquete;
        $subtotalRestante -= $cobro["centavos"];
      }
      $conexion->commit();
      return true;
    } catch (Throwable $error) {
      if ($conexion->inTransaction()) {
        $conexion->rollBack();
      }
      throw $error;
    }
  }

  private static function mdlPaqueteBloqueado($conexion, $id)
  {
    $stmt = $conexion->prepare("SELECT * FROM encomiendas WHERE id = :id FOR UPDATE");
    $stmt->execute([":id" => $id]);
    return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
  }

  private static function mdlCobroPaquete($paquete, $aplicarRecargo = true)
  {
    $precioBase = (float) ($paquete["precio_base"] ?? 0);
    if ($precioBase <= 0) {
      $precioBase = (float) ($paquete["precio"] ?? 2);
    }
    if ($precioBase <= 0) {
      $precioBase = 2;
    }
    $dias = max(0, (new DateTime($paquete["fecha_registro"]))->diff(new DateTime())->days);
    $recargo = $aplicarRecargo && $dias > 7 ? 1.0 : 0.0;
    $centavos = (int) round(($precioBase + $recargo) * 100);
    return ["precio_base" => $precioBase, "recargo" => $recargo, "total" => $centavos / 100, "centavos" => $centavos];
  }
}
