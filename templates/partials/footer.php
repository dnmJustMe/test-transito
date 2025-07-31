<footer class="bg-dark text-light py-4 mt-5">
    <div class="container">
        <div class="row">
            <div class="col-md-6">
                <div class="d-flex align-items-center mb-3">
                    <img src="<?= BASE_URL ?>/assets/images/logo.png" alt="Logo" height="30" class="me-2">
                    <h5 class="mb-0">Test Licencia Cuba</h5>
                </div>
                <p class="text-muted mb-2">
                    Simulador oficial del examen teórico para licencia de conducir en Cuba.
                    Practica con preguntas reales y evalúa tus conocimientos.
                </p>
                <div class="d-flex gap-3">
                    <a href="#" class="text-light" title="Facebook">
                        <i class="bi bi-facebook fs-5"></i>
                    </a>
                    <a href="#" class="text-light" title="Twitter">
                        <i class="bi bi-twitter fs-5"></i>
                    </a>
                    <a href="#" class="text-light" title="Instagram">
                        <i class="bi bi-instagram fs-5"></i>
                    </a>
                    <a href="#" class="text-light" title="YouTube">
                        <i class="bi bi-youtube fs-5"></i>
                    </a>
                </div>
            </div>
            
            <div class="col-md-3">
                <h6 class="fw-bold mb-3">Enlaces Útiles</h6>
                <ul class="list-unstyled">
                    <li><a href="<?= BASE_URL ?>" class="text-muted text-decoration-none">
                        <i class="bi bi-house me-2"></i>Inicio
                    </a></li>
                    <?php if (isset($currentUser)): ?>
                        <li><a href="<?= BASE_URL ?>/test" class="text-muted text-decoration-none">
                            <i class="bi bi-play-circle me-2"></i>Realizar Test
                        </a></li>
                        <li><a href="<?= BASE_URL ?>/history" class="text-muted text-decoration-none">
                            <i class="bi bi-clock-history me-2"></i>Mi Historial
                        </a></li>
                        <li><a href="<?= BASE_URL ?>/profile" class="text-muted text-decoration-none">
                            <i class="bi bi-person me-2"></i>Mi Perfil
                        </a></li>
                    <?php else: ?>
                        <li><a href="<?= BASE_URL ?>/login" class="text-muted text-decoration-none">
                            <i class="bi bi-box-arrow-in-right me-2"></i>Iniciar Sesión
                        </a></li>
                        <li><a href="<?= BASE_URL ?>/register" class="text-muted text-decoration-none">
                            <i class="bi bi-person-plus me-2"></i>Registrarse
                        </a></li>
                    <?php endif; ?>
                    <li><a href="#" class="text-muted text-decoration-none" onclick="showHelp()">
                        <i class="bi bi-question-circle me-2"></i>Ayuda
                    </a></li>
                </ul>
            </div>
            
            <div class="col-md-3">
                <h6 class="fw-bold mb-3">Información</h6>
                <ul class="list-unstyled">
                    <li><a href="#" class="text-muted text-decoration-none" onclick="showAbout()">
                        <i class="bi bi-info-circle me-2"></i>Acerca de
                    </a></li>
                    <li><a href="#" class="text-muted text-decoration-none" onclick="showPrivacyPolicy()">
                        <i class="bi bi-shield-check me-2"></i>Privacidad
                    </a></li>
                    <li><a href="#" class="text-muted text-decoration-none" onclick="showTerms()">
                        <i class="bi bi-file-text me-2"></i>Términos de Uso
                    </a></li>
                    <li><a href="#" class="text-muted text-decoration-none" onclick="showContact()">
                        <i class="bi bi-envelope me-2"></i>Contacto
                    </a></li>
                </ul>
                
                <?php if (isset($currentUser) && $currentUser['role'] === 'admin'): ?>
                    <div class="mt-3">
                        <h6 class="fw-bold mb-2 text-warning">Admin</h6>
                        <a href="<?= BASE_URL ?>/admin/dashboard" class="text-warning text-decoration-none">
                            <i class="bi bi-speedometer2 me-2"></i>Panel de Control
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <hr class="my-4">
        
        <div class="row align-items-center">
            <div class="col-md-6">
                <p class="text-muted mb-0">
                    &copy; <?= date('Y') ?> Test Licencia Cuba. Todos los derechos reservados.
                </p>
            </div>
            <div class="col-md-6 text-md-end">
                <p class="text-muted mb-0">
                    Desarrollado con <i class="bi bi-heart-fill text-danger"></i> por 
                    <a href="#" class="text-light text-decoration-none fw-bold" onclick="showCredits()">DNMJustMe</a>
                </p>
            </div>
        </div>
        
        <!-- Estadísticas del sistema (para usuarios logueados) -->
        <?php if (isset($currentUser)): ?>
            <div class="row mt-3 pt-3 border-top border-secondary">
                <div class="col-12">
                    <div class="row text-center">
                        <div class="col-6 col-md-3">
                            <div class="text-primary">
                                <i class="bi bi-people fs-4"></i>
                                <div class="fw-bold" id="totalUsers">-</div>
                                <small class="text-muted">Usuarios</small>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="text-success">
                                <i class="bi bi-clipboard-check fs-4"></i>
                                <div class="fw-bold" id="totalTests">-</div>
                                <small class="text-muted">Tests Realizados</small>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="text-info">
                                <i class="bi bi-question-circle fs-4"></i>
                                <div class="fw-bold" id="totalQuestions">-</div>
                                <small class="text-muted">Preguntas</small>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="text-warning">
                                <i class="bi bi-trophy fs-4"></i>
                                <div class="fw-bold" id="avgScore">-</div>
                                <small class="text-muted">Promedio General</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</footer>

<!-- Back to top button -->
<button id="backToTop" class="btn btn-primary position-fixed bottom-0 end-0 m-3 rounded-circle d-none" style="z-index: 1000;">
    <i class="bi bi-arrow-up"></i>
</button>

<script>
// Back to top functionality
document.addEventListener('DOMContentLoaded', function() {
    const backToTopBtn = document.getElementById('backToTop');
    
    window.addEventListener('scroll', function() {
        if (window.pageYOffset > 300) {
            backToTopBtn.classList.remove('d-none');
        } else {
            backToTopBtn.classList.add('d-none');
        }
    });
    
    backToTopBtn.addEventListener('click', function() {
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    });
    
    // Cargar estadísticas del sistema
    if (window.App.user) {
        loadSystemStats();
    }
});

function loadSystemStats() {
    // Simular carga de estadísticas (en producción usar API real)
    setTimeout(() => {
        document.getElementById('totalUsers').textContent = '1,234';
        document.getElementById('totalTests').textContent = '5,678';
        document.getElementById('totalQuestions').textContent = '150';
        document.getElementById('avgScore').textContent = '78%';
    }, 1000);
}

function showHelp() {
    Swal.fire({
        title: 'Ayuda',
        html: `
            <div class="text-start">
                <h6>¿Cómo usar el simulador?</h6>
                <ol>
                    <li>Regístrate o inicia sesión</li>
                    <li>Selecciona la cantidad de preguntas</li>
                    <li>Responde cada pregunta cuidadosamente</li>
                    <li>Revisa tus resultados al finalizar</li>
                </ol>
                
                <h6 class="mt-3">Puntuación</h6>
                <p>Necesitas al menos <strong>${window.App.config.passingScore}%</strong> para aprobar el test.</p>
                
                <h6 class="mt-3">¿Problemas técnicos?</h6>
                <p>Si experimentas algún problema, contacta al soporte técnico.</p>
            </div>
        `,
        icon: 'info',
        confirmButtonText: 'Entendido'
    });
}

function showAbout() {
    Swal.fire({
        title: 'Acerca de Test Licencia Cuba',
        html: `
            <div class="text-start">
                <p>Este simulador ha sido desarrollado para ayudar a los aspirantes a obtener su licencia de conducir en Cuba.</p>
                
                <h6>Características:</h6>
                <ul>
                    <li>Preguntas oficiales actualizadas</li>
                    <li>Múltiples modalidades de test</li>
                    <li>Seguimiento de progreso</li>
                    <li>Estadísticas detalladas</li>
                    <li>Interfaz intuitiva y moderna</li>
                </ul>
                
                <p class="mt-3"><strong>Versión:</strong> 1.0.0</p>
                <p><strong>Desarrollado por:</strong> DNMJustMe</p>
            </div>
        `,
        icon: 'info',
        confirmButtonText: 'Cerrar'
    });
}

function showPrivacyPolicy() {
    Swal.fire({
        title: 'Política de Privacidad',
        html: `
            <div class="text-start">
                <p>Tu privacidad es importante para nosotros.</p>
                
                <h6>Información que recopilamos:</h6>
                <ul>
                    <li>Datos de registro (nombre, email, username)</li>
                    <li>Resultados de tests y estadísticas</li>
                    <li>Información de uso de la aplicación</li>
                </ul>
                
                <h6>Uso de la información:</h6>
                <ul>
                    <li>Proporcionar el servicio de simulación</li>
                    <li>Mejorar la experiencia del usuario</li>
                    <li>Generar estadísticas anónimas</li>
                </ul>
                
                <p class="mt-3">No compartimos tu información personal con terceros sin tu consentimiento.</p>
            </div>
        `,
        icon: 'info',
        confirmButtonText: 'Entendido'
    });
}

function showTerms() {
    Swal.fire({
        title: 'Términos de Uso',
        html: `
            <div class="text-start">
                <h6>Condiciones de uso:</h6>
                <ul>
                    <li>Usar el servicio de manera responsable</li>
                    <li>No intentar hackear o comprometer el sistema</li>
                    <li>Respetar a otros usuarios</li>
                    <li>No compartir credenciales de acceso</li>
                </ul>
                
                <h6>Limitaciones:</h6>
                <ul>
                    <li>El servicio se proporciona "tal como está"</li>
                    <li>No garantizamos disponibilidad 100%</li>
                    <li>Nos reservamos el derecho de suspender cuentas</li>
                </ul>
                
                <p class="mt-3">Al usar este servicio, aceptas estos términos.</p>
            </div>
        `,
        icon: 'info',
        confirmButtonText: 'Acepto'
    });
}

function showContact() {
    Swal.fire({
        title: 'Contacto',
        html: `
            <div class="text-start">
                <h6>¿Necesitas ayuda?</h6>
                <p>Puedes contactarnos a través de los siguientes medios:</p>
                
                <div class="d-flex align-items-center mb-2">
                    <i class="bi bi-envelope me-3 text-primary"></i>
                    <span>soporte@testlicenciacuba.cu</span>
                </div>
                
                <div class="d-flex align-items-center mb-2">
                    <i class="bi bi-telephone me-3 text-primary"></i>
                    <span>+53 7 123-4567</span>
                </div>
                
                <div class="d-flex align-items-center mb-2">
                    <i class="bi bi-geo-alt me-3 text-primary"></i>
                    <span>Havana, Cuba</span>
                </div>
                
                <hr>
                <p class="text-muted small">Horario de atención: Lunes a Viernes, 8:00 AM - 5:00 PM</p>
            </div>
        `,
        icon: 'info',
        confirmButtonText: 'Cerrar'
    });
}

function showCredits() {
    Swal.fire({
        title: 'Créditos',
        html: `
            <div class="text-center">
                <div class="mb-3">
                    <i class="bi bi-person-circle display-4 text-primary"></i>
                </div>
                
                <h5>DNMJustMe</h5>
                <p class="text-muted">Desarrollador Full Stack</p>
                
                <div class="d-flex justify-content-center gap-3 mt-3">
                    <a href="#" class="text-decoration-none">
                        <i class="bi bi-github fs-4"></i>
                    </a>
                    <a href="#" class="text-decoration-none">
                        <i class="bi bi-linkedin fs-4"></i>
                    </a>
                    <a href="#" class="text-decoration-none">
                        <i class="bi bi-twitter fs-4"></i>
                    </a>
                </div>
                
                <hr>
                <p class="small text-muted">
                    Desarrollado con PHP, MySQL, Bootstrap y mucho ☕
                </p>
                <p class="small">
                    &copy; ${new Date().getFullYear()} - Todos los derechos reservados
                </p>
            </div>
        `,
        confirmButtonText: 'Cerrar',
        width: '500px'
    });
}
</script>