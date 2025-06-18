---
title: "Caso 1"
author: "Bryan Cordero"
date: "2025-06-10"
---

# Manual de Usuario – API REST de Gestión de Pedidos

## Requisitos Previos

- Tener instalado XAMPP (o similar) con:
  - Apache
  - MySQL
  - PHP >= 7.4
- Herramienta de pruebas HTTP como [Insomnia](https://insomnia.rest/) o [Postman](https://www.postman.com/)
- Clave de autenticación (API Key) definida en `auth/apikey.php`

## Instalación y Configuración

### 1. Iniciar Servicios

Inicia **Apache** y **MySQL** desde XAMPP.

### 2. Crear Base de Datos

En phpMyAdmin o consola de MySQL:

```sql
CREATE DATABASE gestion_pedidos;

USE gestion_pedidos;

CREATE TABLE pedidos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cliente VARCHAR(255) NOT NULL,
    producto VARCHAR(255) NOT NULL,
    cantidad INT NOT NULL,
    estado ENUM('pendiente', 'en proceso', 'enviado', 'entregado') DEFAULT 'pendiente',
    fecha_pedido TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

## Ejemplos de Prueba (Insomnia / Postman)

### 1. Crear un Pedido

- **Método:** `POST`
- **URL:** `http://localhost/api/pedidos`
- **Headers:**
  - `Content-Type: application/json`
  - `X-API-KEY: tu_api_key`
- **Body (JSON):**

```json
{
  "cliente": "Bryan Cordero",
  "producto": "Teclado",
  "cantidad": 2
}
```

### 2. Obtener Todos los Pedidos

- **Método:** `GET`
- **URL:** `http://localhost/api/pedidos`
- **Headers:**
  - `X-API-KEY: tu_api_key`

### 3. Obtener Pedido por ID

- **Método:** `GET`
- **URL:** `http://localhost/api/pedidos/1`
- **Headers:**
  - `X-API-KEY: tu_api_key`

### 4. Actualizar Estado de un Pedido

- **Método:** `PUT`
- **URL:** `http://localhost/api/pedidos/1`
- **Headers:**
  - `Content-Type: application/x-www-form-urlencoded`
  - `X-API-KEY: tu_api_key`
- **Body (x-www-form-urlencoded):**- **estado=enviado**

## Mensajes de Error

| Código | Descripción del Error                      | Mensaje Devuelto                      |
| ------ | ------------------------------------------ | ------------------------------------- |
| 401    | API Key incorrecta o ausente               | "Acceso no autorizado"                |
| 400    | Datos incompletos                          | "Datos incompletos"                   |
| 404    | Pedido no encontrado                       | "Pedido no encontrado"                |
| 400    | Estado no enviado en PUT                   | "Estado no proporcionado"             |
| 400    | Pedido no se puede eliminar (no pendiente) | "No se puede eliminar: estado actual" |

## Recomendaciones

- Proteger el servidor con **HTTPS** en producción.
- Validar los datos en el **frontend** antes de enviarlos.
- Cambiar la **API Key** periódicamente.
- Documentar cualquier cambio en los **endpoints** o en la estructura de datos.
