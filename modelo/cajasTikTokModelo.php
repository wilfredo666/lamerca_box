<?php

require_once "conexion.php";

class ModeloCajasTikTok
{
  static public function mdlCrear($datos)
  {
    $conexion = Conexion::conectar();
    $stmt = $conexion->prepare(
      "INSERT INTO cajas_clientes (codigo, nombre_tiktok, propietaria, whatsapp, observaciones, activo)
      VALUES ('', :nombre, :propietaria, :whatsapp, :observaciones, 1)"
    );
    $stmt->execute($datos);
    $id = (int) $conexion->lastInsertId();
    $conexion->prepare("UPDATE cajas_clientes SET codigo = :codigo WHERE id = :id")
      ->execute([":codigo" => "CT-" . str_pad((string) $id, 6, "0", STR_PAD_LEFT), ":id" => $id]);
    return $id;
  }

  static public function mdlObtener($id)
  {
    $stmt = Conexion::conectar()->prepare("SELECT * FROM cajas_clientes WHERE id = :id LIMIT 1");
    $stmt->execute([":id" => $id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
  }

  static public function mdlActualizar($id, $datos)
  {
    $datos[":id"] = $id;
    $stmt = Conexion::conectar()->prepare(
      "UPDATE cajas_clientes SET nombre_tiktok = :nombre, propietaria = :propietaria,
      whatsapp = :whatsapp, observaciones = :observaciones WHERE id = :id"
    );
    return $stmt->execute($datos);
  }
}
