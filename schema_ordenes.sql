-- Script SQL para crear las tablas del sistema de pago
-- Ejecutar en la base de datos 'tiendaonline'

-- Tabla de órdenes/pedidos
CREATE TABLE IF NOT EXISTS ordenes (
    idorden INT PRIMARY KEY AUTO_INCREMENT,
    idusuario INT,
    iddireccion INT,
    fecha_orden TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    total DECIMAL(10, 2) NOT NULL,
    estado VARCHAR(50) DEFAULT 'pendiente',
    metodo_pago VARCHAR(50) NOT NULL,
    numero_tarjeta VARCHAR(20),
    nombre_tarjeta VARCHAR(100),
    fecha_vencimiento VARCHAR(10),
    codigo_barras VARCHAR(100) UNIQUE,
    tienda_cercana VARCHAR(100),
    detalles_orden JSON,
    estado_entrega VARCHAR(50) DEFAULT 'pendiente',
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (idusuario) REFERENCES usuarios(idusuarios),
    FOREIGN KEY (iddireccion) REFERENCES direcciones_entrega(iddireccion)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de detalles de órdenes
CREATE TABLE IF NOT EXISTS detalles_orden (
    iddetalle INT PRIMARY KEY AUTO_INCREMENT,
    idorden INT NOT NULL,
    idproducto INT NOT NULL,
    nombre_producto VARCHAR(255) NOT NULL,
    precio DECIMAL(10, 2) NOT NULL,
    cantidad INT NOT NULL,
    subtotal DECIMAL(10, 2) NOT NULL,
    FOREIGN KEY (idorden) REFERENCES ordenes(idorden)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Índices para mejorar búsquedas
CREATE INDEX idx_usuario ON ordenes(idusuario);
CREATE INDEX idx_estado ON ordenes(estado);
CREATE INDEX idx_codigo_barras ON ordenes(codigo_barras);
CREATE INDEX idx_fecha ON ordenes(fecha_orden);
CREATE INDEX idx_metodo_pago ON ordenes(metodo_pago);
