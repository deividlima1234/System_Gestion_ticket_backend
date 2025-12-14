# System Gestión de Tickets (Backend)

## 📋 Descripción General

Este proyecto consiste en una **API RESTful** robusta y escalable desarrollada con **Laravel 11**, diseñada para gestionar un sistema de tickets de soporte técnico. La arquitectura está desacoplada, permitiendo que cualquier cliente (como una SPA en React) consuma los servicios de manera eficiente.

### 🚀 Características Principales

*   **Arquitectura Modular:** Desarrollo organizado en módulos independientes (Auth, Tickets, Roles, Notificaciones).
*   **Autenticación Segura:** Implementada con **Laravel Sanctum** (Tokens Bearer).
*   **Control de Acceso (RBAC):** Roles definidos (`user`, `support`, `admin`) con middleware personalizado.
*   **Gestión de Tickets:** CRUD completo con estados, prioridades y asignación de técnicos.
*   **Sistema de Comentarios:** Hilo de conversación por ticket.
*   **Notificaciones:** Envío de correos electrónicos (simulado en logs) ante eventos críticos.

---

## 🛠️ Requisitos del Sistema

*   PHP >= 8.2
*   Composer
*   PostgreSQL
*   Node.js & NPM (opcional, para assets si fuera necesario)

---

## ⚙️ Guía de Instalación

Sigue estos pasos para desplegar el entorno de desarrollo local:

1.  **Clonar el repositorio**
    ```bash
    git clone https://github.com/deividlima1234/System_Gestion_ticket_backend
    cd System_Gestion_ticket_backend
    ```

2.  **Instalar dependencias de PHP**
    ```bash
    composer install
    ```

3.  **Configurar variables de entorno**
    ```bash
    cp .env.example .env
    ```
    Edita el archivo `.env` y configura tu conexión a base de datos:
    ```env
    DB_CONNECTION=pgsql
    DB_HOST=127.0.0.1
    DB_PORT=5432
    DB_DATABASE=gestion_tickets
    DB_USERNAME=postgres
    DB_PASSWORD=tu_password
    ```

4.  **Generar clave de aplicación**
    ```bash
    php artisan key:generate
    ```

5.  **Ejecutar migraciones**
    Esto creará las tablas necesarias en tu base de datos PostgreSQL.
    ```bash
    php artisan migrate
    ```

---

## ▶️ Ejecución del Servidor

Para iniciar el servidor de desarrollo local:

```bash
php artisan serve
```

La API estará disponible en: `http://127.0.0.1:8000`

---

## 📚 Documentación de la API y Pruebas

A continuación se detallan los endpoints principales con ejemplos de cómo probarlos usando `curl`.

### 1. Autenticación

#### 🔐 Login
Obtiene un token de acceso.

*   **Endpoint:** `POST /api/v1/login`
*   **Body:**
    ```json
    {
        "email": "test@example.com",
        "password": "password"
    }
    ```

**Prueba (Curl):**
```bash
curl -X POST http://127.0.0.1:8000/api/v1/login \
-H "Content-Type: application/json" \
-d '{"email":"test@example.com", "password":"password"}'
```

**Respuesta Esperada (200 OK):**
```json
{
    "access_token": "1|...token_hash...",
    "token_type": "Bearer"
}
```

> **Nota:** Copia el `access_token` recibido, lo necesitarás para las siguientes peticiones en el header `Authorization`.

---

### 2. Gestión de Tickets

#### 📝 Crear Ticket
*   **Endpoint:** `POST /api/v1/tickets`
*   **Headers:** `Authorization: Bearer <TOKEN>`
*   **Body:**
    ```json
    {
        "title": "Fallo en impresora",
        "description": "La impresora del piso 2 no responde.",
        "priority": "high"
    }
    ```

**Prueba (Curl):**
```bash
curl -X POST http://127.0.0.1:8000/api/v1/tickets \
-H "Authorization: Bearer <TOKEN>" \
-H "Content-Type: application/json" \
-d '{"title":"Fallo en impresora", "description":"La impresora del piso 2 no responde.", "priority":"high"}'
```

#### 📋 Listar Tickets
*   **Endpoint:** `GET /api/v1/tickets`
*   **Headers:** `Authorization: Bearer <TOKEN>`
*   **Regla:** Los usuarios normales ven solo sus tickets. Admin/Soporte ven todos.

**Prueba (Curl):**
```bash
curl -X GET http://127.0.0.1:8000/api/v1/tickets \
-H "Authorization: Bearer <TOKEN>" \
-H "Content-Type: application/json"
```

#### 🔄 Actualizar Estado (Solo Admin/Soporte)
*   **Endpoint:** `PUT /api/v1/tickets/{id}`
*   **Headers:** `Authorization: Bearer <TOKEN_ADMIN>`
*   **Body:**
    ```json
    {
        "status": "in_progress"
    }
    ```

**Prueba (Curl):**
```bash
curl -X PUT http://127.0.0.1:8000/api/v1/tickets/1 \
-H "Authorization: Bearer <TOKEN_ADMIN>" \
-H "Content-Type: application/json" \
-d '{"status":"in_progress"}'
```

---

### 3. Comentarios y Asignación

#### 💬 Agregar Comentario
*   **Endpoint:** `POST /api/v1/tickets/{id}/comments`
*   **Headers:** `Authorization: Bearer <TOKEN>`
*   **Body:**
    ```json
    {
        "content": "Ya reinicié el equipo y sigue igual."
    }
    ```

**Prueba (Curl):**
```bash
curl -X POST http://127.0.0.1:8000/api/v1/tickets/1/comments \
-H "Authorization: Bearer <TOKEN>" \
-H "Content-Type: application/json" \
-d '{"content":"Ya reinicié el equipo y sigue igual."}'
```

#### 👤 Asignar Técnico (Solo Admin/Soporte)
*   **Endpoint:** `PUT /api/v1/tickets/{id}/assign`
*   **Headers:** `Authorization: Bearer <TOKEN_ADMIN>`
*   **Body:**
    ```json
    {
        "assigned_to": 2
    }
    ```

**Prueba (Curl):**
```bash
curl -X PUT http://127.0.0.1:8000/api/v1/tickets/1/assign \
-H "Authorization: Bearer <TOKEN_ADMIN>" \
-H "Content-Type: application/json" \
-d '{"assigned_to":2}'
```

---

## 📧 Notificaciones (Logs)

El sistema está configurado para usar el driver `log` para correos electrónicos en entorno local.
Puedes verificar las notificaciones enviadas (Creación de Ticket, Cambio de Estado) revisando el archivo de logs:

```bash
tail -f storage/logs/laravel.log
```

---

## 🧪 Usuarios de Prueba (Seeders)

Puedes crear usuarios manualmente usando `php artisan tinker`:

```php
// Usuario Normal
User::factory()->create([
    'name' => 'Test User',
    'email' => 'user@example.com',
    'password' => bcrypt('password'),
    'role' => 'user'
]);

// Administrador
User::factory()->create([
    'name' => 'Admin User',
    'email' => 'admin@example.com',
    'password' => bcrypt('password'),
    'role' => 'admin'
]);
```
