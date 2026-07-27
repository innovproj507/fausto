<?php

/**
 * CLI Script - Generate Application Keys
 * Genera claves de seguridad para APP_KEY y JWT_SECRET
 */

function generateKey(int $length = 32): string
{
    return base64_encode(random_bytes($length));
}

echo "Generando claves de seguridad...\n\n";

$appKey = generateKey(32);
$jwtSecret = generateKey(64);

echo "APP_KEY=base64:{$appKey}\n";
echo "JWT_SECRET={$jwtSecret}\n\n";

echo "Copia estas líneas a tu archivo .env\n";
