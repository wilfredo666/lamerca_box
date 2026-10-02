-- Ejecutar una sola vez en cada base de datos antes de desplegar la paginación.
CREATE INDEX idx_encomiendas_almacen_estado_fecha
  ON encomiendas (id_almacen_actual, estado, fecha_registro, id);
