<?php
/* ============================================================
   API para los sensores IoT
   Método: POST (JSON o formulario)
   Parámetros: token, codigo_sensor, nivel (0-100), bateria (0-100)
   Ejemplo:
   curl -X POST http://localhost/Sistema-Basura-Inteligente/api/lectura.php \
        -d "token=SBI-IOT-2026&codigo_sensor=NODO-0001&nivel=85&bateria=92"
   ============================================================ */
require_once __DIR__ . '/../conexion.php';
require_once __DIR__ . '/../includes/funciones.php';
header('Content-Type: application/json; charset=utf-8');

$datos = $_POST;
if (!$datos) {
    $datos = json_decode(file_get_contents('php://input'), true) ?: [];
}
if (($datos['token'] ?? '') !== API_TOKEN) {
    http_response_code(401);
    echo json_encode(['ok' => false, 'error' => 'Token inválido']);
    exit();
}
$sensor = $datos['codigo_sensor'] ?? '';
$nivel = isset($datos['nivel']) ? max(0, min(100, intval($datos['nivel']))) : null;
$bateria = isset($datos['bateria']) ? max(0, min(100, intval($datos['bateria']))) : null;

$res = consulta($conexion, "SELECT id, codigo FROM contenedores WHERE codigo_sensor=?", "s", [$sensor]);
$c = ($res && $res !== true) ? $res->fetch_assoc() : null;
if (!$c || $nivel === null) {
    http_response_code(404);
    echo json_encode(['ok' => false, 'error' => 'Sensor no registrado o nivel faltante']);
    exit();
}
consulta($conexion, "INSERT INTO lecturas_sensor (id_contenedor, nivel, bateria) VALUES (?,?,?)", "iii", [$c['id'], $nivel, $bateria]);
consulta($conexion, "UPDATE contenedores SET nivel_llenado=?, bateria=?, ultima_lectura=NOW() WHERE id=?", "iii", [$nivel, $bateria, $c['id']]);
generar_alerta_si_corresponde($conexion, $c['id'], $nivel, $c['codigo']);
if ($bateria !== null && $bateria < 20) {
    consulta($conexion, "INSERT INTO alertas (id_contenedor, tipo, mensaje) VALUES (?, 'Batería baja', ?)", "is", [$c['id'], "El sensor $sensor tiene $bateria % de batería."]);
}
echo json_encode(['ok' => true, 'contenedor' => $c['codigo'], 'nivel' => $nivel, 'alerta' => $nivel >= UMBRAL_ALTO]);
