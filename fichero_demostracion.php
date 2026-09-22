<?php
// Configuración de la zona horaria del servidor
date_default_timezone_set('Europe/Madrid');

// 1. Obtención dinámica de datos del servidor
$fechaServidor = date('d/m/Y');
$horaServidor = date('H:i:s');
$versionPHP = phpversion();
$softwareServ = $_SERVER['SERVER_SOFTWARE'] ?? 'Apache / XAMPP';

// 2. Simulación o captura de dato dinámico de usuario (GET)
// Por defecto se incluye una cadena con etiquetas HTML para demostrar su neutralización
$nombreUsuario = $_GET['nombre'] ?? 'Cliente <script>alert("XSS")</script>';
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Demostración de Generación Dinámica - PHP y XAMPP</title>

</head>

<body>

    <div class="container">
        <!-- CABECERA DEL DOCUMENTO -->
        <header>
            <h1>Demostración de Generación Dinámica</h1>
            <p class="subtitle">Entorno de desarrollo local XAMPP &mdash; PHP 8</p>
        </header>

        <!-- CUERPO PRINCIPAL -->
        <main>
            <h1>1. Datos Dinámicos del Servidor en Tiempo Real</h1>
            <ul class="info-list">
                <li>
                    <strong>Fecha actual del servidor:</strong>
                    <!-- Interpolación dinámica de la fecha calculada por PHP -->
                    <span class="badge"><?php echo htmlspecialchars($fechaServidor, ENT_QUOTES, 'UTF-8'); ?></span>
                </li>
                <li>
                    <strong>Hora actual del servidor:</strong>
                    <!-- Interpolación dinámica de la hora del servidor en cada petición -->
                    <span class="badge"><?php echo htmlspecialchars($horaServidor, ENT_QUOTES, 'UTF-8'); ?></span>
                </li>
                <li>
                    <strong>Versión de PHP en ejecución:</strong>
                    <span class="badge">PHP <?php echo htmlspecialchars($versionPHP, ENT_QUOTES, 'UTF-8'); ?></span>
                </li>
                <li>
                    <strong>Servidor web local:</strong>
                    <span class="badge"><?php echo htmlspecialchars($softwareServ, ENT_QUOTES, 'UTF-8'); ?></span>
                </li>
            </ul>

            <!-- PIE DE PÁGINA -->
            <footer>
                DWS1: Elige tu Stack &mdash; Desarrollo Web en Entorno Servidor
            </footer>
    </div>

</body>

</html>