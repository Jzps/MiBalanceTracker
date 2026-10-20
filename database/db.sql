CREATE DATABASE IF NOT EXISTS sistema_gestion_financiera;

USE sistema_gestion_financiera;


-- =====================================================
-- 1. TABLA USUARIO
-- PK: id_usuario
-- UK: correo
-- =====================================================

CREATE TABLE IF NOT EXISTS usuario (
    id_usuario INT NOT NULL AUTO_INCREMENT,
    nombre VARCHAR(100) NOT NULL,
    correo VARCHAR(100) NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    ingreso_fijo_mensual DECIMAL(10,2),
    porcentaje_necesidades DECIMAL(5,2) NOT NULL DEFAULT 50.00,
    porcentaje_deseos DECIMAL(5,2) NOT NULL DEFAULT 30.00,
    porcentaje_ahorro DECIMAL(5,2) NOT NULL DEFAULT 20.00,

    PRIMARY KEY (id_usuario),
    UNIQUE KEY uk_usuario_correo (correo)
);


-- =====================================================
-- 2. TABLA CATEGORIA
-- PK: id_categoria
-- FK: id_usuario
-- =====================================================

CREATE TABLE IF NOT EXISTS categoria (
    id_categoria INT NOT NULL AUTO_INCREMENT,
    id_usuario INT NOT NULL,
    nombre VARCHAR(80) NOT NULL,
    tipo_movimiento ENUM('ingreso', 'gasto') NOT NULL,
    clasificacion_503020 ENUM(
        'necesidad', 'deseo', 'ahorro', 'no_aplica'
    ) NOT NULL DEFAULT 'no_aplica',
    es_predeterminada BOOLEAN NOT NULL DEFAULT FALSE,
    activa BOOLEAN NOT NULL DEFAULT TRUE,

    PRIMARY KEY (id_categoria),

    FOREIGN KEY (id_usuario)
        REFERENCES usuario(id_usuario)
);


-- =====================================================
-- 3. TABLA MOVIMIENTO
-- PK: id_movimiento
-- FK: id_usuario
-- FK: id_categoria
-- =====================================================

CREATE TABLE IF NOT EXISTS movimiento (
    id_movimiento INT NOT NULL AUTO_INCREMENT,
    id_usuario INT NOT NULL,
    id_categoria INT NOT NULL,
    monto DECIMAL(10,2) NOT NULL,
    fecha DATE NOT NULL,
    tipo ENUM('ingreso', 'gasto') NOT NULL,
    descripcion VARCHAR(150) NOT NULL,
    metodo_pago VARCHAR(50),
    notas TEXT,

    PRIMARY KEY (id_movimiento),

    FOREIGN KEY (id_usuario)
        REFERENCES usuario(id_usuario),

    FOREIGN KEY (id_categoria)
        REFERENCES categoria(id_categoria)
);


-- =====================================================
-- 4. TABLA PRESUPUESTO
-- PK: id_presupuesto
-- FK: id_usuario
-- FK: id_categoria
-- =====================================================

CREATE TABLE IF NOT EXISTS presupuesto (
    id_presupuesto INT NOT NULL AUTO_INCREMENT,
    id_usuario INT NOT NULL,
    id_categoria INT NOT NULL,
    monto_limite DECIMAL(10,2) NOT NULL,
    fecha_inicio DATE NOT NULL,
    fecha_fin DATE NOT NULL,
    porcentaje_alerta DECIMAL(5,2) NOT NULL DEFAULT 80.00,

    PRIMARY KEY (id_presupuesto),

    FOREIGN KEY (id_usuario)
        REFERENCES usuario(id_usuario),

    FOREIGN KEY (id_categoria)
        REFERENCES categoria(id_categoria)
);


-- =====================================================
-- 5. TABLA META_AHORRO
-- PK: id_meta
-- FK: id_usuario
-- =====================================================

CREATE TABLE IF NOT EXISTS meta_ahorro (
    id_meta INT NOT NULL AUTO_INCREMENT,
    id_usuario INT NOT NULL,
    nombre VARCHAR(100) NOT NULL,
    tipo_meta ENUM(
        'meta_ahorro', 'fondo_emergencia'
    ) NOT NULL DEFAULT 'meta_ahorro',
    monto_objetivo DECIMAL(10,2) NOT NULL,
    fecha_objetivo DATE NOT NULL,
    estado ENUM(
        'activa', 'completada', 'pausada', 'cancelada'
    ) NOT NULL DEFAULT 'activa',

    PRIMARY KEY (id_meta),

    FOREIGN KEY (id_usuario)
        REFERENCES usuario(id_usuario)
);


-- =====================================================
-- 6. TABLA APORTE_AHORRO
-- PK: id_aporte
-- FK: id_meta
-- =====================================================

CREATE TABLE IF NOT EXISTS aporte_ahorro (
    id_aporte INT NOT NULL AUTO_INCREMENT,
    id_meta INT NOT NULL,
    monto DECIMAL(10,2) NOT NULL,
    fecha DATE NOT NULL,
    nota VARCHAR(255),

    PRIMARY KEY (id_aporte),

    FOREIGN KEY (id_meta)
        REFERENCES meta_ahorro(id_meta)
);


-- =====================================================
-- 7. TABLA DEUDA
-- PK: id_deuda
-- FK: id_usuario
-- =====================================================

CREATE TABLE IF NOT EXISTS deuda (
    id_deuda INT NOT NULL AUTO_INCREMENT,
    id_usuario INT NOT NULL,
    acreedor VARCHAR(150) NOT NULL,
    monto_inicial DECIMAL(10,2) NOT NULL,
    fecha_vencimiento DATE NOT NULL,
    pago_minimo DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    estado ENUM(
        'pendiente', 'pagada', 'cancelada'
    ) NOT NULL DEFAULT 'pendiente',

    PRIMARY KEY (id_deuda),

    FOREIGN KEY (id_usuario)
        REFERENCES usuario(id_usuario)
);


-- =====================================================
-- 8. TABLA PAGO_DEUDA
-- PK: id_pago
-- FK: id_deuda
-- FK: id_movimiento
-- UK: id_movimiento
-- =====================================================

CREATE TABLE IF NOT EXISTS pago_deuda (
    id_pago INT NOT NULL AUTO_INCREMENT,
    id_deuda INT NOT NULL,
    id_movimiento INT NOT NULL,
    monto DECIMAL(10,2) NOT NULL,
    fecha DATE NOT NULL,
    descripcion VARCHAR(150),

    PRIMARY KEY (id_pago),

    UNIQUE KEY uk_pago_deuda_movimiento (id_movimiento),

    FOREIGN KEY (id_deuda)
        REFERENCES deuda(id_deuda),

    FOREIGN KEY (id_movimiento)
        REFERENCES movimiento(id_movimiento)
);