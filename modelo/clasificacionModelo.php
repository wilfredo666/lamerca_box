<?php
require_once "conexion.php";

class ModeloClasificacion
{
  public static function mdlMostrarRegistros()
  {
    $stmt = Conexion::conectar()->query(
      "SELECT * FROM clasificacion ORDER BY descripcion ASC"
    );
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }

  public static function mdlInfo($id)
  {
    $stmt = Conexion::conectar()->prepare(
      "SELECT * FROM clasificacion WHERE id = :id"
    );
    $stmt->execute([":id" => $id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
  }

  public static function mdlRegistrar($datos)
  {
    $stmt = Conexion::conectar()->prepare(
      "INSERT INTO clasificacion (descripcion, estado, fecha_creacion)
       VALUES (:descripcion, :estado, NOW())"
    );
    return $stmt->execute([
      ":descripcion" => $datos["descripcion"],
      ":estado" => $datos["estado"]
    ]);
  }

  public static function mdlActualizar($id, $datos)
  {
    $stmt = Conexion::conectar()->prepare(
      "UPDATE clasificacion
       SET descripcion = :descripcion, estado = :estado
       WHERE id = :id"
    );
    return $stmt->execute([
      ":descripcion" => $datos["descripcion"],
      ":estado" => $datos["estado"],
      ":id" => $id
    ]);
  }

  public static function mdlEliminar($id)
  {
    $pdo = Conexion::conectar();
    $clasificacion = self::mdlInfo($id);
    if (!$clasificacion) {
      throw new InvalidArgumentException("La clasificación no existe.");
    }
    $stmt = $pdo->prepare(
      "SELECT COUNT(*) FROM encomiendas WHERE clasificacion = :descripcion"
    );
    $stmt->execute([":descripcion" => $clasificacion["descripcion"]]);
    if ((int) $stmt->fetchColumn() > 0) {
      throw new InvalidArgumentException("No se puede eliminar una clasificación utilizada. Márquela como inactiva.");
    }

    $stmt = $pdo->prepare("DELETE FROM clasificacion WHERE id = :id");
    $stmt->execute([":id" => $id]);
    if ($stmt->rowCount() !== 1) {
      throw new InvalidArgumentException("La clasificación no existe o ya fue eliminada.");
    }
  }
}
