CREATE TABLE IF NOT EXISTS evidencias_contratos (
    id INT NOT NULL AUTO_INCREMENT,
    contrato_id INT NOT NULL,
    nombre_archivo VARCHAR(255) NOT NULL,

    PRIMARY KEY (id),
    INDEX idx_evidencia_contrato (contrato_id),

    CONSTRAINT fk_evidencia_contrato
        FOREIGN KEY (contrato_id)
        REFERENCES contratos(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_general_ci;


  file_uploads=On
upload_max_filesize=40M
post_max_size=100M
max_file_uploads=30
upload_tmp_dir="C:\xampp\tmp"












INSERT INTO permisos (modulo, accion, nombre, descripcion)
SELECT 'evidencias', 'ver', 'Ver evidencias', 'Consultar contratos y sus evidencias'
WHERE NOT EXISTS (
    SELECT 1 FROM permisos
    WHERE modulo = 'evidencias' AND accion = 'ver'
);

INSERT INTO permisos (modulo, accion, nombre, descripcion)
SELECT 'evidencias', 'agregar', 'Agregar evidencias', 'Subir fotografías a los contratos'
WHERE NOT EXISTS (
    SELECT 1 FROM permisos
    WHERE modulo = 'evidencias' AND accion = 'agregar'
);

INSERT INTO permisos (modulo, accion, nombre, descripcion)
SELECT 'evidencias', 'exportar', 'Exportar evidencias ZIP', 'Descargar fotografías en formato ZIP'
WHERE NOT EXISTS (
    SELECT 1 FROM permisos
    WHERE modulo = 'evidencias' AND accion = 'exportar'
);

INSERT INTO permisos (modulo, accion, nombre, descripcion)
SELECT
    'evidencias',
    'eliminar',
    'Eliminar evidencias',
    'Eliminar fotografías de los contratos'
WHERE NOT EXISTS (
    SELECT 1
    FROM permisos
    WHERE modulo = 'evidencias'
      AND accion = 'eliminar'
);