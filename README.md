# AdmiNET Web (OpenSource)

**AdmiNET Web** es un sistema de gestión y control para WISP e ISP desarrollado con tecnologías web y pensado para correr en entornos locales o en la nube. Este proyecto ha sido liberado como software de código abierto con el objetivo de que la comunidad WISP/ISP pueda mejorar, expandir y adaptar el sistema a sus necesidades reales. 🚀
![clientes](https://github.com/user-attachments/assets/48c01da9-f29b-49a1-9bd0-4a9f9c7aba55)

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
![zzzz](https://github.com/user-attachments/assets/209ef4aa-26f5-4106-9fa3-c0f418f09d43)

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
![Captura desde 2025-06-01 01-50-53](https://github.com/user-attachments/assets/b30492ac-16e1-4c03-8b9f-2ee8ee1e045d)
![Captura desde 2025-06-01 01-54-47](https://github.com/user-attachments/assets/44313227-cc4b-466b-a6e3-da80868af965)
![Captura desde 2025-06-01 01-54-54](https://github.com/user-attachments/assets/cd457fb1-28f4-4991-9b03-01c13330bc31)
![dashboard](https://github.com/user-attachments/assets/31847147-0b5d-4676-b78e-ffa80e2a39ac)
![mapa](https://github.com/user-attachments/assets/6a4f4a0d-dd97-4239-a7b9-13a2d78a9bbe)
![Captura desde 2025-06-01 01-51-41](https://github.com/user-attachments/assets/542fe28d-717d-4a70-8536-107b27c19b62)

¡Contribuye, mejora y transforma el futuro de los WISP con AdmiNET Web! 🌐🛠️
