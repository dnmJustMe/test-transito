<?php
// Script para generar la contraseña hash correcta
$password = 'admin123';
$hash = password_hash($password, PASSWORD_DEFAULT);

echo "Contraseña: " . $password . "\n";
echo "Hash: " . $hash . "\n";

// Verificar que funciona
if (password_verify($password, $hash)) {
    echo "✓ Hash válido\n";
} else {
    echo "✗ Hash inválido\n";
}
?>