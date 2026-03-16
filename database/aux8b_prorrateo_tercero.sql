-- AUX8b: Tabla de prorrateo por tercero
CREATE TABLE IF NOT EXISTS prorrateo_tercero (
    id_prorrateo_tercero INT AUTO_INCREMENT PRIMARY KEY,
    id_detalle INT NOT NULL,
    id_tercero INT NOT NULL,
    monto DECIMAL(18,2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_prorrateo_tercero_detalle
        FOREIGN KEY (id_detalle) REFERENCES asiento_detalle(id_detalle)
        ON DELETE CASCADE,
    CONSTRAINT fk_prorrateo_tercero_tercero
        FOREIGN KEY (id_tercero) REFERENCES terceros(id_tercero)
        ON DELETE RESTRICT
);

CREATE INDEX idx_prorrateo_tercero_detalle ON prorrateo_tercero (id_detalle);
CREATE INDEX idx_prorrateo_tercero_tercero ON prorrateo_tercero (id_tercero);
