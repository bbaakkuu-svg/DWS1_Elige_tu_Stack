1. Cliente vs. Servidor: 
  Explica la diferencia entre el código que se ejecuta en el navegador del usuario y el que se procesa en la máquina remota. Incluye una justificación de por qué la "regla de oro" de la seguridad web es "nunca confiar en los datos que vienen del cliente".
Por el lado del cliente:
El código que se ejecuta en el navegador (html, css y js) es visible e interpretado por el motor del navegador (V8 en Chrome o SpiderMonckey en Firefox). Esto en definitiva significa:
-  La interfaz se renderiza sin consultar de nuevo al servidor.
-  Las validaciones JS son convenientes pero nunca seguras por sí solas.
-  El usuario puede inspeccionar y modificar el código en el navegador.
-  Frameworks como React o Vue amplían la lógica ejecutable en cliente.
Por el lado del servidor:
El servidor recibe la petición HTTP, ejecuta el código de servidor (PHP, Python, Java…), consulta la base de datos si es necesario y genera un documento HTML que envía de vuelta al cliente. 
Características clave:
-  El código fuente nunca es visible para el usuario final.
-  Centraliza el acceso a datos y la lógica de negocio crítica.
-  Gestiona la autenticación, sesiones y permisos de forma segura.
-  Puede atender a miles de clientes simultáneamente
  
3. Web Estática vs. Dinámica:
  Explica las ventajas de la generación dinámica de páginas web para su modelo de negocio (tienda online) frente a una web estática tradicional.

4. La Infraestructura (Servidores):
  Explica brevemente el rol de un servidor web (como Apache o Nginx). Detalla técnicamente por qué es mejor utilizar un sistema de ejecución moderno como PHP-FPM (pool de procesos) en lugar del antiguo modelo CGI. Comenta brevemente qué papel juega un Framework como Laravel cubriendo tareas de un servidor de aplicaciones.

5. Evaluación de Herramientas y Frameworks:
   Justifica la elección de PHP y Laravel 12 como el ecosistema de desarrollo para su proyecto. Debes mencionar al menos dos ventajas clave de Laravel 12 (por ejemplo, el uso del patrón MVC, la seguridad que aporta por defecto o la estructura de directorios simplificada).
