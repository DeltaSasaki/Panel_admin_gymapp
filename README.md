# 🏋️‍♂️ GymOS - Panel de Administración Multi-Sucursal para Gimnasios & SaaS

[![Laravel](https://img.shields.io/badge/Laravel-12.0-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.2+-777BB4?style=for-the-badge&logo=php&logoColor=white)](https://php.net)
[![Tailwind CSS](https://img.shields.io/badge/Tailwind_CSS-4.0-06B6D4?style=for-the-badge&logo=tailwindcss&logoColor=white)](https://tailwindcss.com)
[![Vite](https://img.shields.io/badge/Vite-7.0-646CFF?style=for-the-badge&logo=vite&logoColor=white)](https://vitejs.dev)
[![Architecture](https://img.shields.io/badge/Architecture-Multi--tenant_SaaS-00E676?style=for-the-badge)](https://laravel.com)

**GymOS** es una plataforma integral de gestión administrativa, logística y operativa desarrollada en **Laravel 12**, diseñada para cadenas de gimnasios, clubes deportivos y centros de entrenamiento personalizado. Cuenta con una arquitectura **Multi-tenant (Multi-sucursal)** nativa que permite administrar múltiples sedes en un entorno SaaS centralizado.

---

## 🌟 Características Principales

### 🛡️ 1. Arquitectura Multi-tenant & SaaS Superadmin
* **Segregación Estricta de Datos:** Cada entidad (`users`, `rutinas`, `pagos`, `productos`, `asistencia`) responde al contexto de un `gym_id`.
* **Switch de Sucursal en Tiempo Real (`switchGym`):** Los usuarios con rol `superadmin` pueden alternar el contexto activo para auditar un gimnasio específico o ver el consolidado global (`all`).
* **Control de Planes SaaS (`SaasSubscriptionPlan`):** Suscripciones por gimnasio con límites máximos de usuarios (`max_users`) y entrenadores (`max_trainers`).
* **Bloqueo Automático por Impago (`CheckGymActive` Middleware):** Si una sucursal pasa a estado inactivo (`is_active = 0`), el sistema invalida automáticamente la sesión de los usuarios que no sean `superadmin`.
* **Trazabilidad & Auditoría (`AdminAuditLog`):** Registro detallado de operaciones (`INSERT`, `UPDATE`, `DELETE`, `LOGIN_FAILED`) almacenando IP, dispositivo, estado previo y posterior en JSON.

---

### 👥 2. Gestión de Socios & Expediente Corporal
* Expedientes completos de clientes con historial de medidas antropométricas (`BodyMeasurement`: peso, % de grasa, masa muscular, perímetros).
* Asignación directa de **Entrenador Personal**, **Rutina Activa** y **Plan Nutricional**.

---

### 🏋️ 3. Rutinas de Entrenamiento & Catálogo de Ejercicios
* Constructor de rutinas desglosado por días de entrenamiento.
* Definición de series, repeticiones, peso asignado y descansos entre series.
* Catálogo de ejercicios categorizados con material multimedia demostrativo y músculos trabajados.

---

### 🥗 4. Nutrición & Recetario Macro-calculado
* Diseñador de planes de alimentación personalizados con cálculo de macronutrientes (Proteínas, Carbohidratos, Grasas y Calorías).
* Catálogo de ingredientes y biblioteca de recetas estructuradas por momentos del día (Desayuno, Almuerzo, Cena, Snacks).

---

### 💳 5. Finanzas, Membresías & Cupones
* Gestión de planes de membresía (mensual, trimestral, anual).
* Historial de cobros, renovación de suscripciones y balance de ingresos mensuales.
* Validador en vivo de **Códigos Promocionales (`PromoCode`)** mediante API AJAX.

---

### 🛒 6. Tienda, Inventario & Punto de Venta (POS)
* **Punto de Venta (POS) Integrado:** Interfaz rápida para recepción y entrenadores para el cobro de productos, suplementos y bebidas.
* Control de Kardex/Inventario con movimientos de entrada/salida y alertas automáticas de **stock bajo** (`stock_quantity <= min_stock`).

---

### 🎟️ 7. Control de Asistencia, Aforo & Triggers SQL
* Búsqueda ágil de clientes por DNI/Código para Check-in y Check-out.
* **Trigger SQL de Seguridad (`trg_validate_attendance_membership`):** Rechaza el acceso a nivel de base de datos si el socio no cuenta con una membresía activa y pagada para el día en curso.
* Monitor de **Aforo en Tiempo Real** integrado en la barra de navegación basado en la capacidad contratada en el plan SaaS.

---

### 📅 8. Clases Grupales & Reservas
* Programación de horarios de clases grupales (Spinning, Yoga, Crossfit, etc.) con cupos máximos.
* Motor de reservas para clientes y control de asistencia por clase.

---

### 🏆 9. Gamificación, Retos & Recompensas
* Sistema de retos y desafíos por metas de asistencia o entrenamiento.
* Recompensas con **Puntos de Experiencia (XP)** y **Tokens/Moneda Virtual** canjeable.
* Asignación de **Medallas de Logros (`AchievementDefinition`)**.

---

# 🛠️ Stack Tecnológico

| Capa | Tecnología / Herramienta |
| :--- | :--- |
| **Framework Backend** | PHP 8.2+ / Laravel 12.0 |
| **Arquitectura de UI** | Blade Templating Engine |
| **Estilos & Diseño** | Tailwind CSS 4.0 + Font Plus Jakarta Sans |
| **Bundler & Frontend Assets** | Vite 7.0 + Axios (AJAX) |
| **Iconografía & Componentes** | Lucide Icons + SweetAlert2 + Chart.js |
| **Base de Datos & ORM** | MariaDB / MySQL + Eloquent ORM |
| **Seguridad & Auditoría** | Custom Middleware (`CheckGymActive`) + Triggers SQL + `AdminAuditLog` |

---

# 📁 Estructura del Proyecto

```text
Panel_admin_gymapp/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── AdminController.php         # Gestión de socios, rutinas, nutrición y métricas
│   │   │   ├── AttendanceController.php    # Control de check-in / check-out por DNI
│   │   │   ├── AuthController.php          # Login, logout y switch de sucursal
│   │   │   ├── CatalogController.php       # Catálogos de ejercicios, equipos y recetas
│   │   │   ├── ClassController.php         # Clases grupales y reservas
│   │   │   ├── FinanceController.php       # Membresías, cobros y promociones
│   │   │   ├── GamificationController.php  # Retos, medallas y puntos XP
│   │   │   ├── GymController.php           # Consola SaaS Superadmin y sucursales
│   │   │   ├── InventoryController.php     # POS Tienda, productos y kardex
│   │   │   └── StaffController.php         # Control del personal y entrenadores
│   │   └── Middleware/
│   │       └── CheckGymActive.php          # Verificación de sucursal activa
│   └── Models/                             # Modelos Eloquent de la plataforma (40+ tablas)
├── bootstrap/
│   └── app.php                             # Configuración central de rutas y middleware Laravel 12
├── database/
│   └── migrations/                         # Migraciones base
├── gymapp.sql                              # Estructura y triggers de la base de datos MySQL/MariaDB
├── public/                                 # Puntos de entrada públicos y subida de archivos
├── resources/
│   ├── css/                                # Archivos CSS / Tailwind
│   ├── js/                                 # Scripts JS / Axios
│   └── views/                              # Plantillas Blade estructuradas por módulos
│       ├── layouts/admin.blade.php         # Layout principal del panel
│       └── superadmin/                     # Vistas exclusivas de Superadmin
└── routes/
    └── web.php                             # Rutas web centralizadas del sistema
```

---

# 🚀 Instalación y Configuración Local

### 1. Requisitos Previos
* PHP `>= 8.2`
* Composer `>= 2.5`
* Node.js `>= 18.x` & npm
* Servidor MySQL / MariaDB

### 2. Pasos de Instalación

1. **Clonar el repositorio:**
   ```bash
   git clone https://github.com/tu-usuario/panel_admin_gymapp.git
   cd panel_admin_gymapp
   ```

2. **Instalar dependencias de PHP:**
   ```bash
   composer install
   ```

3. **Instalar dependencias de JavaScript:**
   ```bash
   npm install
   ```

4. **Configurar el archivo de entorno (`.env`):**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

5. **Configurar la base de datos en `.env`:**
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=gymapp
   DB_USERNAME=root
   DB_PASSWORD=
   ```

6. **Importar la Base de Datos:**
   Importa el archivo `gymapp.sql` en tu gestor de base de datos MySQL/MariaDB para cargar las tablas, procedimientos y triggers requeridos.

7. **Compilar recursos de Vite:**
   ```bash
   npm run dev
   ```

8. **Iniciar el servidor de desarrollo:**
   ```bash
   php artisan serve
   ```

---

# ⚙️ Comandos Útiles

* **Ejecutar servidor de desarrollo con listeners integrados:**
  ```bash
  composer run dev
  ```
* **Compilar activos para producción:**
  ```bash
  npm run build
  ```
* **Limpiar caché de configuración y rutas:**
  ```bash
  php artisan config:clear
  php artisan route:clear
  ```

---

# 🔒 Licencia

Este proyecto es software privado desarrollado para **Corpo Asia / GymOS**. Todos los derechos reservados.
