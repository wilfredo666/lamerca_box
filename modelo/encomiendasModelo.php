<?php

require_once "conexion.php";

class ModeloEncomiendas
{
  static public function mdlBuscar($termino = "")
  {
    $stmt = Conexion::conectar()->prepare(
      "SELECT e.*,
        r.codigo AS codigo_recepcion,
        r.tipo_recepcion,
        r.empresa,
        r.fecha_registro AS fecha_recepcion,
        c.nombre AS nombre_cliente,
        c.celular AS celular_cliente
      FROM encomiendas e
      INNER JOIN recepciones r ON r.id = e.id_recepcion
      INNER JOIN clientes c ON c.id = r.id_cliente
      WHERE e.estado = 'Pendiente'
      AND (
        e.codigo LIKE :termino_codigo OR
        e.destinatario LIKE :termino_destinatario OR
        e.contacto LIKE :termino_contacto OR
        c.nombre LIKE :termino_nombre OR
        c.celular LIKE :termino_celular
      )
      ORDER BY e.fecha_registro DESC, e.id DESC"
    );
    $valor = "%" . trim($termino) . "%";
    $stmt->execute([
      ":termino_codigo" => $valor,
      ":termino_destinatario" => $valor,
      ":termino_contacto" => $valor,
      ":termino_nombre" => $valor,
      ":termino_celular" => $valor
    ]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }

  static public function mdlBuscarPorId($id)
  {
    $stmt = Conexion::conectar()->prepare(
      "SELECT e.*, r.codigo AS codigo_recepcion, r.tipo_recepcion, r.empresa,
        c.nombre AS nombre_cliente
      FROM encomiendas e
      INNER JOIN recepciones r ON r.id = e.id_recepcion
      INNER JOIN clientes c ON c.id = r.id_cliente
      WHERE e.id = :id
      LIMIT 1"
    );
    $stmt->execute([":id" => $id]);
    return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
  }

  static public function mdlActualizar($id, $datos)
  {
    $stmt = Conexion::conectar()->prepare(
      "UPDATE encomiendas
      SET clasificacion = :clasificacion, descripcion = :descripcion,
        precio = :precio, destinatario = :destinatario, contacto = :contacto,
        quien_paga = :quien_paga
      WHERE id = :id"
    );
    $datos[":id"] = $id;
    return $stmt->execute($datos);
  }

  static public function mdlEliminar($id)
  {
    $conexion = Conexion::conectar();
    $stmtEntrega = $conexion->prepare("SELECT 1 FROM entrega WHERE id_encomienda = :id LIMIT 1");
    $stmtEntrega->execute([":id" => $id]);
    if ($stmtEntrega->fetchColumn() !== false) {
      throw new InvalidArgumentException("No se puede eliminar una encomienda que ya tiene una entrega.");
    }

    $stmt = $conexion->prepare("DELETE FROM encomiendas WHERE id = :id");
    $stmt->execute([":id" => $id]);
    if ($stmt->rowCount() !== 1) {
      throw new InvalidArgumentException("La encomienda no existe o ya fue eliminada.");
    }
  }

  static public function mdlTotalCobradoHoy()
  {
    $stmt = Conexion::conectar()->prepare(
      "SELECT COALESCE(SUM(total_cobrado), 0) AS total
      FROM entrega
      WHERE estado <> 'Anulado'
      AND DATE(fecha_entrega) = CURDATE()"
    );
    $stmt->execute();
    $resultado = $stmt->fetch();
    $stmt->closeCursor();

    return $resultado;
  }

  static public function mdlCantidadEncomiendasHoy()
  {
    $stmt = Conexion::conectar()->prepare(
      "SELECT COUNT(*) AS total
      FROM encomiendas
      WHERE DATE(fecha_registro) = CURDATE()"
    );
    $stmt->execute();
    $resultado = $stmt->fetch();
    $stmt->closeCursor();

    return $resultado;
  }

  static public function mdlCantidadPendientes()
  {
    $stmt = Conexion::conectar()->prepare(
      "SELECT COUNT(*) AS total
      FROM encomiendas
      WHERE estado = 'Pendiente'"
    );
    $stmt->execute();
    $resultado = $stmt->fetch();
    $stmt->closeCursor();

    return $resultado;
  }

  static public function mdlCantidadEntregadasHoy()
  {
    $stmt = Conexion::conectar()->prepare(
      "SELECT COUNT(*) AS total
      FROM entrega
      WHERE estado = 'Entregado'
      AND DATE(fecha_entrega) = CURDATE()"
    );
    $stmt->execute();
    $resultado = $stmt->fetch();
    $stmt->closeCursor();

    return $resultado;
  }
}