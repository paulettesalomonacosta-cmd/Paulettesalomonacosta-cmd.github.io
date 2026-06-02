# 🛍️ MAXIMA ONLINE STORE

Plataforma de comercio electrónico desarrollada en PHP con características avanzadas de compra, administración de productos y un juego interactivo de descuentos.

---

## 📋 Tabla de Contenidos

- [Características](#características)
- [Requisitos](#requisitos)
- [Instalación](#instalación)
- [Configuración](#configuración)
- [Uso](#uso)
- [Estructura del Proyecto](#estructura-del-proyecto)
- [Características Principales](#características-principales)
- [Seguridad](#seguridad)
- [Soporte](#soporte)

---

## ✨ Características

### 🛒 Carrito de Compras
- Agregar/eliminar productos
- Actualizar cantidades
- Persistencia en sesión
- Vista previa de total

### 💳 Sistema de Pagos
- Pago con tarjeta de crédito
- Pago en tienda física
- Confirmación de pago con código de barras
- Múltiples direcciones de entrega

### 👤 Gestión de Usuarios
- Registro e inicio de sesión
- Perfiles de usuario
- Historial de órdenes
- Wishlist (lista de deseos)
- Reseñas de productos

### 🎮 Max Arena - Juego de Descuentos
- **Sistema de 1 intento por pago**
- Requisito: Compra confirmada de $100+
- Gana descuentos de 10-25%
- Bloqueo tras cada intento (gane o pierda)
- Nuevo pago necesario para otro intento

### 📦 Seguimiento de Entregas
- Rastreo de pedidos en tiempo real
- GPS de ubicación de almacén
- Estados de entrega
- Notificaciones de cambios

### 📱 Administración
- Panel de administración
- Agregar/editar/eliminar productos
- Gestionar imágenes de productos
- Visualizar órdenes
- Gestionar entregas
- **Solo acceso: admin@maximaonline.store**

### 🎨 Características Visuales
- Interfaz responsiva
- Carrusel de ofertas
- Filtros de productos
- Búsqueda avanzada
- Categorías de productos
- Descuentos especiales

---

## 🔧 Requisitos

### Servidor
- **PHP 7.4+**
- **MySQL 5.7+**
- **Apache/Nginx** con soporte de .htaccess

### Cliente
- Navegador moderno (Chrome, Firefox, Edge, Safari)
- JavaScript habilitado
- LocalStorage habilitado

### Dependencias
- Font Awesome 6.0 (CDN)
- Leaflet Maps (CDN)
- jQuery (opcional)

---

## 📥 Instalación

### Paso 1: Descargar Proyecto
```bash
git clone https://github.com/tuusuario/tiendaonline.git
cd tiendaonline
```

### Paso 2: Configurar Base de Datos
1. Abre tu gestor de MySQL (phpMyAdmin, MySQL Workbench, etc.)
2. Crea una nueva base de datos:
```sql
CREATE DATABASE tiendaonline CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### Paso 3: Importar Tablas
1. Ejecuta los archivos SQL en esta orden:
```sql
-- En phpMyAdmin: Importar > Seleccionar archivos
criar_tabla_usuarios.sql
crear_tabla_ordenes.sql
crear_tabla_resenas.sql
crear_tablas_entregas.sql
```

### Paso 4: Configurar Conexión
Edita `conexion.php`:
```php
<?php
$servidor = "localhost";
$usuario = "root";
$contraseña = "";
$bd = "tiendaonline";

$conexion = new mysqli($servidor, $usuario, $contraseña, $bd);

if($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}

$conexion->set_charset("utf8mb4");
?>
```

### Paso 5: Colocar en Servidor Web
```
/var/www/html/tiendaonline/  (Linux)
C:\xampp\htdocs\tiendaonline\  (Windows XAMPP)
C:\wamp\www\tiendaonline\  (Windows WAMP)
```

### Paso 6: Crear Admin
Accede a:
```
http://localhost/tiendaonline/crear_admin.php
```

**Credenciales Admin:**
- 📧 Email: `admin@maximaonline.store`
- 🔐 Contraseña: `AdminMax2024`

---

## ⚙️ Configuración

### Variables de Entorno
Crea un archivo `.env` (opcional):
```
DB_HOST=localhost
DB_USER=root
DB_PASSWORD=
DB_NAME=tiendaonline
APP_URL=http://localhost/tiendaonline
```

### Configuración de Carrusel
Edita `carrusel_config.json`:
```json
[
    {
        "titulo": "TU TÍTULO",
        "descripcion": "Tu descripción",
        "etiqueta": "ETIQUETA",
        "imagen": "https://url-imagen.jpg",
        "gradiente": "linear-gradient(135deg, #000000 0%, #ff5500 100%)"
    }
]
```

### Colores Personalizados
Modifica `estilos.css`:
```css
/* Colores principales */
--color-principal: #ff5500;  /* Naranja */
--color-secundario: #000000;  /* Negro */
```

---

## 🚀 Uso

### Para Clientes

#### Comprar Productos
1. Navega a la tienda
2. Filtra por categoría o busca
3. Haz clic en producto
4. Añade al carrito
5. Ve al carrito y realiza pago

#### Jugar Max Arena
1. Completa compra de $100+ (confirmada)
2. Obtiene 1 intento
3. Presiona cartas para voltearlas
4. Gana descuento (10-25%) O Pierde
5. Paga $100+ más para otro intento

#### Rastrear Pedido
1. Accede a "Mis Órdenes"
2. Selecciona orden
3. Ve ubicación en mapa en tiempo real

### Para Administradores

#### Acceder Panel
1. Haz clic "Administración" (solo visible si eres admin)
2. Login con: `admin@maximaonline.store` / `AdminMax2024`

#### Gestionar Productos
- **Agregar**: Panel Admin → Nuevo Producto
- **Editar**: Botón ✏️ en producto
- **Eliminar**: Botón 🗑️ en producto
- **Imágenes**: Panel Admin → Actualizar Imágenes

#### Gestionar Órdenes
- Ver todas las órdenes
- Cambiar estado de entrega
- Ver detalles del cliente
- Actualizar ubicación GPS

---

## 📁 Estructura del Proyecto

```
tiendaonline/
├── index.php                      # Página principal
├── conexion.php                   # Conexión a BD
├── login.php                      # Login usuarios
├── registro.php                   # Registro usuarios
├── carrito.php                    # Carrito de compras
├── metodos_pago.php              # Selección de pago
├── procesar_pago.php             # Procesar pago
├── confirmacion_pago.php         # Confirmación
├── juego_descuento.php           # Max Arena (API)
├── admin_panel.php               # Panel admin
├── agregar.php                   # Agregar producto
├── editar.php                    # Editar producto
├── eliminar.php                  # Eliminar producto
├── actualizar_imagenes.php       # Actualizar imágenes
├── rastrear_entrega.php          # Rastreo de pedidos
├── gestor_ordenes.php            # Gestión de órdenes
├── gestionar_entregas.php        # Gestión entregas
├── crear_tabla_*.sql             # Scripts SQL
├── estilos.css                   # Estilos CSS
├── carrusel_config.json          # Config carrusel
├── img/                          # Carpeta imágenes
└── README.md                     # Este archivo
```

---

## 🎮 Características Principales Detalladas

### 1. Sistema de Autenticación
- Registro con validación
- Login con hash de contraseña (bcrypt)
- Sesiones persistentes
- Recuperación de contraseña

### 2. Max Arena Game
```
Ciclo de Juego:
├─ Pagar $100+ ✓
├─ Obtener 1 intento
├─ Voltear cartas (sin límite)
├─ Ganar (10-25% desc) O Perder
├─ Bloqueo automático
└─ Pagar $100+ para nuevo intento
```

### 3. Sistema de Pagos
- Validación de tarjeta
- Código de barras para pago en tienda
- Confirmación automática
- Historial de transacciones

### 4. Seguimiento GPS
- Mapa Leaflet en tiempo real
- Ubicación del almacén
- Ruta de entrega
- Actualización automática

### 5. Sistema de Roles
- **Admin**: `admin@maximaonline.store` (solo esta cuenta)
- **Cliente**: Cualquier usuario registrado
- Restricción de acceso implementada

---

## 🔒 Seguridad

### Implementadas
- ✅ Hash de contraseñas (bcrypt)
- ✅ Validación de entrada (escape MySQL)
- ✅ CSRF tokens (recomendado implementar)
- ✅ Restricción de admin por email
- ✅ Sesiones seguras
- ✅ Validación de datos en servidor

### Recomendaciones
- [ ] Implementar prepared statements en todos lados
- [ ] Usar HTTPS en producción
- [ ] Implementar rate limiting
- [ ] Agregar 2FA para admin
- [ ] Backup diario de BD
- [ ] Monitoreo de logs

### Archivos Sensibles
Protege estos archivos:
```
conexion.php      # No exponer credenciales BD
.env              # Variables privadas
admin_panel.php   # Solo admin accede
```

---

## 📞 Soporte

### Solución de Problemas

**Error: "No se puede conectar a la BD"**
- Verifica que MySQL está corriendo
- Comprueba credenciales en `conexion.php`
- Verifica que la BD existe

**Error: "Tabla no existe"**
- Ejecuta los scripts SQL en orden
- Verifica caracteres UTF-8

**El juego no funciona**
- Verifica que pagaste compra de $100+
- Confirma que el pago está en estado 'pagado'
- Limpia cache del navegador

**No puedo acceder a admin**
- Verifica email: `admin@maximaonline.store`
- Contraseña: `AdminMax2024`
- Solo esta cuenta tiene acceso

---

## 📝 Notas Importantes

### Base de Datos
- Caracteres soportados: UTF-8 completo
- Respaldo recomendado: Semanal
- Limpieza de datos: Mensual

### Rendimiento
- Optimiza imágenes (máx 500KB)
- Cachea productos estáticos
- Considera CDN para imágenes

### Actualizaciones
- Mantén PHP actualizado (7.4+)
- Actualiza MySQL (5.7+)
- Revisa de seguridad mensual

---

## 📄 Licencia

Este proyecto es propiedad de MAXIMA ONLINE STORE.
Uso comercial permitido solo con autorización.

---

## 👨‍💻 Desarrollado por

**MAXIMA ONLINE STORE**
- 📧 Contacto: admin@maximaonline.store
- 🌐 Sitio: www.maximaonline.store
- 📱 Teléfono: [Tu teléfono]

---

## ✅ Checklist de Instalación

- [ ] Base de datos creada
- [ ] Tablas importadas
- [ ] conexion.php configurado
- [ ] Admin creado
- [ ] Prueba de login exitosa
- [ ] Prueba de compra (sin pagar)
- [ ] Colores actualizados (naranja/rojo)
- [ ] Carrusel configurado
- [ ] Imágenes subidas
- [ ] Emails configurados (opcional)

---

**Versión**: 1.0.0  
**Última actualización**: 2 de Junio de 2026  
**Estado**: ✅ Producción

