<?php
require_once 'conexion.php'; 

class ModeloAlmacen {
  static public function mdlRegAlmacen($data) {
    $stmt = Conexion::conectar()->prepare(
      "INSERT INTO almacen (nombre_almacen, descripcion, ciudad, direccion, encargado, contacto)
       VALUES (:nombre, :descripcion, :ciudad, :direccion, :encargado, :contacto)"
    );
    return $stmt->execute([
      ":nombre" => $data["nombre"],
      ":descripcion" => $data["descripcion"],
      ":ciudad" => $data["ciudad"],
      ":direccion" => $data["direccion"],
      ":encargado" => $data["encargado"],
      ":contacto" => $data["contacto"]
    ]);
  }

  static public function mdlInfoAlmacen($id){
    $stmt = Conexion::conectar()->prepare("SELECT * FROM almacen WHERE id_almacen = :id");
    $stmt->bindValue(":id", $id, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetch(PDO::FETCH_ASSOC);
  }

  static public function mdlEditAlmacen($data) {
    $pdo = Conexion::conectar();

    $stmt = $pdo->prepare(
      "UPDATE almacen SET nombre_almacen = :nombre, descripcion = :descripcion, ciudad = :ciudad,
       direccion = :direccion, encargado = :encargado, contacto = :contacto,
       estado_almacen = :estado
       WHERE id_almacen = :id"
    );
    return $stmt->execute([
      ":nombre" => $data["nombre"],
      ":descripcion" => $data["descripcion"],
      ":ciudad" => $data["ciudad"],
      ":direccion" => $data["direccion"],
      ":encargado" => $data["encargado"],
      ":contacto" => $data["contacto"],
      ":estado" => $data["estado"],
      ":id" => $data["id"]
    ]);
  }

  static public function mdlEliAlmacen($id) {
    $stmt = Conexion::conectar()->prepare("DELETE FROM almacen WHERE id_almacen = :id");
    $stmt->bindParam(":id", $id, PDO::PARAM_INT);
    return $stmt->execute() ? 'ok' : $stmt->errorInfo();
  }

  static public function mdlMostrarRegistros() {
    $stmt = Conexion::conectar()->prepare("SELECT * FROM almacen");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }
}
?>