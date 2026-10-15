-- ============================================================
-- SISTEMA DE USUARIOS, ROLES, PERMISOS Y ACTIVIDAD
-- BASE DE DATOS: inventario
-- ============================================================

-- ============================================================
-- 1. TABLA: ROLES
-- ============================================================

CREATE TABLE roles (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(50) NOT NULL UNIQUE,
    descripcion VARCHAR(255) DEFAULT NULL,
    estado TINYINT(1) NOT NULL DEFAULT 1,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- ============================================================
-- 2. TABLA: USUARIOS
-- ============================================================

CREATE TABLE usuarios (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    apellido VARCHAR(100) NOT NULL,
    usuario VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,

    rol_id INT UNSIGNED NOT NULL,

    estado TINYINT(1) NOT NULL DEFAULT 1,

    ultimo_acceso DATETIME DEFAULT NULL,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_usuarios_roles
        FOREIGN KEY (rol_id)
        REFERENCES roles(id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- ============================================================
-- 3. TABLA: PERMISOS
-- ============================================================

CREATE TABLE permisos (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    modulo VARCHAR(50) NOT NULL,
    accion VARCHAR(50) NOT NULL,

    nombre VARCHAR(100) NOT NULL,
    descripcion VARCHAR(255) DEFAULT NULL,

    UNIQUE KEY permiso_unico (modulo, accion)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- ============================================================
-- 4. TABLA: PERMISOS DE CADA ROL
-- ============================================================

CREATE TABLE rol_permisos (
    rol_id INT UNSIGNED NOT NULL,
    permiso_id INT UNSIGNED NOT NULL,

    PRIMARY KEY (rol_id, permiso_id),

    CONSTRAINT fk_rol_permisos_rol
        FOREIGN KEY (rol_id)
        REFERENCES roles(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,

    CONSTRAINT fk_rol_permisos_permiso
        FOREIGN KEY (permiso_id)
        REFERENCES permisos(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- ============================================================
-- 5. TABLA: ACTIVIDAD
-- ============================================================

CREATE TABLE actividad (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    usuario_id INT UNSIGNED DEFAULT NULL,

    modulo VARCHAR(50) NOT NULL,
    accion VARCHAR(50) NOT NULL,

    descripcion VARCHAR(500) DEFAULT NULL,

    referencia_id INT UNSIGNED DEFAULT NULL,

    ip VARCHAR(45) DEFAULT NULL,

    fecha DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_actividad_usuario
        FOREIGN KEY (usuario_id)
        REFERENCES usuarios(id)
        ON DELETE SET NULL
        ON UPDATE CASCADE,

    INDEX idx_actividad_usuario (usuario_id),
    INDEX idx_actividad_modulo (modulo),
    INDEX idx_actividad_accion (accion),
    INDEX idx_actividad_fecha (fecha)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- ============================================================
-- 6. CREAR ROL ADMINISTRADOR
-- ============================================================

INSERT INTO roles (
    nombre,
    descripcion,
    estado
)
VALUES (
    'Administrador',
    'Acceso completo al sistema y administración de usuarios y permisos',
    1
);


-- ============================================================
-- 7. CREAR ROL USUARIO
-- ============================================================

INSERT INTO roles (
    nombre,
    descripcion,
    estado
)
VALUES (
    'Usuario',
    'Usuario con permisos personalizados',
    1
);


-- ============================================================
-- 8. PERMISOS DEL SISTEMA
-- ============================================================

INSERT INTO permisos
    (modulo, accion, nombre, descripcion)
VALUES

-- ============================================================
-- PRODUCTOS
-- ============================================================

(
    'productos',
    'ver',
    'Ver productos',
    'Puede consultar productos'
),

(
    'productos',
    'agregar',
    'Agregar productos',
    'Puede agregar productos'
),

(
    'productos',
    'editar',
    'Editar productos',
    'Puede editar productos'
),

(
    'productos',
    'eliminar',
    'Eliminar productos',
    'Puede eliminar productos'
),


-- ============================================================
-- IMPORTAR EXCEL
-- ============================================================

(
    'importar_excel',
    'ver',
    'Ver importar Excel',
    'Puede acceder al módulo de importación'
),

(
    'importar_excel',
    'importar',
    'Importar Excel',
    'Puede importar productos mediante Excel'
),


-- ============================================================
-- COTIZACIONES
-- ============================================================

(
    'cotizaciones',
    'ver',
    'Ver cotizaciones',
    'Puede consultar cotizaciones'
),

(
    'cotizaciones',
    'agregar',
    'Agregar cotizaciones',
    'Puede crear cotizaciones'
),

(
    'cotizaciones',
    'editar',
    'Editar cotizaciones',
    'Puede editar cotizaciones'
),

(
    'cotizaciones',
    'eliminar',
    'Eliminar cotizaciones',
    'Puede eliminar cotizaciones'
),


-- ============================================================
-- FACTURACIÓN
-- ============================================================

(
    'facturacion',
    'ver',
    'Ver facturación',
    'Puede consultar facturación'
),

(
    'facturacion',
    'agregar',
    'Agregar facturación',
    'Puede crear facturas'
),

(
    'facturacion',
    'editar',
    'Editar facturación',
    'Puede editar facturas'
),

(
    'facturacion',
    'eliminar',
    'Eliminar facturación',
    'Puede eliminar facturas'
),


-- ============================================================
-- CUATRIMESTRES
-- ============================================================

(
    'cuatrimestres',
    'ver',
    'Ver cuatrimestres',
    'Puede consultar cuatrimestres'
),


-- ============================================================
-- SMMLV
-- ============================================================

(
    'smmlv',
    'ver',
    'Ver SMMLV',
    'Puede consultar SMMLV'
),

(
    'smmlv',
    'agregar',
    'Agregar SMMLV',
    'Puede agregar valores SMMLV'
),

(
    'smmlv',
    'editar',
    'Editar SMMLV',
    'Puede editar valores SMMLV'
),

(
    'smmlv',
    'eliminar',
    'Eliminar SMMLV',
    'Puede eliminar valores SMMLV'
),


-- ============================================================
-- FAVORES
-- ============================================================

(
    'favores',
    'ver',
    'Ver favores',
    'Puede consultar favores'
),

(
    'favores',
    'agregar',
    'Agregar favores',
    'Puede agregar favores'
),

(
    'favores',
    'editar',
    'Editar favores',
    'Puede editar favores'
),

(
    'favores',
    'eliminar',
    'Eliminar favores',
    'Puede eliminar favores'
),


-- ============================================================
-- ADMINISTRACIÓN DE USUARIOS
-- ============================================================

(
    'usuarios',
    'ver',
    'Ver usuarios',
    'Puede consultar usuarios'
),

(
    'usuarios',
    'agregar',
    'Agregar usuarios',
    'Puede crear usuarios'
),

(
    'usuarios',
    'editar',
    'Editar usuarios',
    'Puede editar usuarios'
),

(
    'usuarios',
    'eliminar',
    'Eliminar usuarios',
    'Puede eliminar usuarios'
),


-- ============================================================
-- ADMINISTRACIÓN DE PERMISOS
-- ============================================================

(
    'permisos',
    'ver',
    'Ver permisos',
    'Puede consultar permisos'
),

(
    'permisos',
    'editar',
    'Editar permisos',
    'Puede modificar permisos'
),


-- ============================================================
-- ACTIVIDAD
-- ============================================================

(
    'actividad',
    'ver',
    'Ver actividad',
    'Puede consultar la actividad del sistema'
);


-- ============================================================
-- 9. ASIGNAR TODOS LOS PERMISOS AL ADMINISTRADOR
-- ============================================================

INSERT INTO rol_permisos (
    rol_id,
    permiso_id
)
SELECT
    r.id,
    p.id
FROM roles r
CROSS JOIN permisos p
WHERE r.nombre = 'Administrador';


CREATE TABLE usuario_permisos (
    usuario_id INT UNSIGNED NOT NULL,
    permiso_id INT UNSIGNED NOT NULL,

    PRIMARY KEY (usuario_id, permiso_id),

    CONSTRAINT fk_usuario_permisos_usuario
        FOREIGN KEY (usuario_id)
        REFERENCES usuarios(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,

    CONSTRAINT fk_usuario_permisos_permiso
        FOREIGN KEY (permiso_id)
        REFERENCES permisos(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

ALTER TABLE usuarios
ADD COLUMN foto VARCHAR(255) DEFAULT NULL AFTER password;


DELETE FROM permisos
WHERE modulo = 'facturacion';

INSERT INTO permisos
    (modulo, accion, nombre, descripcion)
VALUES
    (
        'facturacion',
        'ver',
        'Ver facturación',
        'Puede consultar el módulo de facturación'
    ),
    (
        'facturacion',
        'contrato_ver',
        'Ver contratos',
        'Puede consultar los contratos'
    ),
    (
        'facturacion',
        'contrato_crear',
        'Crear contratos',
        'Puede crear contratos'
    ),
    (
        'facturacion',
        'contrato_editar',
        'Editar contratos',
        'Puede editar contratos'
    ),
    (
        'facturacion',
        'factura_ver',
        'Ver facturas',
        'Puede consultar las facturas'
    ),
    (
        'facturacion',
        'factura_crear',
        'Crear facturas',
        'Puede crear facturas'
    ),
    (
        'facturacion',
        'factura_editar',
        'Editar facturas',
        'Puede editar facturas'
    ),
    (
        'facturacion',
        'factura_eliminar',
        'Eliminar facturas',
        'Puede eliminar facturas'
    );





    INSERT INTO permisos
    (modulo, accion, nombre, descripcion)
VALUES
    ('favores', 'finalizar', 'Finalizar favor', 'Puede finalizar un favor'),

    ('favores', 'ver_movimientos', 'Ver movimientos', 'Puede consultar los movimientos de una persona'),

    ('favores', 'agregar_movimiento', 'Agregar movimiento', 'Puede registrar nuevos movimientos'),

    ('favores', 'editar_movimiento', 'Editar movimiento', 'Puede modificar un movimiento'),

    ('favores', 'eliminar_movimiento', 'Eliminar movimiento', 'Puede eliminar un movimiento'),

    ('favores', 'pagar_movimiento', 'Registrar pago', 'Puede registrar pagos o abonos'),

    ('favores', 'ver_finalizados', 'Ver finalizados', 'Puede consultar los favores finalizados'),

    ('favores', 'pdf', 'Ver PDF', 'Puede generar el PDF de favores finalizados');









    fact
    
ALTER TABLE facturas
    MODIFY numero_factura VARCHAR(100) DEFAULT NULL,
    ADD COLUMN fecha_factura DATE DEFAULT NULL AFTER numero_factura;