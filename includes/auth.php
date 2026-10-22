<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../conexion.php';
require_once __DIR__ . '/funciones.php';

/* Exige sesión iniciada y (opcionalmente) un rol permitido */
function requiere_rol($roles = [])
{
    if (empty($_SESSION['usuario'])) {
        header("Location: ../login.php");
        exit();
    }
    if (!empty($roles) && !in_array($_SESSION['rol'], $roles, true)) {
        if ($_SESSION['rol'] === 'recolector') {
            header("Location: ../recolector/panel-recolector.php");
        } else {
            header("Location: ../admin/panel-admin.php");
        }
        exit();
    }
}
