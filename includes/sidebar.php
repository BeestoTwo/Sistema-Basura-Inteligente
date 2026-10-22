<?php
$actual = basename($_SERVER['PHP_SELF']);
function item_menu($href, $texto, $actual)
{
    $activo = basename($href) === $actual ? ' class="activo"' : '';
    echo '<a href="' . $href . '"' . $activo . '>' . $texto . '</a>';
}
?>
<div class="sidebar">
    <h2>🗑️ Basura<br>Inteligente</h2>
    <?php
    item_menu("../admin/panel-admin.php", "🏠 Inicio", $actual);
    item_menu("../contenedores/listar-contenedores.php", "🗑️ Contenedores", $actual);
    item_menu("../camiones/listar-camiones.php", "🚛 Camiones", $actual);
    item_menu("../rutas/listar-rutas.php", "🗺️ Rutas", $actual);
    item_menu("../mapa/mapa-general.php", "📍 Mapa", $actual);
    item_menu("../sensores/monitoreo.php", "📡 Sensores IoT", $actual);
    item_menu("../ciudadanos/listar-reportes.php", "📢 Reportes ciudadanos", $actual);
    item_menu("../reportes/reportes.php", "📊 Reportes", $actual);
    item_menu("../usuarios/listar-usuarios.php", "👥 Usuarios", $actual);
    ?>
    <a href="../logout.php">🚪 Cerrar sesión</a>
    <div class="sidebar-usuario">Sesión: <b><?= e($_SESSION['nombre'] ?? $_SESSION['usuario']) ?></b></div>
</div>
