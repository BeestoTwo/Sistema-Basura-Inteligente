<?php
/* Funciones de apoyo usadas en todo el sistema */

function e($texto)
{
    return htmlspecialchars((string)$texto, ENT_QUOTES, 'UTF-8');
}

/* Ejecuta una consulta preparada y devuelve el resultado (SELECT) o true/false */
function consulta($conexion, $sql, $tipos = "", $params = [])
{
    $stmt = $conexion->prepare($sql);
    if (!$stmt) {
        return false;
    }
    if ($tipos !== "") {
        $stmt->bind_param($tipos, ...$params);
    }
    if (!$stmt->execute()) {
        return false;
    }
    $res = $stmt->get_result();
    return $res === false ? true : $res;
}

function filas($resultado)
{
    $lista = [];
    if ($resultado && $resultado !== true) {
        while ($f = $resultado->fetch_assoc()) {
            $lista[] = $f;
        }
    }
    return $lista;
}

function valor($conexion, $sql, $tipos = "", $params = [])
{
    $r = consulta($conexion, $sql, $tipos, $params);
    if ($r && $r !== true) {
        $f = $r->fetch_row();
        return $f ? $f[0] : null;
    }
    return null;
}

function clase_nivel($nivel, $estado = "")
{
    if ($estado === "Mantenimiento") return "azul";
    if ($nivel >= UMBRAL_ALTO) return "rojo";
    if ($nivel >= UMBRAL_MEDIO) return "amarillo";
    return "verde";
}

function prefijo_residuo($tipo)
{
    $mapa = [
        "Orgánico" => "ORG",
        "Plástico" => "PLA",
        "Papel/Cartón" => "PAP",
        "Vidrio" => "VID",
    ];
    return $mapa[$tipo] ?? "CON";
}

function tipos_residuo()
{
    return ["Orgánico", "Plástico", "Papel/Cartón", "Vidrio"];
}

function mensaje_y_redirigir($mensaje, $destino)
{
    echo "<script>alert(" . json_encode($mensaje) . "); window.location.href=" . json_encode($destino) . ";</script>";
    exit();
}

/* Registra una alerta si un contenedor supera el umbral (evita duplicados pendientes) */
function generar_alerta_si_corresponde($conexion, $id_contenedor, $nivel, $codigo)
{
    if ($nivel >= UMBRAL_ALTO) {
        $existe = valor($conexion, "SELECT COUNT(*) FROM alertas WHERE id_contenedor=? AND estado='Pendiente' AND tipo='Contenedor lleno'", "i", [$id_contenedor]);
        if (!$existe) {
            consulta(
                $conexion,
                "INSERT INTO alertas (id_contenedor, tipo, mensaje, estado) VALUES (?, 'Contenedor lleno', ?, 'Pendiente')",
                "is",
                [$id_contenedor, "El contenedor $codigo alcanzó un nivel de llenado de $nivel %."]
            );
        }
    }
}
