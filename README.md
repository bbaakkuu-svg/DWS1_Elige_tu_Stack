# DWS1 - Elige tu Stack

Práctica de la Unidad 1 del módulo **Desarrollo Web en Entorno Servidor (DWES)** &mdash; Grado Superior en DAW.

## Descripción del Proyecto

Análisis y justificación arquitectónica de backend para la plataforma de comercio de proximidad **EcoCercano**, complementado con una prueba de concepto en **PHP 8**.

## Contenido del Repositorio

- **`Informe_Consultoria.md`**: Informe técnico de arquitectura que analiza:
  - Modelos de ejecución (Cliente vs. Servidor) y principio de desconfianza en datos del cliente.
  - Justificación de web dinámica vs. estática en e-commerce local (stock perecedero y concurrencia).
  - Infraestructura de servidores: Apache/Nginx, evolución de CGI a PHP-FPM y Laravel como servidor de aplicaciones.
  - Selección del stack: PHP 8+ y Laravel 12 (MVC, Eloquent, migraciones y mitigación de SQLi, CSRF y XSS).
- **`fichero_demostracion.php`**: Script en PHP 8 de generación dinámica en servidor:
  - Configuración de zona horaria (`Europe/Madrid`).
  - Renderizado dinámico de métricas del entorno en tiempo real (fecha, hora, versión de PHP y software web).
  - Captura y simulación de entrada de usuario mediante parámetro `GET`.
  - Sanitización de salidas en servidor mediante `htmlspecialchars()` para prevenir ataques XSS.

## Despliegue Local (XAMPP)

1. **Requisitos**: Servidor web con PHP 8+ (verificado en XAMPP con Apache 2.4 y PHP 8.2).
2. **Ubicación**: Situar la carpeta del proyecto en el directorio `htdocs`:
   ```text
   C:\xampp\htdocs\DWS1_Elige_tu_Stack
   ```
3. **Ejecución**: Iniciar **Apache** en el Panel de Control de XAMPP.
4. **Acceso**: Navegar a:
   ```
   http://localhost/DWS1_Elige_tu_Stack/fichero_demostracion.php
   ```
