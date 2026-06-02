-- Tabla para direcciones de entrega
CREATE TABLE IF NOT EXISTS direcciones_entrega (
    iddireccion INT AUTO_INCREMENT PRIMARY KEY,
    idusuarios INT NOT NULL,
    calle VARCHAR(255) NOT NULL,
    numero VARCHAR(20),
    apartamento VARCHAR(20),
    ciudad VARCHAR(100) NOT NULL,
    estado VARCHAR(100),
    codigo_postal VARCHAR(20),
    pais VARCHAR(100),
    latitud DECIMAL(10, 8),
    longitud DECIMAL(11, 8),
    referencia TEXT,
    es_predeterminada BOOLEAN DEFAULT FALSE,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (idusuarios) REFERENCES usuarios(idusuarios) ON DELETE CASCADE
);

-- Tabla para repartidores/deliveries
CREATE TABLE IF NOT EXISTS repartidores (
    idrepartidor INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(150) NOT NULL,
    telefono VARCHAR(20),
    email VARCHAR(100),
    placa_vehiculo VARCHAR(20),
    activo BOOLEAN DEFAULT TRUE,
    latitud DECIMAL(10, 8),
    longitud DECIMAL(11, 8),
    ultima_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Tabla para rastreo de entregas
CREATE TABLE IF NOT EXISTS entregas (
    identrega INT AUTO_INCREMENT PRIMARY KEY,
    idpedidos INT NOT NULL,
    idusuarios INT NOT NULL,
    iddireccion INT,
    idrepartidor INT,
    estado ENUM('pendiente', 'recogida', 'en_camino', 'entregando', 'entregado', 'cancelada') DEFAULT 'pendiente',
    latitud_repartidor DECIMAL(10, 8),
    longitud_repartidor DECIMAL(11, 8),
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_recogida TIMESTAMP NULL,
    fecha_entrega_estimada TIMESTAMP NULL,
    fecha_entrega_real TIMESTAMP NULL,
    tiempo_estimado_minutos INT,
    FOREIGN KEY (idpedidos) REFERENCES pedidos(idpedidos) ON DELETE CASCADE,
    FOREIGN KEY (idusuarios) REFERENCES usuarios(idusuarios) ON DELETE CASCADE,
    FOREIGN KEY (iddireccion) REFERENCES direcciones_entrega(iddireccion),
    FOREIGN KEY (idrepartidor) REFERENCES repartidores(idrepartidor)
);

-- Tabla para historial de rastreo
CREATE TABLE IF NOT EXISTS historial_entrega (
    idhistorial INT AUTO_INCREMENT PRIMARY KEY,
    identrega INT NOT NULL,
    estado VARCHAR(50),
    latitud DECIMAL(10, 8),
    longitud DECIMAL(11, 8),
    mensaje VARCHAR(255),
    fecha_evento TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (identrega) REFERENCES entregas(identrega) ON DELETE CASCADE
);
