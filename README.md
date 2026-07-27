# 🛍️ Sistema Ecommerce PHP - Arquitectura Modular

![PHP Version](https://img.shields.io/badge/PHP-8.1%2B-blue)
![License](https://img.shields.io/badge/license-MIT-green)

Sistema de comercio electrónico empresarial desarrollado en **PHP puro** (sin frameworks), con arquitectura modular, sistema de plugins, y preparado para integraciones con ERP.

## 📋 Tabla de Contenidos

- [Características Principales](#características-principales)
- [Arquitectura](#arquitectura)
- [Requisitos del Sistema](#requisitos-del-sistema)
- [Instalación](#instalación)
- [Configuración](#configuración)
- [Estructura de Carpetas](#estructura-de-carpetas)
- [Uso Básico](#uso-básico)
- [Sistema de Plugins](#sistema-de-plugins)
- [API REST](#api-rest)
- [Integración con ERP](#integración-con-erp)
- [Seguridad](#seguridad)
- [Testing](#testing)
- [Contribuir](#contribuir)

---

## ✨ Características Principales

### 🎯 Funcionalidades Core
- ✅ Catálogo de productos con categorías, variantes y atributos
- ✅ Sistema de carrito de compras y checkout
- ✅ Gestión completa de pedidos con estados
- ✅ Inventario multi-almacén con control de stock
- ✅ Sistema de usuarios con roles y permisos granulares
- ✅ Cupones y descuentos
- ✅ Reviews y valoraciones de productos
- ✅ Sistema de notificaciones y emails

### 🚀 Características Avanzadas
- ✅ **Sistema de Plugins**: Extensible y modular
- ✅ **Multi-tienda**: Soporta múltiples tiendas desde una instalación
- ✅ **Multi-moneda**: Precios y conversiones automáticas
- ✅ **Multi-idioma**: Traducciones completas
- ✅ **API RESTful**: Autenticación JWT para integraciones
- ✅ **Integración ERP**: Sincronización bidireccional
- ✅ **Webhooks**: Para pagos y eventos externos
- ✅ **Rate Limiting**: Control de peticiones API
- ✅ **Auditoría completa**: Logs de todas las acciones

### 🔒 Seguridad
- Protección CSRF en todos los formularios
- Password hashing con bcrypt
- Prepared statements (PDO) anti SQL Injection
- Protección XSS automática
- Autenticación JWT para API
- Sistema 2FA opcional
- Rate limiting en login y API

---

## 🏗️ Arquitectura

### Patrón de Diseño

Este sistema utiliza **Arquitectura Hexagonal (Clean Architecture)** combinada con **Domain-Driven Design (DDD)**:

```
┌─────────────────────────────────────────┐
│         INTERFACES (UI)                 │
│  Web Frontend │ Admin Panel │ API REST  │
└─────────────────────────────────────────┘
                  ▼
┌─────────────────────────────────────────┐
│       APPLICATION LAYER                 │
│  Controllers │ Middleware │ Validation  │
└─────────────────────────────────────────┘
                  ▼
┌─────────────────────────────────────────┐
│         DOMAIN LAYER                    │
│  Entities │ Services │ Repositories     │
└─────────────────────────────────────────┘
                  ▼
┌─────────────────────────────────────────┐
│      INFRASTRUCTURE LAYER               │
│  Database │ Email │ Storage │ Cache     │
└─────────────────────────────────────────┘
```

### Componentes Principales

1. **Core**: Framework base (Router, Container, Request/Response)
2. **Domain**: Lógica de negocio organizada por entidades
3. **Infrastructure**: Servicios de infraestructura (DB, Email, Storage)
4. **Plugins**: Sistema extensible de plugins

---

## 💻 Requisitos del Sistema

- **PHP**: 8.1 o superior
- **MySQL**: 8.0 o superior
- **Composer**: 2.x
- **Extensiones PHP**:
  - PDO
  - JSON
  - mbstring
  - cURL
  - GD o Imagick (para imágenes)

---

## 📦 Instalación

### 1. Clonar el repositorio

```bash
git clone https://github.com/your-repo/ecommerce-php.git
cd ecommerce-php
```

### 2. Instalar dependencias

```bash
composer install
```

### 3. Configurar el entorno

```bash
cp .env.example .env
```

Edita `.env` con tus credenciales:

```env
DB_HOST=127.0.0.1
DB_DATABASE=ecommerce
DB_USERNAME=root
DB_PASSWORD=tu_password
```

### 4. Importar la base de datos

```bash
mysql -u root -p ecommerce < ecommerce.sql
mysql -u root -p ecommerce < database_improvements.sql
```

### 5. Generar claves de seguridad

```bash
php cli/generate-key.php
```

Esto generará claves para `APP_KEY` y `JWT_SECRET` en tu archivo `.env`.

### 6. Configurar permisos

```bash
chmod -R 775 storage/
chmod -R 775 public/uploads/
```

### 7. Servidor de desarrollo

```bash
# Opción 1: PHP Built-in Server
php -S localhost:8000 -t public

# Opción 2: Laragon/XAMPP/Wamp
# Apunta tu virtual host a la carpeta 'public/'
```

Visita: `http://localhost:8000`

---

## ⚙️ Configuración

### Archivos de Configuración

Todos los archivos de configuración están en `/config`:

- `app.php` - Configuración general
- `database.php` - Conexiones a BD
- `mail.php` - Servidor SMTP
- `payment.php` - Gateways de pago
- `plugins.php` - Configuración de plugins

### Variables de Entorno Importantes

```env
# Aplicación
APP_NAME="Mi Tienda"
APP_ENV=production  # local, staging, production
APP_DEBUG=false

# Base de datos
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_DATABASE=ecommerce

# Email
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-password

# Pagos
STRIPE_SECRET_KEY=sk_live_...
PAYPAL_CLIENT_ID=...

# ERP
ERP_API_URL=https://erp.empresa.com/api
ERP_API_KEY=your-api-key
```

---

## 📁 Estructura de Carpetas

```
ecommerce/
├── app/
│   ├── Core/                    # Framework base
│   │   ├── Application.php
│   │   ├── Container.php
│   │   ├── Router.php
│   │   ├── Request.php
│   │   ├── Response.php
│   │   └── Plugin/              # Sistema de plugins
│   ├── Domain/                  # Lógica de negocio
│   │   ├── Product/
│   │   ├── Order/
│   │   ├── User/
│   │   └── Payment/
│   ├── Infrastructure/          # Servicios
│   └── Api/                     # API REST
│
├── config/                      # Archivos de configuración
├── database/                    # Migraciones y seeds
├── plugins/                     # Plugins instalados
│   └── PaymentGateways/
│       └── Stripe/
├── public/                      # Directorio público
│   ├── index.php               # Frontend
│   ├── admin.php               # Admin panel
│   ├── api.php                 # API REST
│   └── assets/
├── storage/                     # Logs, cache, uploads
├── views/                       # Templates
│   ├── frontend/
│   └── admin/
├── vendor/                      # Dependencias Composer
├── .env                        # Configuración local
├── composer.json
└── README.md
```

---

## 🎮 Uso Básico

### Frontend (Tienda)

**URL**: `http://localhost:8000/index.php`

Rutas principales:
- `/` - Página de inicio
- `/products` - Catálogo de productos
- `/products/{slug}` - Detalle de producto
- `/cart` - Carrito de compras
- `/checkout` - Proceso de pago
- `/account/login` - Login
- `/account/profile` - Perfil del usuario

### Panel de Administración

**URL**: `http://localhost:8000/admin.php`

Credenciales por defecto:
- Email: `admin@ecommerce.com`
- Password: `admin123` (¡Cámbialo inmediatamente!)

Secciones:
- Dashboard
- Productos
- Categorías
- Pedidos
- Inventario
- Usuarios
- Cupones
- Plugins
- Configuración

### API REST

**URL Base**: `http://localhost:8000/api.php/api/v1`

#### Autenticación

```bash
# Obtener JWT token
curl -X POST http://localhost:8000/api.php/api/v1/auth/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "user@example.com",
    "password": "password123"
  }'

# Respuesta
{
  "token": "eyJ0eXAiOiJKV1QiLCJhbGc...",
  "expires_in": 3600
}
```

#### Uso de la API

```bash
# Listar productos
curl -X GET http://localhost:8000/api.php/api/v1/products \
  -H "Authorization: Bearer YOUR_JWT_TOKEN"

# Crear pedido
curl -X POST http://localhost:8000/api.php/api/v1/orders \
  -H "Authorization: Bearer YOUR_JWT_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "items": [
      {"product_id": 1, "quantity": 2}
    ],
    "shipping_address": {...}
  }'
```

---

## 🔌 Sistema de Plugins

### Características

- ✅ Activación/desactivación sin código
- ✅ Sistema de hooks y eventos
- ✅ Configuración por plugin
- ✅ Gestión desde el admin panel

### Crear un Plugin

**Estructura básica**:

```
plugins/MiCategoria/MiPlugin/
├── plugin.json              # Metadatos
├── MiPlugin.php            # Clase principal
├── config.php              # Configuración
└── views/                  # Vistas (opcional)
```

**plugin.json**:

```json
{
  "name": "mi_plugin",
  "display_name": "Mi Plugin",
  "version": "1.0.0",
  "namespace": "Plugins\\MiCategoria\\MiPlugin",
  "main_class": "Plugins\\MiCategoria\\MiPlugin\\MiPlugin"
}
```

**MiPlugin.php**:

```php
<?php

namespace Plugins\MiCategoria\MiPlugin;

use App\Core\Plugin\PluginInterface;
use App\Core\Plugin\EventDispatcher;

class MiPlugin implements PluginInterface
{
    public function boot(): void
    {
        // Inicialización del plugin
    }

    public function registerHooks(EventDispatcher $dispatcher): void
    {
        // Registrar eventos
        $dispatcher->listen('product.created', function($product) {
            // Hacer algo cuando se crea un producto
        }, 10);
    }

    // ... implementar métodos de PluginInterface
}
```

### Eventos Disponibles

```php
// Productos
'product.before_create'
'product.after_create'
'product.before_update'
'product.after_update'

// Pedidos
'order.before_checkout'
'order.after_payment'
'order.status_changed'

// Usuarios
'user.login'
'user.register'
'user.logout'

// Carrito
'cart.item_added'
'cart.item_removed'

// Pagos
'payment.process'
'payment.completed'
'payment.failed'
```

---

## 🌐 API REST

### Endpoints Principales

#### Productos

```
GET    /api/v1/products           # Listar
GET    /api/v1/products/{id}      # Obtener
POST   /api/v1/products           # Crear
PUT    /api/v1/products/{id}      # Actualizar
DELETE /api/v1/products/{id}      # Eliminar
```

#### Inventario

```
GET    /api/v1/inventory          # Listar
PUT    /api/v1/inventory/{id}     # Actualizar stock
POST   /api/v1/inventory/sync     # Sincronizar
```

#### Pedidos

```
GET    /api/v1/orders             # Listar
GET    /api/v1/orders/{id}        # Obtener
POST   /api/v1/orders             # Crear
PUT    /api/v1/orders/{id}/status # Actualizar estado
```

### Rate Limiting

- **Usuarios autenticados**: 1000 requests/hora
- **IP sin autenticar**: 100 requests/hora

---

## 🔄 Integración con ERP

### Configuración

En `.env`:

```env
ERP_API_URL=https://erp.tuempresa.com/api
ERP_API_KEY=your_erp_api_key
ERP_SYNC_ENABLED=true
ERP_SYNC_INTERVAL=300  # segundos
```

### Endpoints de Sincronización

```
POST /api/v1/erp/products/import    # Importar productos del ERP
POST /api/v1/erp/inventory/sync     # Sincronizar inventario
POST /api/v1/erp/orders/export      # Exportar pedidos al ERP
GET  /api/v1/erp/sync/status        # Estado de sincronización
```

### Mapeo de IDs

El sistema mantiene un mapeo entre IDs locales y IDs del ERP en la tabla `erp_mappings`:

```sql
SELECT * FROM erp_mappings 
WHERE entity_type = 'product' AND local_id = 123;
```

---

## 🔒 Seguridad

### Mejores Prácticas Implementadas

1. **Autenticación**:
   - Passwords hasheados con bcrypt
   - Soporte para 2FA
   - Bloqueo tras intentos fallidos

2. **Autorización**:
   - Sistema de roles y permisos
   - Middleware de autenticación
   - Control de acceso granular

3. **Datos**:
   - Prepared statements (PDO)
   - Validación de entrada
   - Sanitización de salida (XSS)
   - CSRF tokens en formularios

4. **API**:
   - JWT para autenticación
   - Rate limiting
   - Verificación de firmas en webhooks

### Checklist de Seguridad

- [ ] Cambiar credenciales por defecto
- [ ] Generar nuevas claves APP_KEY y JWT_SECRET
- [ ] Configurar HTTPS en producción
- [ ] Establecer permisos correctos en carpetas
- [ ] Habilitar 2FA para administradores
- [ ] Configurar firewall y fail2ban
- [ ] Hacer backups regulares de la BD
- [ ] Mantener PHP y dependencias actualizadas

---

## 🧪 Testing

```bash
# Ejecutar todos los tests
composer test

# Con coverage
composer test -- --coverage

# Test específico
vendor/bin/phpunit tests/Unit/ProductTest.php
```

---

## 🤝 Contribuir

1. Fork el proyecto
2. Crea una rama para tu feature (`git checkout -b feature/AmazingFeature`)
3. Commit tus cambios (`git commit -m 'Add some AmazingFeature'`)
4. Push a la rama (`git push origin feature/AmazingFeature`)
5. Abre un Pull Request

---

## 📝 Licencia

Este proyecto está bajo la licencia MIT. Ver archivo `LICENSE` para más detalles.

---

## 💬 Soporte

- **Documentación**: [docs.ecommerce.com](https://docs.ecommerce.com)
- **Issues**: [GitHub Issues](https://github.com/your-repo/issues)
- **Email**: support@ecommerce.com

---

## 🙏 Agradecimientos

- Comunidad PHP
- Contribuidores del proyecto
- Todos los que usan este sistema

---

**Hecho con ❤️ en PHP puro**
