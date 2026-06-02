<?php
include("conexion.php");

// Desactivar restricciones de clave foránea temporalmente
$conexion->query("SET FOREIGN_KEY_CHECKS=0");

// Array de productos por categoría
$productos = [
    // PRODUCTOS SMART
    [
        "nombre" => "Smart Watch Pro Ultra",
        "categoria" => "Productos smart",
        "precio" => 129.99,
        "descripcion" => "Reloj inteligente con pantalla AMOLED, monitor de ritmo cardíaco, GPS integrado y batería de 7 días. Compatible con iOS y Android.",
        "stock" => 50,
        "calificacion" => 4.8,
        "descuento" => 20,
        "imagen" => "https://m.media-amazon.com/images/I/81KbgrFcqCL._AC_SL1500_.jpg"
    ],
    [
        "nombre" => "Smart Speaker Mini",
        "categoria" => "Productos smart",
        "precio" => 49.99,
        "descripcion" => "Altavoz inteligente compacto con Alexa integrado. Control de hogar inteligente, música en HD y micrófono de campo lejano.",
        "stock" => 75,
        "calificacion" => 4.5,
        "descuento" => 15,
        "imagen" => "img/speaker1.jpg"
    ],
    [
        "nombre" => "Smart Home Hub",
        "categoria" => "Productos smart",
        "precio" => 99.99,
        "descripcion" => "Centro de control inteligente para tu hogar. Compatible con más de 500 dispositivos smart. Pantalla táctil de 4 pulgadas.",
        "stock" => 30,
        "calificacion" => 4.7,
        "descuento" => 18,
        "imagen" => "img/hub1.jpg"
    ],
    [
        "nombre" => "Smart Bulb WiFi RGB",
        "categoria" => "Productos smart",
        "precio" => 19.99,
        "descripcion" => "Bombilla LED inteligente con 16 millones de colores. Control remoto via app, compatible con Alexa y Google Home.",
        "stock" => 200,
        "calificacion" => 4.6,
        "descuento" => 25,
        "imagen" => "img/bulb1.jpg"
    ],
    [
        "nombre" => "Smart Thermostat",
        "categoria" => "Productos smart",
        "precio" => 179.99,
        "descripcion" => "Termostato inteligente que aprende tus preferencias. Ahorra energía automáticamente y contrólalo desde tu teléfono.",
        "stock" => 25,
        "calificacion" => 4.9,
        "descuento" => 22,
        "imagen" => "img/thermostat1.jpg"
    ],
    [
        "nombre" => "Smart Doorbell Camera",
        "categoria" => "Productos smart",
        "precio" => 139.99,
        "descripcion" => "Timbre inteligente con cámara HD 1080p, visión nocturna infrarroja y detección de movimiento inteligente.",
        "stock" => 40,
        "calificacion" => 4.8,
        "descuento" => 20,
        "imagen" => "img/doorbell1.jpg"
    ],

    // COVENTRA SMART (Electrodomésticos Smart)
    [
        "nombre" => "Nevera Inteligente Connected",
        "categoria" => "Coventra smart",
        "precio" => 1299.99,
        "descripcion" => "Refrigerador inteligente con pantalla táctil, gestión de alimentos y control de temperatura remoto. Dispenser de agua y hielo.",
        "stock" => 10,
        "calificacion" => 4.9,
        "descuento" => 15,
        "imagen" => "img/nevera1.jpg"
    ],
    [
        "nombre" => "Lavadora Smart WiFi",
        "categoria" => "Coventra smart",
        "precio" => 799.99,
        "descripcion" => "Lavadora inteligente con control remoto, sensores de carga automática y programas especializados para diferentes tejidos.",
        "stock" => 15,
        "calificacion" => 4.7,
        "descuento" => 18,
        "imagen" => "img/lavadora1.jpg"
    ],
    [
        "nombre" => "Horno Inteligente Convección",
        "categoria" => "Coventra smart",
        "precio" => 599.99,
        "descripcion" => "Horno eléctrico inteligente con control de temperatura preciso, pantalla digital y múltiples funciones de cocción.",
        "stock" => 12,
        "calificacion" => 4.8,
        "descuento" => 20,
        "imagen" => "img/horno1.jpg"
    ],
    [
        "nombre" => "Aire Acondicionado Smart",
        "categoria" => "Coventra smart",
        "precio" => 449.99,
        "descripcion" => "Aire acondicionado inteligente con ahorro de energía, control de humedad y programación semanal desde la app.",
        "stock" => 20,
        "calificacion" => 4.6,
        "descuento" => 16,
        "imagen" => "img/aire1.jpg"
    ],
    [
        "nombre" => "Cafetera Smart Premium",
        "categoria" => "Coventra smart",
        "precio" => 159.99,
        "descripcion" => "Cafetera inteligente programable con control desde tu teléfono. Mantiene el café caliente por horas sin quemar.",
        "stock" => 45,
        "calificacion" => 4.7,
        "descuento" => 17,
        "imagen" => "img/cafetera1.jpg"
    ],

    // NOVEDADES
    [
        "nombre" => "Drone 4K Profesional",
        "categoria" => "Novedades",
        "precio" => 699.99,
        "descripcion" => "Drone con cámara 4K, tiempo de vuelo de 25 minutos y tecnología de evitación de obstáculos. Perfecto para fotografía aérea.",
        "stock" => 18,
        "calificacion" => 4.8,
        "descuento" => 19,
        "imagen" => "img/drone1.jpg"
    ],
    [
        "nombre" => "Auriculares Inalámbricos Noise Cancelling",
        "categoria" => "Novedades",
        "precio" => 249.99,
        "descripcion" => "Auriculares premium con cancelación activa de ruido, batería de 30 horas y sonido estéreo cristalino.",
        "stock" => 60,
        "calificacion" => 4.9,
        "descuento" => 21,
        "imagen" => "img/auriculares1.jpg"
    ],
    [
        "nombre" => "Monitor Gaming 144Hz",
        "categoria" => "Novedades",
        "precio" => 349.99,
        "descripcion" => "Monitor 27 pulgadas con 144Hz, tiempo de respuesta 1ms, panel IPS y soporte para AMD FreeSync Premium.",
        "stock" => 25,
        "calificacion" => 4.7,
        "descuento" => 18,
        "imagen" => "img/monitor1.jpg"
    ],
    [
        "nombre" => "Tablet Ultra Slim 12.9\"",
        "categoria" => "Novedades",
        "precio" => 579.99,
        "descripcion" => "Tablet con procesador octa-core, pantalla AMOLED, 8GB RAM y batería de 10 horas de duración.",
        "stock" => 35,
        "calificacion" => 4.8,
        "descuento" => 22,
        "imagen" => "img/tablet1.jpg"
    ],
    [
        "nombre" => "Laptop Gaming Performance",
        "categoria" => "Novedades",
        "precio" => 1299.99,
        "descripcion" => "Laptop gaming con procesador i9, GPU RTX 4070, 32GB RAM y pantalla 144Hz. Diseña, juega y crea sin límites.",
        "stock" => 8,
        "calificacion" => 4.9,
        "descuento" => 20,
        "imagen" => "img/laptop1.jpg"
    ],

    // TECNOLOGÍA
    [
        "nombre" => "Cámara Digital Mirrorless",
        "categoria" => "Tecnología",
        "precio" => 1199.99,
        "descripcion" => "Cámara mirrorless con sensor full frame, 4K a 60fps y estabilización óptica de imagen avanzada.",
        "stock" => 12,
        "calificacion" => 4.9,
        "descuento" => 18,
        "imagen" => "img/camara1.jpg"
    ],
    [
        "nombre" => "Router WiFi 6 Gigabit",
        "categoria" => "Tecnología",
        "precio" => 199.99,
        "descripcion" => "Router de última generación con WiFi 6, conectividad de hasta 3Gbps y alcance de 200 metros.",
        "stock" => 50,
        "calificacion" => 4.7,
        "descuento" => 20,
        "imagen" => "img/router1.jpg"
    ],
    [
        "nombre" => "SSD NVMe 1TB Ultra Rápido",
        "categoria" => "Tecnología",
        "precio" => 89.99,
        "descripcion" => "Unidad SSD de última generación con velocidades de lectura de 7.1GB/s. Perfecto para gaming y edición.",
        "stock" => 100,
        "calificacion" => 4.8,
        "descuento" => 25,
        "imagen" => "img/ssd1.jpg"
    ],
    [
        "nombre" => "Fuente de Poder 1000W Modular",
        "categoria" => "Tecnología",
        "precio" => 149.99,
        "descripcion" => "Fuente modular 80+ Gold, 1000W de potencia, ventilación silenciosa y protecciones múltiples.",
        "stock" => 40,
        "calificacion" => 4.6,
        "descuento" => 16,
        "imagen" => "img/fuente1.jpg"
    ],
    [
        "nombre" => "Tarjeta Gráfica RTX 4080",
        "categoria" => "Tecnología",
        "precio" => 1099.99,
        "descripcion" => "GPU de última generación con 16GB GDDR6X, arquitectura Ada y rendimiento de gaming extremo.",
        "stock" => 15,
        "calificacion" => 4.9,
        "descuento" => 17,
        "imagen" => "img/gpu1.jpg"
    ],
    [
        "nombre" => "Procesador Intel i9 13th Gen",
        "categoria" => "Tecnología",
        "precio" => 589.99,
        "descripcion" => "Procesador de última generación con 24 núcleos, 32 threads y velocidades hasta 5.6GHz.",
        "stock" => 25,
        "calificacion" => 4.8,
        "descuento" => 19,
        "imagen" => "img/procesador1.jpg"
    ],

    // HOGAR
    [
        "nombre" => "Aspiradora Robot Inteligente",
        "categoria" => "Hogar",
        "precio" => 399.99,
        "descripcion" => "Robot aspirador con mapeo inteligente, succión potente y batería de 180 minutos. Contrólalo desde la app.",
        "stock" => 30,
        "calificacion" => 4.7,
        "descuento" => 18,
        "imagen" => "img/aspiradora1.jpg"
    ],
    [
        "nombre" => "Juego Completo de Sartenes Premium",
        "categoria" => "Hogar",
        "precio" => 179.99,
        "descripcion" => "Set de 9 sartenes y ollas con revestimiento antiadherente titanium y mangos resistentes al calor.",
        "stock" => 25,
        "calificacion" => 4.6,
        "descuento" => 22,
        "imagen" => "img/sartenes1.jpg"
    ],
    [
        "nombre" => "Licuadora de Alta Potencia",
        "categoria" => "Hogar",
        "precio" => 199.99,
        "descripcion" => "Licuadora profesional 2000W con 6 velocidades, vaso de vidrio templado y garantía de 10 años.",
        "stock" => 45,
        "calificacion" => 4.8,
        "descuento" => 20,
        "imagen" => "img/licuadora1.jpg"
    ],
    [
        "nombre" => "Set de Ropa de Cama Seda",
        "categoria" => "Hogar",
        "precio" => 299.99,
        "descripcion" => "Juego de sábanas y almohadas de seda pura, suave al tacto y beneficioso para la piel.",
        "stock" => 50,
        "calificacion" => 4.9,
        "descuento" => 18,
        "imagen" => "img/sabanas1.jpg"
    ],
    [
        "nombre" => "Espejo LED de Pared",
        "categoria" => "Hogar",
        "precio" => 149.99,
        "descripcion" => "Espejo decorativo LED anti-empaño, iluminación ajustable y marco de aluminio minimalista.",
        "stock" => 35,
        "calificacion" => 4.5,
        "descuento" => 15,
        "imagen" => "img/espejo1.jpg"
    ],
    [
        "nombre" => "Lámpara de Pie Inteligente",
        "categoria" => "Hogar",
        "precio" => 99.99,
        "descripcion" => "Lámpara LED ajustable RGB, control remoto incluido y consumo energético mínimo.",
        "stock" => 60,
        "calificacion" => 4.7,
        "descuento" => 21,
        "imagen" => "img/lampara1.jpg"
    ],

    // MODA
    [
        "nombre" => "Zapatillas Running Premium",
        "categoria" => "Moda",
        "precio" => 159.99,
        "descripcion" => "Zapatillas de running con tecnología de amortiguación avanzada, peso ligero y diseño aerodinámico.",
        "stock" => 80,
        "calificacion" => 4.8,
        "descuento" => 20,
        "imagen" => "img/zapatillas1.jpg"
    ],
    [
        "nombre" => "Chaqueta Deportiva Impermeable",
        "categoria" => "Moda",
        "precio" => 129.99,
        "descripcion" => "Chaqueta deportiva transpirable, impermeable y diseño moderno. Disponible en varios colores.",
        "stock" => 60,
        "calificacion" => 4.7,
        "descuento" => 18,
        "imagen" => "img/chaqueta1.jpg"
    ],
    [
        "nombre" => "Reloj de Moda Análogo",
        "categoria" => "Moda",
        "precio" => 89.99,
        "descripcion" => "Reloj elegante con correa de cuero genuino, resistencia al agua y cristal de zafiro.",
        "stock" => 70,
        "calificacion" => 4.6,
        "descuento" => 22,
        "imagen" => "img/reloj1.jpg"
    ],
    [
        "nombre" => "Jeans Premium Ajuste Perfecto",
        "categoria" => "Moda",
        "precio" => 79.99,
        "descripcion" => "Jeans de algodón 100% con diseño moderno, costuras reforzadas y ajuste ergonómico.",
        "stock" => 120,
        "calificacion" => 4.5,
        "descuento" => 19,
        "imagen" => "img/jeans1.jpg"
    ],
    [
        "nombre" => "Bolsa de Mano Luxe",
        "categoria" => "Moda",
        "precio" => 199.99,
        "descripcion" => "Bolsa de mano de cuero genuino italiano con múltiples compartimentos y cierre de seguridad.",
        "stock" => 40,
        "calificacion" => 4.8,
        "descuento" => 17,
        "imagen" => "img/bolsa1.jpg"
    ],
    [
        "nombre" => "Gafas de Sol Polarizadas",
        "categoria" => "Moda",
        "precio" => 119.99,
        "descripcion" => "Gafas con lentes polarizadas, protección UV 100% y marco de titanio ligero.",
        "stock" => 55,
        "calificacion" => 4.7,
        "descuento" => 20,
        "imagen" => "img/gafas1.jpg"
    ],

    // DEPORTES
    [
        "nombre" => "Bicicleta Montaña Profesional",
        "categoria" => "Deportes",
        "precio" => 499.99,
        "descripcion" => "Bicicleta MTB con suspensión dual, frenos de disco hidráulicos y cambios de 21 velocidades.",
        "stock" => 20,
        "calificacion" => 4.9,
        "descuento" => 18,
        "imagen" => "img/bicicleta1.jpg"
    ],
    [
        "nombre" => "Mancuerna Ajustable 50kg",
        "categoria" => "Deportes",
        "precio" => 299.99,
        "descripcion" => "Juego de mancuernas ajustables con pesos de 2.5kg a 25kg por unidad, acero cromado.",
        "stock" => 25,
        "calificacion" => 4.8,
        "descuento" => 20,
        "imagen" => "img/mancuerna1.jpg"
    ],
    [
        "nombre" => "Tabla de Surf Profesional",
        "categoria" => "Deportes",
        "precio" => 349.99,
        "descripcion" => "Tabla de surf fibra de vidrio, diseño hidrodinámico y acabado glossy de alta calidad.",
        "stock" => 15,
        "calificacion" => 4.7,
        "descuento" => 16,
        "imagen" => "img/tabla_surf1.jpg"
    ],
    [
        "nombre" => "Bola de Fútbol Profesional",
        "categoria" => "Deportes",
        "precio" => 49.99,
        "descripcion" => "Balón de fútbol oficial con 32 paneles, costura reforzada y cubierta de cuero sintético.",
        "stock" => 150,
        "calificacion" => 4.6,
        "descuento" => 21,
        "imagen" => "img/balon1.jpg"
    ],
    [
        "nombre" => "Casco de Ciclismo Aerodinámico",
        "categoria" => "Deportes",
        "precio" => 199.99,
        "descripcion" => "Casco de ciclismo ligero, ventilación óptima y certificado de seguridad internacional.",
        "stock" => 50,
        "calificacion" => 4.8,
        "descuento" => 19,
        "imagen" => "img/casco1.jpg"
    ],
    [
        "nombre" => "Patineta Eléctrica Moderna",
        "categoria" => "Deportes",
        "precio" => 599.99,
        "descripcion" => "Patineta eléctrica con motor de 1000W, alcance de 50km y velocidad máxima de 45km/h.",
        "stock" => 18,
        "calificacion" => 4.9,
        "descuento" => 22,
        "imagen" => "img/patineta1.jpg"
    ],

    // PASTAMANÍAS
    [
        "nombre" => "Set Completo de Pastas Italianas",
        "categoria" => "Pastamanías",
        "precio" => 59.99,
        "descripcion" => "Colección de 10 tipos diferentes de pasta 100% trigo integral, importada directamente de Italia.",
        "stock" => 80,
        "calificacion" => 4.8,
        "descuento" => 23,
        "imagen" => "img/pasta1.jpg"
    ],
    [
        "nombre" => "Máquina Para Hacer Pasta",
        "categoria" => "Pastamanías",
        "precio" => 199.99,
        "descripcion" => "Máquina manual de acero inoxidable para hacer pasta fresca casera. Accesorios para 6 tipos de pasta.",
        "stock" => 30,
        "calificacion" => 4.9,
        "descuento" => 20,
        "imagen" => "img/maquina_pasta1.jpg"
    ],
    [
        "nombre" => "Salsa Tomate Orgánica Premium",
        "categoria" => "Pastamanías",
        "precio" => 19.99,
        "descripcion" => "Salsa de tomate 100% orgánica, sin conservantes, hecha con tomates frescos de Campania.",
        "stock" => 200,
        "calificacion" => 4.7,
        "descuento" => 18,
        "imagen" => "img/salsa1.jpg"
    ],
    [
        "nombre" => "Queso Parmesano Importado",
        "categoria" => "Pastamanías",
        "precio" => 34.99,
        "descripcion" => "Queso Parmesano Reggiano auténtico, envejecido 36 meses, origen Italia DOP.",
        "stock" => 60,
        "calificacion" => 4.9,
        "descuento" => 17,
        "imagen" => "img/queso1.jpg"
    ],
    [
        "nombre" => "Aceite de Oliva Extra Virgen",
        "categoria" => "Pastamanías",
        "precio" => 29.99,
        "descripcion" => "Aceite de oliva premium, cosecha temprana, prensado en frío, botella de 500ml.",
        "stock" => 100,
        "calificacion" => 4.8,
        "descuento" => 19,
        "imagen" => "https://m.media-amazon.com/images/I/51z1bzUMYyL._AC_SL1000_.jpg"
    ],
    [
        "nombre" => "Vino Tinto Reserva Italiano",
        "categoria" => "Pastamanías",
        "precio" => 49.99,
        "descripcion" => "Vino tinto italiano reserva, envejecido en barrica, combinación perfecta con pasta.",
        "stock" => 75,
        "calificacion" => 4.7,
        "descuento" => 21,
        "imagen" => "img/vino1.jpg"
    ]
];

// Insertar productos en la base de datos
$insertados = 0;
$errores = 0;

foreach($productos as $producto) {
    $nombre = $conexion->real_escape_string($producto['nombre']);
    $categoria = $conexion->real_escape_string($producto['categoria']);
    $precio = $producto['precio'];
    $descripcion = $conexion->real_escape_string($producto['descripcion']);
    $stock = $producto['stock'];
    $calificacion = $producto['calificacion'];
    $descuento = $producto['descuento'];
    $imagen = $conexion->real_escape_string($producto['imagen']);

    $sql = "INSERT INTO productos (nombre_producto, categoria, precio, descripcion, stock, calificacion, descuento, imagen) 
            VALUES ('$nombre', '$categoria', $precio, '$descripcion', $stock, $calificacion, $descuento, '$imagen')";

    if($conexion->query($sql)) {
        $insertados++;
    } else {
        $errores++;
        echo "Error al insertar: " . $conexion->error . "<br>";
    }
}

// Reactivar restricciones de clave foránea
$conexion->query("SET FOREIGN_KEY_CHECKS=1");

echo "<h1 style='color: green;'>✓ Proceso completado</h1>";
echo "<p><strong>Productos insertados: $insertados</strong></p>";
echo "<p><strong>Errores: $errores</strong></p>";
echo "<p style='margin-top: 20px;'><a href='index.php' style='background: #000000; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Ir a la tienda</a></p>";

$conexion->close();
?>
