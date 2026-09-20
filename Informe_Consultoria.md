# Informe Técnico de Consultoría Arquitectónica

**Proyecto:** Plataforma de Comercio Electrónico de Proximidad (EcoCercano)  
**Materia:** Desarrollo Web en Entorno Servidor (DWES)  
**Fecha:** Septiembre 2026  

---

## 1. Modelos de Ejecución: Cliente vs. Servidor

En cualquier aplicación web moderna existe una separación física y conceptual entre el código que se procesa en el dispositivo del usuario y el que se ejecuta en el servidor. Comprender esta frontera es esencial para diseñar un sistema seguro y con un rendimiento adecuado.

### Entorno del Cliente (Navegador Web)
El lado cliente está formado por los archivos que el servidor transfiere al navegador: estructura HTML5, hojas de estilo CSS3 y scripts de JavaScript. El motor del navegador (como V8 en navegadores basados en Chromium o SpiderMonkey en Firefox) descarga este código, lo interpreta en la máquina local y renderiza la interfaz gráfica.

Sus ventajas principales radican en la inmediatez: las animaciones, validaciones de formato en campos de formulario o cambios visuales en el DOM no necesitan hacer un viaje de ida y vuelta por la red. La carga de cómputo gráfico se reparte entre los dispositivos de los usuarios.

Sin embargo, el cliente es un **entorno no controlado**. Todo lo que se envía al navegador puede ser inspeccionado, detenido y modificado en tiempo real mediante las herramientas de desarrollo del navegador (DevTools / F12), extensiones o scripts de usuario.

### Entorno del Servidor
El lado servidor comprende la infraestructura privada (servidores físicos, máquinas virtuales o contenedores) donde residen los lenguajes de backend (PHP 8+, Python, Java, etc.) y los sistemas de bases de datos (MariaDB, PostgreSQL, MySQL).

En este entorno reside la lógica de negocio crítica: verificación de identidad y permisos de usuario, cálculo definitivo de precios e impuestos, transacciones de inventario y comunicación con pasarelas de pago. El código fuente nunca abandona el servidor; hacia el cliente únicamente viajan las respuestas procesadas (documentos HTML o respuestas en JSON).

### La Regla de Oro: "Nunca confiar en los datos que vienen del cliente"
En el desarrollo de una tienda online, asumir que los datos recibidos son válidos porque se comprobaron con JavaScript en el navegador es un error crítico de seguridad. Cualquier validación en el cliente es eludible por tres vías principales:

1. **Modificación del DOM y scripts en local:** Un usuario puede abrir las herramientas de desarrollador, eliminar atributos como `required`, `min` o `max`, modificar el valor de un campo oculto que contenga un precio o desactivar directamente la ejecución de JavaScript.
2. **Proxies de intercepción (Burp Suite, OWASP ZAP):** Un atacante puede desviar el tráfico HTTP/HTTPS a través de un proxy local y alterar las cabeceras, parámetros POST o payloads JSON después de que el navegador haya validado el formulario pero antes de que los datos alcancen el servidor.
3. **Peticiones HTTP directas (cURL, Postman, scripts externos):** No existe ninguna garantía de que una petición web provenga de un navegador web estándar. Se pueden emitir peticiones POST arbitrarias con parámetros forzados, valores negativos en cantidades o inyecciones maliciosas.

Por tanto, las validaciones en JavaScript del lado cliente tienen una función exclusiva de **experiencia de usuario (UX)**, dando feedback visual inmediato para no hacer esperar al cliente. Por el contrario, la **validación estricta, la sanitización tipada y el control de autorización en el servidor son la única barrera de seguridad real y obligatoria**.

---

## 2. Arquitectura Web: Estática vs. Dinámica

Para una tienda online de productos de proximidad (frutas de temporada, verduras ecológicas, lácteos de granjas locales y aceites de cooperativa), la elección entre una web estática o dinámica marca la viabilidad técnica del negocio.

| Característica | Web Estática (HTML/CSS fijo) | Web Dinámica (Generación en Servidor / PHP) |
| :--- | :--- | :--- |
| **Generación del contenido** | Archivos HTML pre-renderizados en disco; idénticos para cada petición. | Procesamiento en tiempo real por cada petición según el estado de la base de datos. |
| **Control de inventario** | Inviable en tiempo real; requiere recompilar y desplegar el sitio tras cada venta. | Consultas y actualizaciones atómicas inmediatas (`UPDATE stock = stock - 1`). |
| **Gestión de usuarios y carritos** | Requiere servicios SaaS externos desconectados de la base de datos central. | Sesiones seguras en servidor, carritos persistentes y tarifas personalizadas. |
| **Concurrencia en pedidos** | No soporta bloqueos transaccionales; alto riesgo de sobreventa. | Transacciones ACID con bloqueo a nivel de fila (`SELECT ... FOR UPDATE`). |
| **Integración con APIs** | Limitada a llamadas JavaScript en frontend a servicios de terceros. | Capacidad nativa de actuar como backend y servir endpoints JSON para apps móviles. |

### Justificación para el comercio de proximidad
1. **Gestión de stock perecedero y compras concurrentes:** Los productores locales disponen de partidas pequeñas y limitadas (por ejemplo, 20 lotes de fresas recolectadas en el día). Si dos clientes pulsan "Comprar" al mismo tiempo sobre la última unidad disponible, una web estática no puede coordinar esa concurrencia. Una arquitectura dinámica en servidor permite abrir una transacción de base de datos, comprobar el stock actual de forma atómica y decrementar la cantidad, evitando roturas de stock y cobros indebidos.
2. **Catálogo variable según temporada y geolocalización:** La disponibilidad de producto cambia semanalmente en función de las cosechas locales. Un servidor dinámico genera vistas de catálogo adaptadas a la comarca del comprador, calculando distancias kilométricas y costes de transporte específicos.
3. **Persistencia de sesión y carritos multitienda:** Los clientes añaden productos de diferentes productores locales a un mismo pedido. El backend gestiona el estado de sesión mediante cookies cifradas (`HttpOnly`, `SameSite`), consolidando el pedido y calculando el desglose de costes en un único checkout.

---

## 3. Infraestructura de Servidores y Mecanismos de Ejecución

La arquitectura backend se apoya en varias capas complementarias: el servidor web perimetral, el gestor de ejecución de scripts y el framework que actúa como servidor de aplicaciones lógico.

### El Rol del Servidor Web (Apache / Nginx)
El servidor web es el primer punto de contacto para las peticiones entrantes:
* **Terminación SSL/TLS:** Gestiona el cifrado y descifrado de certificados HTTPS, descargando de esta tarea de cálculo criptográfico al intérprete de la aplicación.
* **Servicio eficiente de ficheros estáticos:** Apache (con MPM Event) o Nginx resuelven peticiones de imágenes, hojas de estilo CSS y ficheros JS directamente desde disco con latencias mínimas, sin invocar el runtime de PHP.
* **Proxy inverso y compresión:** Comprime el tráfico de salida (Gzip / Brotli) y redirige las peticiones dinámicas al procesador correspondiente mediante sockets FastCGI.

### Evolución de Mecanismos: CGI Tradicional frente a PHP-FPM

* **El problema de CGI clásico (modelo fork por petición):**  
  En el estándar CGI clásico, cada petición HTTP dirigida a un script PHP provocaba que el servidor web ejecutase una llamada al sistema `fork()` para crear un proceso nuevo del sistema operativo. Este proceso cargaba el binario intérprete de PHP en memoria RAM, parseaba el código fuente, lo ejecutaba y destruía el proceso al finalizar.  
  Bajo tráfico moderado o picos de visitas, este modelo saturaba la tabla de procesos del kernel de Linux/Windows, provocaba un consumo desmedido de memoria y disparaba el cambio de contexto (*context switching*) de la CPU, degradando el servidor hasta provocar caídas por agotamiento de recursos.

* **La solución PHP-FPM (FastCGI Process Manager):**  
  PHP-FPM resuelve este cuello de botella mediante una **arquitectura de pool de procesos persistentes precargados en memoria**.  
  Un proceso maestro (*master process*) gestiona un grupo de procesos trabajadores (*workers*) configurables (`pm.max_children`, `pm.start_servers`). Cuando llega una petición FastCGI, un worker libre la atiende de inmediato y, al terminar, **no se destruye**: limpia su entorno y queda esperando la siguiente petición en cola.  
  Adicionalmente, PHP-FPM trabaja de forma conjunta con **OPcache**, un módulo de memoria compartida que almacena el bytecode precompilado de los scripts PHP. Esto elimina la necesidad de leer y compilar los archivos PHP en cada solicitud, reduciendo los tiempos de respuesta de cientos de milisegundos a valores inferiores a los 10 ms.

### Laravel como Servidor de Aplicaciones Lógico
Aunque PHP opera bajo un ciclo de vida donde el estado de memoria se reinicia entre peticiones (*share-nothing*), frameworks como Laravel asumen internamente las responsabilidades de un servidor de aplicaciones empresarial:
1. **Gestión unificada del ciclo de vida de la petición (*Request Lifecycle*):** Centraliza la recepción en `public/index.php`, encapsula la petición en un objeto `Request` inmutable y la canaliza a través de un pipeline ordenado de *Middlewares* (validación CSRF, autenticación de sesión, limitación de peticiones por minuto o *rate limiting*).
2. **Contenedor de Inversión de Control (IoC / Service Container):** Facilita la inyección de dependencias y el desacoplamiento de servicios (por ejemplo, alternar entre un adaptador de pagos Redsys y Stripe sin modificar los controladores de negocio).
3. **Gestión de colas de tareas asíncronas (*Queue Workers*):** Laravel permite despachar procesos pesados (envío de correos de confirmación, generación de facturas en PDF o sincronización de albaranes) a colas en segundo plano respaldadas por Redis o base de datos, devolviendo una respuesta inmediata al usuario.

---

## 4. Selección de Stack: PHP 8+ y Laravel 12

Para un proyecto que inicia su andadura en 2026, la combinación de **PHP 8.x con Laravel 12** ofrece una relación sobresaliente entre velocidad de desarrollo, seguridad por defecto y costes de infraestructura controlados.

### Justificación de PHP 8+ frente a Alternativas
* **Tipado estricto y robustez:** Con `declare(strict_types=1);`, tipos escalares en argumentos, tipos de retorno, uniones (`string|int`) y tipos de intersección, PHP ofrece hoy el rigor de lenguajes fuertemente tipados, previniendo errores de conversión en tiempo de ejecución.
* **Rendimiento con compilador JIT:** La introducción del compilador Just-In-Time en PHP 8 optimiza el procesamiento de cálculos complejos, reduciendo distancias de rendimiento con alternativas compiladas.
* **Eficiencia operativa frente a Java y Node.js:**  
  * Frente a **Java (Spring Boot)**, PHP 8 arranca con una huella de memoria mínima (decenas de megabytes frente a cientos de megabytes de una JVM estándar) y costes de alojamiento notablemente menores en entornos cloud o VPS sencillos.  
  * Frente a **Node.js**, el modelo monohilo de Node.js corre el riesgo de bloquear el bucle de eventos (*event loop*) si se realiza un cálculo pesado en CPU. En PHP, el aislamiento por petición garantiza que una incidencia puntual en un script no interrumpa el servicio al resto de usuarios.

### Ventajas de Laravel 12 para la Plataforma de Proximidad

#### 1. Patrón Modelo-Vista-Controlador (MVC) y Eloquent ORM
Laravel 12 estructura la aplicación separando responsabilidades:
* **Modelos (Eloquent ORM):** Representan entidades de negocio (`Productor`, `Producto`, `Pedido`) y sus relaciones (`$productor->productos()`). Evitan escribir consultas SQL crudas y gestionan la lógica de datos con código legible y orientado a objetos.
* **Controladores:** Reciben las peticiones filtradas, coordinan los servicios de dominio y delegan la respuesta a la vista o al formato JSON.
* **Vistas (Blade):** Motor de plantillas compilado a PHP nativo que soporta herencia de layouts y directivas limpias, agilizando el diseño de la interfaz sin mezclar lógica de negocio pesada en el frontend.

#### 2. Estructura Simplificada y Mantenibilidad
En su versión 12, Laravel consolida una estructura de arranque simplificada:
* **Bootstrap unificado:** La configuración del kernel de rutas y el registro de middlewares globales se realiza de forma centralizada en `bootstrap/app.php`, reduciendo el número de archivos dispersos.
* **Migraciones de base de datos:** El esquema relacional se describe como código PHP versionable en Git. Cualquier miembro del equipo o entorno de pruebas puede reconstruir la base de datos ejecutando `php artisan migrate`, garantizando consistencia absoluta entre entornos de desarrollo y producción.

#### 3. Seguridad Integrada contra Amenazas OWASP Top 10
* **Mitigación de Inyección SQL (SQLi):**  
  Eloquent ORM y el constructor de consultas (*Query Builder*) utilizan internamente el driver PDO de PHP con **sentencias preparadas obligatorias y vinculación de parámetros (*parameter binding*)**. El motor de base de datos separa la gramática de la consulta de los datos introducidos por el usuario, neutralizando cualquier intento de inyección SQL.
* **Protección contra Cross-Site Request Forgery (CSRF):**  
  Todos los formularios que envían peticiones `POST`, `PUT` o `DELETE` incluyen la directiva `@csrf`, que genera un token criptográfico de un solo uso asociado a la sesión del usuario. El middleware verifica este token de manera automática, bloqueando cualquier petición externa no autorizada con un código de respuesta HTTP `419 Page Expired`.
* **Escape contextual contra Cross-Site Scripting (XSS):**  
  La directiva de renderizado de Blade `{{ $variable }}` canaliza internamente cualquier dato a través de `htmlspecialchars($variable, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')`. Si un usuario intenta inyectar código HTML o scripts JavaScript (`<script>`, eventos `onload`), el motor lo transforma en texto plano inofensivo, impidiendo su ejecución en el navegador.

---

## 5. Conclusión Técnica

La arquitectura fundamentada en **PHP 8+ y Laravel 12 desplegada sobre un servidor web con PHP-FPM** proporciona a la startup de proximidad una base tecnológica escalable, segura y económica. Esta elección permite centrar el esfuerzo en las reglas de negocio y la experiencia de usuario, apoyándose en un ecosistema maduro con herramientas profesionales de desarrollo.
