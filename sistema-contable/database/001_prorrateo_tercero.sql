-- Tabla para prorratear el monto de una línea de asiento entre uno o varios terceros.
-- Consistente con el esquema del dump (decimal(14,2) y FKs similares a prorrateo_centro_costo).

CREATE TABLE IF NOT EXISTS prorrateo_tercero (
  id_prorrateo_tercero INT NOT NULL AUTO_INCREMENT,
  id_detalle INT NOT NULL,
  id_tercero INT NOT NULL,
  monto DECIMAL(14,2) NOT NULL,
  PRIMARY KEY (id_prorrateo_tercero),
  KEY id_detalle (id_detalle),
  KEY id_tercero (id_tercero),
  CONSTRAINT prorrateo_tercero_ibfk_1 FOREIGN KEY (id_detalle) REFERENCES asiento_detalle (id_detalle) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT prorrateo_tercero_ibfk_2 FOREIGN KEY (id_tercero) REFERENCES terceros (id_tercero) ON DELETE RESTRICT ON UPDATE CASCADE
);
