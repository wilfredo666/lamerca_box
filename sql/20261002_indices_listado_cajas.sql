-- Ejecutar una sola vez en cada base de datos antes de desplegar la paginación de cajas.
CREATE INDEX idx_recepciones_estado_tipo_fecha
  ON recepciones (estado, tipo_recepcion, fecha_registro, id);

CREATE INDEX idx_recepciones_estado_fecha
  ON recepciones (estado, fecha_registro, id);

CREATE INDEX idx_encomiendas_recepcion_estado
  ON encomiendas (id_recepcion, estado);
