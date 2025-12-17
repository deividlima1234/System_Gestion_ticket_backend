# System Gestión de Tickets (Backend)

![Build Status](https://img.shields.io/badge/build-passing-brightgreen)
![License](https://img.shields.io/badge/license-MIT-blue)
![PHP Version](https://img.shields.io/badge/php-%5E8.2-777BB4)
![Laravel Version](https://img.shields.io/badge/laravel-%5E11.0-FF2D20)

## 📋 Descripción General

Este proyecto es una **API RESTful** robusta y escalable desarrollada con **Laravel 11**, diseñada para gestionar un sistema de tickets de soporte técnico eficiente. Su arquitectura modular y desacoplada permite una integración fluida con diversos clientes frontend (como SPAs en React o Vue), garantizando un rendimiento óptimo y una fácil mantenibilidad.

El sistema está construido siguiendo las mejores prácticas de desarrollo, incluyendo autenticación segura, control de acceso basado en roles (RBAC) y una estructura de base de datos normalizada.

### 🚀 Características Principales

#### 🔐 Autenticación y Seguridad
*   **Laravel Sanctum:** Implementación de tokens Bearer para una autenticación segura y ligera.
*   **Protección de Rutas:** Middleware personalizado para asegurar endpoints sensibles.

#### 👥 Gestión de Usuarios y Roles (RBAC)
*   **Roles Definidos:**
    *   `admin`: Control total del sistema, gestión de usuarios y tickets.
    *   `support`: Gestión de tickets y asignaciones.
    *   `user`: Creación y seguimiento de sus propios tickets.
*   **Gestión de Perfiles:** Actualización de información personal y credenciales.

#### 🎫 Sistema de Tickets
*   **Ciclo de Vida Completo:** Creación, actualización, asignación y cierre de tickets.
*   **Estados Personalizables:** `open`, `in_progress`, `pending`, `resolved`, `closed`.
*   **Priorización:** Clasificación por niveles (`low`, `medium`, `high`) para una mejor gestión del SLA.
*   **Asignación Inteligente:** Capacidad de asignar tickets a técnicos específicos.

#### 💬 Colaboración
*   **Hilo de Comentarios:** Comunicación fluida dentro de cada ticket entre usuarios y soporte.
*   **Historial:** Registro de interacciones para auditoría y seguimiento.

---

## 🛠️ Stack Tecnológico

*   **Lenguaje:** PHP 8.2+
*   **Framework:** Laravel 11
*   **Base de Datos:** PostgreSQL
*   **Containerización:** Docker (opcional para desarrollo)
*   **Servidor Web:** Apache / Nginx

---

## ⚙️ Guía de Instalación

### Requisitos Previos
Asegúrate de tener instalado lo siguiente en tu entorno:
*   [PHP](https://www.php.net/) >= 8.2
*   [Composer](https://getcomposer.org/)
*   [PostgreSQL](https://www.postgresql.org/)
*   [Git](https://git-scm.com/)

### Instalación Local

1.  **Clonar el repositorio**
    ```bash
    git clone https://github.com/deividlima1234/System_Gestion_ticket_backend.git
    cd System_Gestion_ticket_backend
    ```

2.  **Instalar dependencias**
    ```bash
    composer install
    ```

3.  **Configurar entorno**
    Copia el archivo de ejemplo y configura tus credenciales de base de datos.
    ```bash
    cp .env.example .env
    ```
    Edita el archivo `.env`:
    ```env
    DB_CONNECTION=pgsql
    DB_HOST=127.0.0.1
    DB_PORT=5432
    DB_DATABASE=gestion_tickets
    DB_USERNAME=tu_usuario
    DB_PASSWORD=tu_password
    ```

4.  **Generar Key de Aplicación**
    ```bash
    php artisan key:generate
    ```

5.  **Ejecutar Migraciones**
    Crea las tablas en la base de datos.
    ```bash
    php artisan migrate
    ```

6.  **Iniciar Servidor**
    ```bash
    php artisan serve
    ```
    La API estará disponible en `http://127.0.0.1:8000`.

### 🐳 Instalación con Docker

Si prefieres usar Docker, el proyecto incluye un `Dockerfile` listo para usar.

1.  **Construir la imagen**
    ```bash
    docker build -t ticket-backend .
    ```

2.  **Ejecutar el contenedor**
    ```bash
    docker run -p 8000:80 ticket-backend
    ```

---

## 📚 Documentación de la API

A continuación se detallan los endpoints principales. Para probarlos, asegúrate de incluir el header `Accept: application/json`.

### 🔐 Autenticación

| Método | Endpoint | Descripción |
| :--- | :--- | :--- |
| `POST` | `/api/v1/login` | Iniciar sesión y obtener token. |
| `POST` | `/api/v1/logout` | Cerrar sesión (Requiere Token). |
| `GET` | `/api/v1/user` | Obtener usuario autenticado. |

**Ejemplo Login:**
```bash
curl -X POST http://127.0.0.1:8000/api/v1/login \
-H "Content-Type: application/json" \
-d '{"email":"admin@example.com", "password":"password"}'
```

### 🎫 Tickets

| Método | Endpoint | Descripción | Acceso |
| :--- | :--- | :--- | :--- |
| `GET` | `/api/v1/tickets` | Listar tickets. | Todos (Filtros según rol) |
| `POST` | `/api/v1/tickets` | Crear nuevo ticket. | Todos |
| `GET` | `/api/v1/tickets/{id}` | Ver detalles de un ticket. | Dueño / Support / Admin |
| `PUT` | `/api/v1/tickets/{id}` | Actualizar ticket. | Dueño / Support / Admin |
| `DELETE` | `/api/v1/tickets/{id}` | Eliminar ticket. | Admin |
| `PUT` | `/api/v1/tickets/{id}/assign` | Asignar técnico. | Support / Admin |

**Ejemplo Crear Ticket:**
```bash
curl -X POST http://127.0.0.1:8000/api/v1/tickets \
-H "Authorization: Bearer <TOKEN>" \
-H "Content-Type: application/json" \
-d '{"title":"Error en Login", "description":"No puedo acceder...", "priority":"high"}'
```

### 💬 Comentarios

| Método | Endpoint | Descripción |
| :--- | :--- | :--- |
| `GET` | `/api/v1/tickets/{id}/comments` | Ver comentarios de un ticket. |
| `POST` | `/api/v1/tickets/{id}/comments` | Agregar comentario. |

### 👥 Usuarios (Admin Only)

| Método | Endpoint | Descripción |
| :--- | :--- | :--- |
| `GET` | `/api/v1/users` | Listar todos los usuarios. |
| `POST` | `/api/v1/users` | Crear usuario (Soporte/Admin). |
| `PUT` | `/api/v1/users/{id}` | Actualizar usuario. |
| `DELETE` | `/api/v1/users/{id}` | Eliminar usuario. |

---

## 🗄️ Esquema de Base de Datos

El sistema utiliza las siguientes tablas principales:

*   **users**: Almacena la información de usuarios y sus roles (`role`).
*   **tickets**: Contiene la información de los tickets, estado (`status`), prioridad (`priority`) y relaciones con usuarios (`user_id`, `assigned_to`).
*   **comments**: Almacena los mensajes asociados a cada ticket.
*   **personal_access_tokens**: Tabla de Laravel Sanctum para gestión de tokens API.

---

## ✅ Testing

Para ejecutar las pruebas automatizadas del sistema:

```bash
php artisan test
```

---

## 📄 Licencia

Este proyecto está bajo la licencia [MIT](https://opensource.org/licenses/MIT).

<div align="center">
  <sub>Desarrollado con ❤️ por Eddam_code.</sub>
</div>