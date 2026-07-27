<?php
$message = setting('maintenance_message') ?: 'Estamos realizando tareas de mantenimiento. Vuelve pronto.';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sitio en Mantenimiento - <?= sanitize(env('APP_NAME', 'Ecommerce')) ?></title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #ed1c24 0%, #c41820 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .box {
            background: white;
            padding: 3rem;
            border-radius: 12px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            max-width: 480px;
            width: 100%;
            text-align: center;
        }
        .icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #ed1c24 0%, #c41820 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
            font-size: 2.25rem;
        }
        h1 { color: #1f2937; font-size: 1.75rem; margin-bottom: 0.75rem; }
        p { color: #6b7280; line-height: 1.6; }
    </style>
</head>
<body>
    <div class="box">
        <div class="icon">🛠️</div>
        <h1>Sitio en Mantenimiento</h1>
        <p><?= sanitize($message) ?></p>
    </div>
</body>
</html>
