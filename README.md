# Sistema de Test de Tránsito

Un sistema completo de evaluación para licencias de conducir con frontend moderno y API REST robusta.

## 🚀 Características

### Frontend
- **HTML5, CSS3, Bootstrap 5** - Diseño moderno y responsive
- **JavaScript + jQuery** - Interactividad y validaciones
- **SweetAlert2** - Alertas y modales elegantes
- **Animate.css** - Animaciones suaves
- **Bootstrap Icons** - Iconografía consistente
- **URLs amigables** - Navegación limpia
- **Validaciones en tiempo real** - Experiencia de usuario mejorada

### Backend
- **PHP 7.4+** - Backend robusto
- **MySQL** - Base de datos relacional
- **API REST** - Arquitectura escalable
- **JWT Authentication** - Autenticación segura
- **PDO** - Conexión segura a base de datos
- **CORS** - Soporte para aplicaciones móviles

### Funcionalidades
- ✅ **Registro e inicio de sesión** de usuarios
- ✅ **Tests por categorías** (Señales, Leyes, Mecánica, etc.)
- ✅ **Sistema de puntuación** con aprobación/reprobación
- ✅ **Historial de tests** con revisión detallada
- ✅ **Panel de administración** para gestionar preguntas
- ✅ **Subida de imágenes** para preguntas
- ✅ **Estadísticas** de rendimiento
- ✅ **Timer** con advertencias
- ✅ **Responsive design** para móviles

## 📋 Requisitos

- PHP 7.4 o superior
- MySQL 5.7 o superior
- Apache/Nginx con mod_rewrite habilitado
- Extensiones PHP: PDO, PDO_MySQL, JSON, mbstring

## 🛠️ Instalación

### 1. Clonar el repositorio
```bash
git clone <url-del-repositorio>
cd test-transito
```

### 2. Configurar la base de datos
```bash
# Importar el esquema de la base de datos
mysql -u root -p < database/schema.sql
```

### 3. Configurar la aplicación
Editar el archivo `api/config/config.php`:
```php
// Configuración de la base de datos
define('DB_HOST', 'localhost');
define('DB_NAME', 'test_transito');
define('DB_USER', 'tu_usuario');
define('DB_PASS', 'tu_contraseña');

// URL base (ajustar según tu configuración)
define('BASE_URL', 'http://localhost/test-transito/');
```

### 4. Configurar el servidor web

#### Apache (.htaccess ya incluido)
Asegúrate de que mod_rewrite esté habilitado:
```bash
sudo a2enmod rewrite
sudo systemctl restart apache2
```

#### Nginx
```nginx
location /test-transito {
    try_files $uri $uri/ /test-transito/index.html;
    
    location ~ ^/test-transito/api/(.*)$ {
        try_files $uri $uri/ /test-transito/api/index.php?$args;
    }
}
```

### 5. Permisos de archivos
```bash
chmod 755 assets/img/questions/
chmod 644 logs/
```

## 🗄️ Estructura de la Base de Datos

### Tablas principales:
- **users** - Usuarios del sistema
- **categories** - Categorías de preguntas
- **questions** - Preguntas del test
- **test_sessions** - Sesiones de test
- **user_answers** - Respuestas de usuarios
- **system_config** - Configuración del sistema

### Usuario administrador por defecto:
- **Email**: admin@test-transito.com
- **Contraseña**: admin123
- **Rol**: admin

## 🚀 Uso

### Para usuarios:
1. Registrarse en el sistema
2. Seleccionar una categoría de test
3. Elegir número de preguntas
4. Realizar el test
5. Ver resultados y estadísticas

### Para administradores:
1. Iniciar sesión con credenciales de admin
2. Acceder al panel de administración
3. Gestionar categorías y preguntas
4. Subir imágenes para preguntas
5. Ver estadísticas del sistema

## 📡 API REST

### Endpoints principales:

#### Autenticación
- `POST /api/auth/register` - Registro de usuarios
- `POST /api/auth/login` - Inicio de sesión
- `GET /api/auth/profile` - Perfil del usuario
- `PUT /api/auth/update-profile` - Actualizar perfil

#### Preguntas
- `GET /api/questions/` - Listar preguntas
- `POST /api/questions/` - Crear pregunta (admin)
- `PUT /api/questions/{id}` - Actualizar pregunta (admin)
- `DELETE /api/questions/{id}` - Eliminar pregunta (admin)
- `POST /api/questions/start-test` - Iniciar test
- `POST /api/questions/submit-answer` - Enviar respuesta
- `POST /api/questions/finish-test/{session_id}` - Finalizar test

#### Categorías
- `GET /api/categories/` - Listar categorías
- `POST /api/categories/` - Crear categoría (admin)
- `PUT /api/categories/{id}` - Actualizar categoría (admin)
- `DELETE /api/categories/{id}` - Eliminar categoría (admin)

#### Sesiones
- `GET /api/sessions/` - Historial de tests
- `GET /api/sessions/stats` - Estadísticas del usuario
- `GET /api/sessions/{id}` - Detalles de sesión

## 🔧 Configuración Avanzada

### Variables de entorno
Puedes crear un archivo `.env` para configuraciones adicionales:
```env
DB_HOST=localhost
DB_NAME=test_transito
DB_USER=root
DB_PASS=
JWT_SECRET=tu_clave_secreta_muy_segura_aqui_2024
BASE_URL=http://localhost/test-transito/
```

### Personalización
- **Colores**: Editar variables CSS en `assets/css/style.css`
- **Configuración**: Modificar `api/config/config.php`
- **Preguntas**: Usar el panel de administración
- **Imágenes**: Subir a `assets/img/questions/`

## 📱 Soporte Móvil

El sistema está optimizado para dispositivos móviles y puede ser usado como API para aplicaciones móviles nativas.

### Headers requeridos para API móvil:
```
Authorization: Bearer <token>
Content-Type: application/json
```

## 🔒 Seguridad

- **JWT Tokens** para autenticación
- **Validación de entrada** en frontend y backend
- **Preparación de consultas** para prevenir SQL injection
- **CORS configurado** para APIs
- **Contraseñas hasheadas** con password_hash()
- **Control de acceso** basado en roles

## 🐛 Solución de Problemas

### Error de conexión a la base de datos
- Verificar credenciales en `api/config/config.php`
- Asegurar que MySQL esté ejecutándose
- Verificar que la base de datos exista

### Error 404 en rutas
- Verificar que mod_rewrite esté habilitado
- Revisar configuración del servidor web
- Verificar archivo `.htaccess`

### Error de permisos
- Verificar permisos de directorios
- Asegurar que PHP pueda escribir en logs/
- Verificar permisos de assets/img/questions/

### Problemas con imágenes
- Verificar que el directorio assets/img/questions/ exista
- Verificar permisos de escritura
- Revisar configuración de MAX_FILE_SIZE

## 📈 Estadísticas

El sistema incluye:
- **Estadísticas de usuario**: Tests completados, promedio, mejor puntuación
- **Estadísticas de administrador**: Total de preguntas, categorías, usuarios, tests
- **Historial detallado**: Revisión de respuestas con explicaciones
- **Progreso en tiempo real**: Timer y contador de preguntas

## 🤝 Contribución

1. Fork el proyecto
2. Crear una rama para tu feature (`git checkout -b feature/AmazingFeature`)
3. Commit tus cambios (`git commit -m 'Add some AmazingFeature'`)
4. Push a la rama (`git push origin feature/AmazingFeature`)
5. Abrir un Pull Request

## 📄 Licencia

Este proyecto está bajo la Licencia MIT. Ver el archivo `LICENSE` para más detalles.

## 📞 Soporte

Para soporte técnico o preguntas:
- Crear un issue en GitHub
- Contactar al desarrollador principal

---

**Desarrollado con ❤️ para ayudar a obtener licencias de conducir de manera segura y efectiva.**
