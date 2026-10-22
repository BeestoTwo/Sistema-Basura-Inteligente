# Sistema Inteligente de Recolección de Basura – Santa Cruz de la Sierra

Aplicación web (PHP + MySQL + Leaflet) para gestionar contenedores con sensores IoT, camiones recolectores,
rutas de recolección optimizadas, alertas, reportes ciudadanos y reportes estadísticos.

## Requisitos
- XAMPP (Apache + PHP 8.x + MySQL/MariaDB) o cualquier servidor con PHP 7.4 o superior y la extensión mysqli.
- Navegador web moderno (Chrome, Edge o Firefox).

## Instalación en XAMPP (paso a paso)
1. Copie la carpeta `Sistema-Basura-Inteligente` dentro de `C:\xampp\htdocs\`.
2. Inicie **Apache** y **MySQL** desde el panel de control de XAMPP.
3. Abra **phpMyAdmin** (http://localhost/phpmyadmin) o **HeidiSQL** y ejecute el archivo
   `database/basura_inteligente.sql`. El script crea la base de datos `basura_inteligente`,
   sus 8 tablas y carga datos de ejemplo (52 contenedores, 6 camiones, 5 rutas, lecturas de sensores, etc.).
4. Copie `config.example.php` como `config.php` y ajuste sus valores: si su usuario `root` de MySQL
   tiene contraseña, escríbala en `DB_PASS`; use `DB_PORT` 3306 (puerto por defecto de XAMPP) y
   defina su propio `API_TOKEN`.
5. Ingrese a: **http://localhost/Sistema-Basura-Inteligente/**

## Usuarios de prueba
| Usuario | Contraseña | Rol |
|---|---|---|
| admin | admin123 | Administrador |
| yerzon, jperez, cmansilla, jrojas, rcarrillo | reco123 | Recolector (cada uno con un camión asignado) |

## Módulos
| Módulo | Carpeta | Funciones |
|---|---|---|
| Inicio y acceso | `index.php`, `login.php` | Portada, inicio de sesión con roles y contraseñas cifradas |
| Panel administrador | `admin/` | Indicadores, alertas, contenedores con mayor llenado y mapa |
| Contenedores | `contenedores/` | Registrar (con mapa), listar con filtros, ver historial del sensor, editar, dar de baja, reactivar |
| Camiones | `camiones/` | Registrar, listar, editar, dar de baja, reactivar, eliminar; aviso de licencias por vencer |
| Rutas | `rutas/` | Crear ruta inteligente (prioriza por llenado y ordena por cercanía), ver ruta, cambiar estado, imprimir |
| Mapa | `mapa/` | Mapa general con filtros por tipo y nivel, y reportes ciudadanos |
| Sensores IoT | `sensores/`, `api/` | Monitoreo de sensores, alertas, simulador de lecturas y API para nodos reales |
| Reportes ciudadanos | `ciudadanos/` | Formulario público para reportar problemas y gestión por el administrador |
| Reportes | `reportes/` | Estadísticas por tipo, distrito, estado y rutas; exportación CSV (Excel) e impresión |
| Usuarios | `usuarios/` | Alta, edición y baja de administradores y recolectores |
| Panel recolector | `recolector/` | Ruta asignada del día, mapa y registro de contenedores recolectados |

## API para sensores IoT
`POST api/lectura.php` con los campos `token`, `codigo_sensor`, `nivel` y `bateria`:
```
curl -X POST http://localhost/Sistema-Basura-Inteligente/api/lectura.php \
     -d "token=SBI-IOT-2026&codigo_sensor=NODO-0001&nivel=85&bateria=92"
```
Respuesta: `{"ok":true,"contenedor":"ORG-D11-001","nivel":85,"alerta":true}`.
El token se configura en `config.php` (`API_TOKEN`).

## Mapas
Los mapas usan Leaflet (incluido en `assets/leaflet`, funciona sin internet) y los mosaicos de OpenStreetMap.
Si no hay conexión a internet, el sistema muestra automáticamente un **mapa esquemático** de la ciudad
(anillos y avenidas principales) para que los mapas sigan funcionando. El trazado de rutas por calles usa
el servicio OSRM; sin conexión se dibuja una ruta aproximada.

## Seguridad
- Consultas preparadas (evitan inyección SQL).
- Contraseñas cifradas con `password_hash`.
- Control de acceso por rol en cada página.
- Escape de datos al mostrarlos (evita XSS).

## Estructura de la base de datos
`usuarios`, `camiones`, `contenedores`, `rutas`, `detalle_ruta`, `lecturas_sensor`, `alertas`, `reportes_ciudadanos`.
El diagrama entidad-relación se incluye en el documento del proyecto.
