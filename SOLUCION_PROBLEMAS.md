# 🔧 **SOLUCIÓN DE PROBLEMAS - SISTEMA DE TEST DE TRÁNSITO**

## 📋 **Problemas Reportados y Soluciones**

### **Error 1: Column not found: 1054 Unknown column 'answer1'**
**Problema:** El script de inserción de preguntas fallaba porque la estructura de la base de datos no coincidía.

**Solución:** 
- ✅ **Estructura corregida** en `database/schema.sql`
- ✅ **Script actualizado** en `insert_questions.php`
- ✅ **Modelos actualizados** para usar la estructura correcta

### **Error 2: 401 Unauthorized al hacer login**
**Problema:** El usuario admin no se creaba correctamente o tenía credenciales incorrectas.

**Solución:**
- ✅ **Script de instalación mejorado** en `install.php`
- ✅ **Usuario admin recreado** con contraseña conocida: `admin123`

### **Error 3: jQuery Syntax Error en navegación**
**Problema:** Error de jQuery al navegar después del login.

**Solución:**
- ✅ **Navegación corregida** en `assets/js/app.js`
- ✅ **Validaciones agregadas** para enlaces vacíos

### **Error 4: 404 Not Found al iniciar test**
**Problema:** No había preguntas en la base de datos.

**Solución:**
- ✅ **Script de preguntas corregido** en `insert_questions.php`
- ✅ **100 preguntas insertadas** con estructura correcta

## 🚀 **INSTRUCCIONES DETALLADAS PARA XAMPP EN WINDOWS**

### **Paso 1: Preparar XAMPP**

1. **Abrir XAMPP Control Panel**
   - Busca "XAMPP" en el menú de Windows
   - O ve a `C:\xampp\xampp-control.exe`

2. **Iniciar Servicios**
   - Haz clic en **"Start"** para **Apache**
   - Haz clic en **"Start"** para **MySQL**
   - Verifica que ambos muestren **"Running"** en verde

3. **Verificar que funcionan**
   - Abre tu navegador
   - Ve a `http://localhost/`
   - Deberías ver la página de XAMPP

### **Paso 2: Colocar el Proyecto**

1. **Navegar al directorio de XAMPP**
   ```
   C:\xampp\htdocs\
   ```

2. **Crear carpeta del proyecto**
   - Crea una carpeta llamada `test-transito`
   - La ruta completa será: `C:\xampp\htdocs\test-transito\`

3. **Copiar archivos del proyecto**
   - Copia todos los archivos del proyecto a esta carpeta
   - Asegúrate de que la estructura sea:
   ```
   C:\xampp\htdocs\test-transito\
   ├── install.php
   ├── insert_questions.php
   ├── test_system.php
   ├── index.html
   ├── api\
   ├── assets\
   ├── database\
   └── ...
   ```

### **Paso 3: Ejecutar Scripts de Instalación**

#### **Opción A: Desde el Navegador (Recomendado)**

1. **Ejecutar instalador**
   - Abre tu navegador
   - Ve a: `http://localhost/test-transito/install.php`
   - Deberías ver la salida del script en la pantalla

2. **Ejecutar insertador de preguntas**
   - Ve a: `http://localhost/test-transito/insert_questions.php`
   - Deberías ver el progreso de inserción

3. **Probar el sistema**
   - Ve a: `http://localhost/test-transito/test_system.php`
   - Verifica que todos los checks muestren ✓

#### **Opción B: Desde Línea de Comandos**

1. **Abrir Command Prompt**
   - Presiona `Windows + R`
   - Escribe `cmd` y presiona Enter

2. **Navegar al directorio**
   ```cmd
   cd C:\xampp\htdocs\test-transito
   ```

3. **Ejecutar scripts**
   ```cmd
   php install.php
   php insert_questions.php
   php test_system.php
   ```

### **Paso 4: Configurar Imágenes**

1. **Crear directorio de imágenes**
   - Ve a: `C:\xampp\htdocs\test-transito\assets\img\questions\`

2. **Colocar imágenes**
   - Nombra las imágenes como: `i{NRO}.png`
   - Ejemplo: `i10.png`, `i825.png`, etc.
   - El NRO corresponde al campo en la base de datos

3. **Verificar imágenes**
   - Las imágenes deben estar en formato PNG
   - El sistema buscará automáticamente las imágenes

### **Paso 5: Probar el Sistema**

1. **Acceder al sistema**
   - Ve a: `http://localhost/test-transito/`
   - Deberías ver la página principal

2. **Hacer login**
   - Haz clic en **"Iniciar Sesión"**
   - Usuario: `admin`
   - Contraseña: `admin123`

3. **Probar funcionalidades**
   - Navega entre las secciones
   - Intenta realizar un test
   - Verifica el panel de administración

## 🔧 **Solución de Problemas Comunes**

### **Error: "php no se reconoce como comando"**
```cmd
# Agregar PHP al PATH
set PATH=%PATH%;C:\xampp\php
```

### **Error de conexión a MySQL**
1. Verifica que MySQL esté iniciado en XAMPP
2. Verifica las credenciales en `api/config/config.php`:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_NAME', 'test_transito');
   define('DB_USER', 'root');
   define('DB_PASS', '');  // Sin contraseña por defecto
   ```

### **Error de permisos**
1. Haz clic derecho en la carpeta `test-transito`
2. Propiedades → Seguridad
3. Asegúrate de que el usuario tenga permisos de escritura

### **Error 404 en Apache**
1. Verifica que Apache esté iniciado
2. Verifica que el archivo `.htaccess` esté presente
3. Verifica que `mod_rewrite` esté habilitado en XAMPP

### **Error: "Column not found"**
1. Ejecuta `install.php` para recrear las tablas
2. Ejecuta `insert_questions.php` para insertar las preguntas
3. Verifica con `test_system.php`

## 📊 **Verificación del Sistema**

### **Script de Prueba**
Ejecuta `test_system.php` para verificar:
- ✅ Conexión a la base de datos
- ✅ Tablas creadas correctamente
- ✅ Usuario admin configurado
- ✅ Categorías insertadas
- ✅ Preguntas insertadas
- ✅ Estructura de base de datos correcta
- ✅ Directorios creados
- ✅ Archivos principales presentes
- ✅ API endpoints funcionando

### **Verificación Manual**
1. **Base de datos:**
   - Ve a `http://localhost/phpmyadmin`
   - Selecciona `test_transito`
   - Verifica las tablas: `users`, `categories`, `questions`

2. **Sistema web:**
   - Ve a `http://localhost/test-transito/`
   - Haz login con `admin` / `admin123`
   - Prueba todas las funcionalidades

## 🎯 **Pasos Rápidos (Resumen)**

1. **Iniciar XAMPP** → Apache + MySQL
2. **Copiar proyecto** → `C:\xampp\htdocs\test-transito\`
3. **Ejecutar instalador** → `http://localhost/test-transito/install.php`
4. **Insertar preguntas** → `http://localhost/test-transito/insert_questions.php`
5. **Probar sistema** → `http://localhost/test-transito/test_system.php`
6. **Acceder al sistema** → `http://localhost/test-transito/`

## 📝 **Estructura de Imágenes**

El sistema maneja las imágenes de la siguiente manera:

- **Ubicación:** `assets/img/questions/`
- **Formato:** PNG
- **Nomenclatura:** `i{NRO}.png`
- **Ejemplo:** `i10.png`, `i825.png`

Donde `NRO` es el campo de la base de datos que identifica cada pregunta.

## 🆘 **Si algo no funciona**

1. **Revisar logs de XAMPP**
   - Ve a `C:\xampp\apache\logs\error.log`
   - Ve a `C:\xampp\mysql\data\mysql_error.log`

2. **Verificar configuración**
   - Asegúrate de que XAMPP esté en el puerto 80 (Apache) y 3306 (MySQL)
   - Verifica que no haya conflictos con otros servicios

3. **Reiniciar servicios**
   - Detén Apache y MySQL en XAMPP
   - Inícialos nuevamente

4. **Ejecutar script de prueba**
   - Ve a `http://localhost/test-transito/test_system.php`
   - Revisa los errores reportados

**¡Con estos pasos deberías tener el sistema funcionando perfectamente en XAMPP!**