# Solución de Problemas - Sistema de Test de Tránsito

## Problemas Reportados

1. **Error 401 (Unauthorized)** al hacer login con admin
2. **Error jQuery con "#"** al navegar después del login
3. **Error 404** al intentar hacer un test

## Soluciones Implementadas

### 1. Script de Instalación Completo

Ejecuta el script de instalación para configurar todo correctamente:

```bash
# Ejecutar el instalador
php install.php
```

Este script:
- ✅ Crea la base de datos y tablas
- ✅ Crea el usuario admin con contraseña correcta
- ✅ Inserta las categorías por defecto
- ✅ Crea los directorios necesarios

### 2. Insertar Preguntas

Después de la instalación, ejecuta:

```bash
# Insertar las 100 preguntas del JSON
php insert_questions.php
```

### 3. Credenciales del Admin

- **Usuario**: `admin`
- **Contraseña**: `admin123`
- **Email**: `admin@test-transito.com`

### 4. Problemas de Navegación Solucionados

Se han corregido los errores en `assets/js/app.js`:
- ✅ Validación de enlaces de navegación
- ✅ Manejo de secciones inexistentes
- ✅ Prevención de errores jQuery con "#"

### 5. Verificación de la API

Para verificar que la API funciona correctamente:

```bash
# Probar endpoint de categorías
curl http://localhost/test-transito/api/categories/with-count

# Probar autenticación
curl -X POST http://localhost/test-transito/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@test-transito.com","password":"admin123"}'
```

## Pasos para Solucionar

### Paso 1: Ejecutar Instalador
```bash
php install.php
```

### Paso 2: Insertar Preguntas
```bash
php insert_questions.php
```

### Paso 3: Verificar Configuración
1. Asegúrate de que Apache/PHP esté funcionando
2. Verifica que la base de datos MySQL esté activa
3. Confirma que el directorio esté en `http://localhost/test-transito/`

### Paso 4: Probar el Sistema
1. Accede a `http://localhost/test-transito/`
2. Haz login con admin/admin123
3. Prueba la navegación entre secciones
4. Intenta realizar un test

## Estructura de Archivos Creados

```
├── install.php                    # Instalador completo
├── insert_questions.php           # Script para insertar preguntas
├── database/
│   ├── schema.sql                # Esquema de base de datos
│   ├── insert_questions.sql      # Inserts SQL de preguntas
│   └── fix_admin_user.sql       # Arreglar usuario admin
├── assets/
│   ├── css/style.css            # Estilos corregidos
│   └── js/app.js               # JavaScript corregido
└── api/
    ├── config/config.php        # Configuración
    ├── controllers/             # Controladores
    ├── models/                  # Modelos
    └── includes/               # Utilidades
```

## Verificación de Funcionamiento

### 1. Verificar Base de Datos
```sql
USE test_transito;
SELECT COUNT(*) as total_questions FROM questions;
SELECT COUNT(*) as total_categories FROM categories;
SELECT * FROM users WHERE username = 'admin';
```

### 2. Verificar Archivos
```bash
# Verificar que existan los directorios
ls -la assets/img/questions/
ls -la logs/

# Verificar permisos
chmod 755 assets/img/questions/
chmod 755 logs/
```

### 3. Verificar API
```bash
# Probar endpoint de autenticación
curl -X POST http://localhost/test-transito/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@test-transito.com","password":"admin123"}'
```

## Problemas Comunes y Soluciones

### Error 401 (Unauthorized)
- **Causa**: Usuario admin no existe o contraseña incorrecta
- **Solución**: Ejecutar `php install.php`

### Error jQuery con "#"
- **Causa**: Enlaces de navegación mal formados
- **Solución**: Ya corregido en `assets/js/app.js`

### Error 404 en Tests
- **Causa**: Preguntas no insertadas o categorías vacías
- **Solución**: Ejecutar `php insert_questions.php`

### Error de Conexión a Base de Datos
- **Causa**: MySQL no está ejecutándose o credenciales incorrectas
- **Solución**: Verificar que MySQL esté activo y las credenciales en `api/config/config.php`

## Logs y Debugging

### Verificar Logs de Error
```bash
tail -f logs/error.log
```

### Debugging de la API
```bash
# Verificar que la API responda
curl http://localhost/test-transito/api/categories/

# Verificar autenticación
curl -X POST http://localhost/test-transito/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@test-transito.com","password":"admin123"}'
```

## Comandos de Verificación Rápida

```bash
# 1. Verificar que PHP funcione
php -v

# 2. Verificar que MySQL esté activo
mysql -u root -p -e "SHOW DATABASES;"

# 3. Ejecutar instalación
php install.php

# 4. Insertar preguntas
php insert_questions.php

# 5. Verificar archivos
ls -la assets/img/questions/
ls -la logs/

# 6. Probar API
curl http://localhost/test-transito/api/categories/with-count
```

## Notas Importantes

1. **Contraseña del Admin**: `admin123` (no cambiar en el código)
2. **Base de Datos**: `test_transito`
3. **URL Base**: `http://localhost/test-transito/`
4. **Directorio de Imágenes**: `assets/img/questions/`
5. **Logs**: `logs/error.log`

## Contacto y Soporte

Si persisten los problemas después de seguir estos pasos:

1. Verifica los logs de error en `logs/error.log`
2. Confirma que Apache/PHP esté configurado correctamente
3. Verifica que MySQL esté ejecutándose
4. Asegúrate de que el directorio tenga los permisos correctos

---

**Sistema Listo**: Después de ejecutar `install.php` e `insert_questions.php`, el sistema estará completamente funcional con 100 preguntas organizadas en 20 categorías.