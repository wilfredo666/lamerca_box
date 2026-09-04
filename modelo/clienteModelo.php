<?php

require_once "conexion.php";

class ModeloCliente
{
  public static function mdlListar()
  {
    $stmt = Conexion::conectar()->prepare(
      "SELECT id, nombre, celular, observaciones, pais, ciudad, activo, fecha_registro
      FROM clientes
      ORDER BY nombre ASC, id ASC"
    );
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }

  public static function mdlCrear($datos)
  {
    $stmt = Conexion::conectar()->prepare(
      "INSERT INTO clientes (nombre, celular, observaciones, pais, ciudad, activo)
      VALUES (:nombre, :celular, :observaciones, :pais, :ciudad, 1)"
    );
    return $stmt->execute($datos);
  }

  public static function mdlActualizar($id, $datos)
  {
    $datos[":id"] = $id;
    $stmt = Conexion::conectar()->prepare(
      "UPDATE clientes
      SET nombre = :nombre, celular = :celular, observaciones = :observaciones,
          pais = :pais, ciudad = :ciudad
      WHERE id = :id"
    );
    return $stmt->execute($datos);
  }

  public static function mdlCambiarEstado($id, $activo)
  {
    $stmt = Conexion::conectar()->prepare(
      "UPDATE clientes SET activo = :activo WHERE id = :id"
    );
    return $stmt->execute([":activo" => $activo, ":id" => $id]);
  }

  public static function mdlEliminar($id)
  {
    $stmt = Conexion::conectar()->prepare("DELETE FROM clientes WHERE id = :id");
    return $stmt->execute([":id" => $id]);
  }
}
