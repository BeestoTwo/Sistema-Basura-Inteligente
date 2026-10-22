<?php require_once __DIR__ . '/config.php'; ?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= APP_NOMBRE ?></title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: Arial, sans-serif; }
        body { background: linear-gradient(135deg, #198754, #28a745); min-height: 100vh; display: flex; justify-content: center; align-items: center; color: white; }
        .contenedor { text-align: center; width: 80%; }
        h1 { font-size: 55px; margin-bottom: 30px; }
        p { font-size: 24px; margin-bottom: 40px; }
        .botones { display: flex; gap: 18px; justify-content: center; flex-wrap: wrap; }
        .btn { background: white; color: #198754; text-decoration: none; padding: 15px 35px; border-radius: 10px; font-size: 20px; font-weight: bold; }
        .btn.secundario { background: transparent; color: white; border: 2px solid white; }
        .btn:hover { background: #e9ecef; }
        .btn.secundario:hover { background: rgba(255, 255, 255, .15); }
    </style>
</head>

<body>
    <div class="contenedor">
        <h1>🗑️ Sistema Inteligente de Recolección de Basura</h1>
        <p>Plataforma web para optimizar la gestión de residuos sólidos en la ciudad de Santa Cruz de la Sierra.</p>
        <div class="botones">
            <a href="login.php" class="btn">Iniciar Sesión</a>
            <a href="ciudadanos/reportar.php" class="btn secundario">📢 Reportar un problema</a>
        </div>
    </div>
</body>

</html>
