-- Tabla de Centros de Costo (AUX6)
-- Campos: código (único), nombre, descripción (opcional), estado (activo/inactivo).

CREATE TABLE IF NOT EXISTS centros_costo (
  id_centro_costo INT NOT NULL AUTO_INCREMENT,
  codigo VARCHAR(50) NOT NULL,
  nombre VARCHAR(100) NOT NULL,
  descripcion VARCHAR(200) NULL,
  estado TINYINT NOT NULL DEFAULT 1,
  PRIMARY KEY (id_centro_costo),
  UNIQUE KEY codigo (codigo)
);
