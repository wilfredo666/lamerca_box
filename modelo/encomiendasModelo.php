<?php

require_once "conexion.php";

class ModeloEncomiendas
{
  static public function mdlBuscar($termino = "", $idAlmacen = null, $pagina = 1, $limite = 12, $soloSinImagen = false)
  {
    $filtroAlmacen = $idAlmacen !== null ? " AND e.id_almacen_actual = :id_almacen" : "";
    $filtroSinImagen = $soloSinImagen ? " AND (e.foto IS NULL OR e.foto = '')" : "";
    $pagina = max(1, (int) $pagina);
    $limite = max(1, (int) $limite);
    $desplazamiento = ($pagina - 1) * $limite;
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
      WHERE e.estado = 'Pendiente'" . $filtroAlmacen . $filtroSinImagen . "
      AND (
        e.codigo LIKE :termino_codigo OR
        e.destinatario LIKE :termino_destinatario OR
        e.contacto LIKE :termino_contacto OR
        c.nombre LIKE :termino_nombre OR
        c.celular LIKE :termino_celular
      )
      ORDER BY e.fecha_registro DESC, e.id DESC
      LIMIT :limite OFFSET :desplazamiento"
    );
    $valor = "%" . trim($termino) . "%";
    $parametros = [
      ":termino_codigo" => $valor,
      ":termino_destinatario" => $valor,
      ":termino_contacto" => $valor,
      ":termino_nombre" => $valor,
      ":termino_celular" => $valor
    ];
    if ($idAlmacen !== null) {
      $parametros[":id_almacen"] = $idAlmacen;
    }
    foreach ($parametros as $nombre => $valorParametro) {
      $stmt->bindValue($nombre, $valorParametro);
    }
    $stmt->bindValue(":limite", $limite, PDO::PARAM_INT);
    $stmt->bindValue(":desplazamiento", $desplazamiento, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }

  static public function mdlContarBusqueda($termino = "", $idAlmacen = null, $soloSinImagen = false)
  {
    $filtroAlmacen = $idAlmacen !== null ? " AND e.id_almacen_actual = :id_almacen" : "";
    $filtroSinImagen = $soloSinImagen ? " AND (e.foto IS NULL OR e.foto = '')" : "";
    $stmt = Conexion::conectar()->prepare(
      "SELECT COUNT(*)
       FROM encomiendas e
       INNER JOIN recepciones r ON r.id = e.id_recepcion
       INNER JOIN clientes c ON c.id = r.id_cliente
       WHERE e.estado = 'Pendiente'" . $filtroAlmacen . $filtroSinImagen . "
       AND (
         e.codigo LIKE :termino_codigo OR
         e.destinatario LIKE :termino_destinatario OR
         e.contacto LIKE :termino_contacto OR
         c.nombre LIKE :termino_nombre OR
         c.celular LIKE :termino_celular
       )"
    );
    $valor = "%" . trim($termino) . "%";
    $parametros = [
      ":termino_codigo" => $valor,
      ":termino_destinatario" => $valor,
      ":termino_contacto" => $valor,
      ":termino_nombre" => $valor,
      ":termino_celular" => $valor
    ];
    if ($idAlmacen !== null) {
      $parametros[":id_almacen"] = $idAlmacen;
    }
    $stmt->execute($parametros);
    return (int) $stmt->fetchColumn();
  }

  static public function mdlContarSinImagen($termino = "", $idAlmacen = null)
  {
    return self::mdlContarBusqueda($termino, $idAlmacen, true);
  }

  static public function mdlBuscarPorId($id)
  {
    $stmt = Conexion::conectar()->prepare(
      "SELECT e.*, r.codigo AS codigo_recepcion, r.tipo_recepcion, r.empresa,
        c.nombre AS nombre_cliente, c.celular AS celular_remitente
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

  static public function mdlEliminar($id, $motivo, $idAlmacen)
  {
    $conexion = Conexion::conectar();
    $conexion->beginTransaction();
    try {
      $stmtEstado = $conexion->prepare(
        "SELECT estado, id_recepcion FROM encomiendas
         WHERE id = :id AND id_almacen_actual = :id_almacen
         FOR UPDATE"
      );
      $stmtEstado->execute([":id" => $id, ":id_almacen" => $idAlmacen]);
      $encomienda = $stmtEstado->fetch(PDO::FETCH_ASSOC);
      if ($encomienda === false || $encomienda["estado"] !== "Pendiente") {
        throw new InvalidArgumentException("La encomienda no existe, ya fue eliminada o no está pendiente.");
      }

      $stmtEntrega = $conexion->prepare("SELECT 1 FROM entrega WHERE id_encomienda = :id LIMIT 1");
      $stmtEntrega->execute([":id" => $id]);
      if ($stmtEntrega->fetchColumn() !== false) {
        throw new InvalidArgumentException("No se puede eliminar una encomienda que ya tiene una entrega.");
      }

      $stmt = $conexion->prepare(
        "UPDATE encomiendas
         SET estado = 'Eliminado', observacion = :observacion, fecha_actualizacion = NOW()
         WHERE id = :id AND estado = 'Pendiente'"
      );
      $stmt->execute([
        ":observacion" => $motivo,
        ":id" => $id
      ]);
      if ($stmt->rowCount() !== 1) {
        throw new InvalidArgumentException("No se pudo actualizar el estado de la encomienda.");
      }
      ModeloRecepcion::mdlCerrarRecepcionSiNoTienePendientes($conexion, $encomienda["id_recepcion"]);
      $conexion->commit();
    } catch (Throwable $error) {
      if ($conexion->inTransaction()) {
        $conexion->rollBack();
      }
      throw $error;
    }
  }

  static public function mdlEliminadas($idAlmacen = null)
  {
    $filtroAlmacen = $idAlmacen !== null ? " AND e.id_almacen_actual = :id_almacen" : "";
    $stmt = Conexion::conectar()->prepare(
      "SELECT e.*, r.tipo_recepcion AS tipo, r.empresa,
        r.fecha_registro AS fecha_recepcion,
        c.nombre AS cliente, c.celular AS celular,
        e.observacion AS motivo_eliminacion,
        e.fecha_actualizacion AS fecha_eliminacion
       FROM encomiendas e
       INNER JOIN recepciones r ON r.id = e.id_recepcion
       LEFT JOIN clientes c ON c.id = r.id_cliente
       WHERE e.estado = 'Eliminado'" . $filtroAlmacen . "
       ORDER BY e.fecha_actualizacion DESC, e.id DESC"
    );
    $parametros = [];
    if ($idAlmacen !== null) {
      $parametros[":id_almacen"] = $idAlmacen;
    }
    $stmt->execute($parametros);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }

  static public function mdlDetalleEliminada($id, $idAlmacen)
  {
    $stmt = Conexion::conectar()->prepare(
      "SELECT e.*, r.codigo AS codigo_recepcion, r.tipo_recepcion,
        r.empresa, r.fecha_registro AS fecha_recepcion,
        r.observaciones AS observaciones_recepcion,
        c.nombre AS cliente, c.celular AS celular_remitente,
        e.observacion AS motivo_eliminacion,
        e.fecha_actualizacion AS fecha_eliminacion
       FROM encomiendas e
       INNER JOIN recepciones r ON r.id = e.id_recepcion
       LEFT JOIN clientes c ON c.id = r.id_cliente
       WHERE e.id = :id AND e.id_almacen_actual = :id_almacen
         AND e.estado = 'Eliminado'
       LIMIT 1"
    );
    $stmt->execute([":id" => $id, ":id_almacen" => $idAlmacen]);
    return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
  }

  static public function mdlTotalCobradoHoy()
  {
    $stmt = Conexion::conectar()->prepare(
      "SELECT (
        COALESCE((
          SELECT SUM(total_cobrado) FROM entrega
          WHERE estado <> 'Anulado' AND DATE(fecha_entrega) = CURDATE()
        ), 0)
        +
        COALESCE((
          SELECT SUM(precio) FROM encomiendas
          WHERE quien_paga = 'Remitente' AND cobrado = 1 AND DATE(fecha_registro) = CURDATE()
        ), 0)
      ) AS total"
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