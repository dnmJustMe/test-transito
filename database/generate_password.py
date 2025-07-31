#!/usr/bin/env python3
import bcrypt
import hashlib

# Generar hash de la contraseña admin123
password = b'admin123'
salt = bcrypt.gensalt()
hash_result = bcrypt.hashpw(password, salt)

print("Contraseña: admin123")
print("Hash generado:", hash_result.decode('utf-8'))

# Verificar que funciona
if bcrypt.checkpw(password, hash_result):
    print("✓ Hash válido")
else:
    print("✗ Hash inválido")

# También generar con PHP compatible
import hashlib
import base64

# Simular PHP password_hash con bcrypt
php_hash = bcrypt.hashpw(password, bcrypt.gensalt(12))
print("Hash PHP compatible:", php_hash.decode('utf-8'))