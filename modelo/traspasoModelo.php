<?php

require_once "conexion.php";

class ModeloTraspaso
{
  public static function mdlListar($idAlmacen)
  {
    $stmt = Conexion::conectar()->prepare(
      "SELECT t.*, ao.nombre_almacen AS almacen_origen,
          ad.nombre_almacen AS almacen_destino,
          e.codigo AS codigo_encomienda, e.destinatario,
          u.nombre AS usuario
       FROM traspaso t
       INNER JOIN almacen ao ON ao.id_almacen = t.id_almacen_origen
       INNER JOIN almacen ad ON ad.id_almacen = t.id_almacen_destino
       LEFT JOIN encomiendas e ON e.id = t.id_encomienda
       INNER JOIN usuario u ON u.id_usuario = t.id_usuario
       WHERE t.id_almacen_origen = :almacen_origen
          OR t.id_almacen_destino = :almacen_destino
       ORDER BY t.fecha_traspaso DESC, t.id DESC"
    );
    $stmt->execute([
      ":almacen_origen" => $idAlmacen,
      ":almacen_destino" => $idAlmacen
    ]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }

  public static function mdlEncomiendasDisponibles($idAlmacen)
  {
    $stmt = Conexion::conectar()->prepare(
      "SELECT e.id, e.codigo, e.destinatario, e.descripcion,
          e.estado, e.id_almacen_actual
       FROM encomiendas e
       WHERE e.id_almacen_actual = :almacen
         AND e.estado = 'Pendiente'
       ORDER BY e.fecha_registro DESC, e.id DESC"
    );
    $stmt->execute([":almacen" => $idAlmacen]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }

  public static function mdlAlmacenesActivos($idExcluir)
  {
    $stmt = Conexion::conectar()->prepare(
      "SELECT id_almacen, nombre_almacen, ciudad
       FROM almacen
       WHERE estado_almacen = 1 AND id_almacen <> :id
       ORDER BY nombre_almacen ASC"
    );
    $stmt->execute([":id" => $idExcluir]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }

  public static function mdlRegistrar($datos)
  {
    $conexion = Conexion::conectar();
    $conexion->beginTransaction();
    try {
      $stmt = $conexion->prepare(
        "SELECT id, estado, id_almacen_actual
         FROM encomiendas
         WHERE id = :id
         FOR UPDATE"
      );
      $stmt->execute([":id" => $datos["id_encomienda"]]);
      $encomienda = $stmt->fetch(PDO::FETCH_ASSOC);
      if ($encomienda === false) {
        throw new InvalidArgumentException("La encomienda no existe.");
      }
      if ($encomienda["estado"] !== "Pendiente") {
        throw new InvalidArgumentException("Solo se pueden traspasar encomiendas pendientes.");
      }
      if ((int) $encomienda["id_almacen_actual"] !== (int) $datos["id_almacen_origen"]) {
        throw new InvalidArgumentException("La encomienda no pertenece al almacén de origen.");
      }

      $stmt = $conexion->prepare(
        "SELECT 1 FROM almacen
         WHERE id_almacen = :id AND estado_almacen = 1
         LIMIT 1"
      );
      $stmt->execute([":id" => $datos["id_almacen_destino"]]);
      if ($stmt->fetchColumn() === false) {
        throw new InvalidArgumentException("El almacén de destino no está disponible.");
      }

      $codigo = "TR-" . strtoupper(bin2hex(random_bytes(6)));
      $stmt = $conexion->prepare(
        "INSERT INTO traspaso
         (codigo, id_almacen_origen, id_almacen_destino, id_encomienda,
          concepto, id_usuario, estado)
         VALUES (:codigo, :origen, :destino, :encomienda,
          :concepto, :usuario, 'ENVIADO')"
      );
      $stmt->execute([
        ":codigo" => $codigo,
        ":origen" => $datos["id_almacen_origen"],
        ":destino" => $datos["id_almacen_destino"],
        ":encomienda" => $datos["id_encomienda"],
        ":concepto" => $datos["concepto"],
        ":usuario" => $datos["id_usuario"]
      ]);

      $stmt = $conexion->prepare(
        "UPDATE encomiendas
         SET id_almacen_actual = :destino
         WHERE id = :id AND id_almacen_actual = :origen
           AND estado = 'Pendiente'"
      );
      $stmt->execute([
        ":destino" => $datos["id_almacen_destino"],
        ":id" => $datos["id_encomienda"],
        ":origen" => $datos["id_almacen_origen"]
      ]);
      if ($stmt->rowCount() !== 1) {
        throw new RuntimeException("No se pudo actualizar la ubicación de la encomienda.");
      }

      $conexion->commit();
      return $codigo;
    } catch (Throwable $error) {
      if ($conexion->inTransaction()) {
        $conexion->rollBack();
      }
      throw $error;
    }

    }

    public static function mdlRegistrarMultiple($ids, $idAlmacenOrigen, $idAlmacenDestino, $concepto, $idUsuario)
    {
      $conexion = Conexion::conectar();
      $conexion->beginTransaction();
      try {
        $stmt = $conexion->prepare(
          "SELECT id, estado, id_almacen_actual
           FROM encomiendas WHERE id = :id FOR UPDATE"
        );
        $insertar = $conexion->prepare(
          "INSERT INTO traspaso
           (codigo, id_almacen_origen, id_almacen_destino, id_encomienda, concepto, id_usuario, estado)
           VALUES (:codigo, :origen, :destino, :encomienda, :concepto, :usuario, 'ENVIADO')"
        );
        $actualizar = $conexion->prepare(
          "UPDATE encomiendas SET id_almacen_actual = :destino
           WHERE id = :id AND id_almacen_actual = :origen AND estado = 'Pendiente'"
        );
        foreach ($ids as $id) {
          $stmt->execute([":id" => $id]);
          $encomienda = $stmt->fetch(PDO::FETCH_ASSOC);
          if (!$encomienda || $encomienda["estado"] !== "Pendiente" ||
            (int) $encomienda["id_almacen_actual"] !== (int) $idAlmacenOrigen) {
            throw new InvalidArgumentException("Una encomienda ya no está disponible en este almacén.");
          }
          $codigo = "TR-" . strtoupper(bin2hex(random_bytes(6)));
          $insertar->execute([
            ":codigo" => $codigo,
            ":origen" => $idAlmacenOrigen,
            ":destino" => $idAlmacenDestino,
            ":encomienda" => $id,
            ":concepto" => $concepto,
            ":usuario" => $idUsuario
          ]);
          $actualizar->execute([
            ":destino" => $idAlmacenDestino,
            ":id" => $id,
            ":origen" => $idAlmacenOrigen
          ]);
          if ($actualizar->rowCount() !== 1) {
            throw new RuntimeException("No se pudo actualizar la ubicación de una encomienda.");
          }
        }
        $conexion->commit();
      } catch (Throwable $error) {
        if ($conexion->inTransaction()) {
          $conexion->rollBack();
        }
        throw $error;
      }
    }
  }
