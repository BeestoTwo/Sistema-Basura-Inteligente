<?php
/* ============================================================
   CONFIGURACIÓN GENERAL - Sistema Inteligente de Recolección de Basura
   Ajuste estos valores según su instalación (XAMPP / servidor).
   ============================================================ */
define('DB_HOST', '127.0.0.1');
define('DB_USER', 'root');
define('DB_PASS', '');               // En XAMPP la contraseña de root suele estar vacía
define('DB_NAME', 'basura_inteligente');
define('DB_PORT', 3307);          // Instancia MySQL local del proyecto (.mysql-local)

define('APP_NOMBRE', 'Sistema Inteligente de Recolección de Basura');

// Base de operaciones de los camiones (inicio y fin de cada ruta)
define('BASE_LAT', -17.8146);
define('BASE_LNG', -63.1561);
define('BASE_NOMBRE', 'Base de camiones (Normandía)');

// Umbrales de llenado
define('UMBRAL_ALTO', 80);   // >= 80 %: prioridad alta / alerta
define('UMBRAL_MEDIO', 50);  // >= 50 %: prioridad media

// Token que deben enviar los sensores IoT a la API (api/lectura.php)
define('API_TOKEN', 'CAMBIAR-ESTE-TOKEN');

// Velocidad promedio de los camiones en ruta (km/h) para estimar tiempos
define('VELOCIDAD_PROMEDIO', 25);

date_default_timezone_set('America/La_Paz');
