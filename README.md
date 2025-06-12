# AdmiNET Web (OpenSource)

**AdmiNET Web** es un sistema de gestión y control para WISP e ISP desarrollado con tecnologías web y pensado para correr en entornos locales o en la nube. Este proyecto ha sido liberado como software de código abierto con el objetivo de que la comunidad WISP/ISP pueda mejorar, expandir y adaptar el sistema a sus necesidades reales. 🚀

## Tecnologías Utilizadas

- **Frontend:** HTML, CSS, JavaScript, Bootstrap
- **Backend:** PHP, Python (para automatización y cortes)
- **Base de datos:** MySQL
- **Conexión a MikroTik:** vía SSH (puerto 22 abierto requerido)

## Estructura del Proyecto

- Todo el código fuente debe estar contenido dentro de la carpeta principal: `ded/`
- La base de datos principal `adminet_test` está incluida en: `ded/config/db.sql`
- También se requiere una segunda base de datos llamada `adminet_global`, donde se define:
  - Usuario del sistema
  - Contraseña
  - Nombre de la base de datos (usualmente `adminet_test`)
- La conexión se carga dinámicamente al iniciar sesión.

## Requisitos

- Tener permisos de lectura/escritura adecuados en la carpeta `evidencia/` (necesario para el módulo de tickets).
- Reemplazar las credenciales de conexión (`user`, `password`, `host`, `port`, `database`) en el archivo de configuración según tu sistema local o VPS.
- Puerto **22** habilitado en MikroTik para conexión por **SSH**.

## Recomendaciones

- Para conexión entre MikroTiks y el servidor (local o en la nube), se recomienda configurar una **VPN WireGuard**, compatible con RouterOS 7.
- Puede ejecutarse en:
  - **XAMPP** (Windows/macOS)
  - **VPS Linux** (Apache + MySQL + PHP + Python)

## Automatización de Cortes y Activaciones

Incluye el script `adminet_cortes_system.py` que automatiza los cortes y activaciones de clientes.

- Requiere tener creadas ambas bases de datos: `adminet_global` y `adminet_test`
- Este script se puede configurar como **servicio de Windows o Linux**

## Licencia y Uso

> ⚠️ **IMPORTANTE:**  
> Este proyecto es **código abierto** para fines **educativos y comunitarios**.  
> Queda **prohibido** utilizar el sistema total o parcialmente con fines **comerciales** o de **lucro empresarial**.  

✅ Se permite:
- Modificar el código
- Usarlo para administrar tu WISP/ISP
- Compartir mejoras y propuestas

🚫 No se permite:
- Revender o distribuir el sistema con fines comerciales
- Quitar los créditos al autor original

## Créditos

Este sistema fue desarrollado por **Software Escobedo / Richard García Escobedo** como una herramienta libre para mejorar la gestión de redes WISP/ISP en Latinoamérica y el mundo.

> **Cualquier uso o distribución debe conservar estos créditos.**

---

¡Contribuye, mejora y transforma el futuro de los WISP con AdmiNET Web! 🌐🛠️