<?php
// Script para generar password hash
$password = 'password123';
$hash = password_hash($password, PASSWORD_BCRYPT);

echo "Password: $password\n";
echo "Hash: $hash\n";
echo "\nCopia este SQL:\n";
echo "UPDATE users SET password = '$hash' WHERE email = 'admin@test.com';\n";
