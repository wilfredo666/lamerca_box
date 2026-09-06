<?php
require_once "conexion.php";

class ModeloCaja
{
  public static function mdlMovimientos($idAlmacen)
  {
    $stmt = Conexion::conectar()->prepare(
      "SELECT c.*, u.nombre AS nombre_usuario
       FROM caja c
       INNER JOIN usuario u ON u.id_usuario = c.id_usuario
       WHERE c.id_almacen = :id_almacen
       ORDER BY c.fecha_movimiento DESC, c.id_caja DESC"
    );
    $stmt->execute([":id_almacen" => $idAlmacen]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }

  public static function mdlResumen($idAlmacen)
  {
    $stmt = Conexion::conectar()->prepare(
      "SELECT
        COALESCE(SUM(CASE WHEN tipo = 'Ingreso' AND estado_caja = 1 THEN cantidad ELSE 0 END), 0) AS total_ingresos,
        COALESCE(SUM(CASE WHEN tipo = 'Salida' AND estado_caja = 1 THEN cantidad ELSE 0 END), 0) AS total_salidas
       FROM caja
       WHERE id_almacen = :id_almacen"
    );
    $stmt->execute([":id_almacen" => $idAlmacen]);
    $resumen = $stmt->fetch(PDO::FETCH_ASSOC);
    $resumen["saldo_actual"] = (float) $resumen["total_ingresos"] - (float) $resumen["total_salidas"];
    return $resumen;
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
