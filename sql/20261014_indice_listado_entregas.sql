ALTER TABLE entrega
  ADD INDEX idx_entrega_listado (estado, fecha_entrega, id);
