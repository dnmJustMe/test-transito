// Configuración global
const API_BASE_URL = 'http://localhost/test-transito/api/';
let currentUser = null;
let currentTest = null;
let testTimer = null;
let lifeRegenerationTimer = null;

// Configurar SweetAlert2
Swal.mixin({
    toast: true,
    position: 'top-end',
    showConfirmButton: false,
    timer: 3000,
    timerProgressBar: true
});

// Inicializar la aplicación
$(document).ready(function() {
    console.log('Inicializando aplicación...');
    setupEventListeners();
    checkAuthStatus();
    showSection('home');
    loadPublicStats();
    
    // Debug: Verificar estado de autenticación cada 5 segundos
    setInterval(function() {
        console.log('Estado actual:', {
            currentUser: currentUser,
            token: localStorage.getItem('token'),
            userMenuVisible: $('#userMenu').is(':visible'),
            authButtonsVisible: $('#authButtons').is(':visible')
        });
    }, 5000);
});

function setupEventListeners() {
    // Navegación
    $('.nav-link').on('click', function(e) {
        e.preventDefault();
        const href = $(this).attr('href');
        if (href && href !== '#') {
            const target = href.substring(1);
            showSection(target);
        }
    });

    // Login
    $('#loginForm').on('submit', function(e) {
        e.preventDefault();
        login();
    });

    // Registro
    $('#registerForm').on('submit', function(e) {
        e.preventDefault();
        register();
    });

    // Logout
    $('#logoutBtn').on('click', function(e) {
        e.preventDefault();
        logout();
    });

    // Perfil
    $('#profileForm').on('submit', function(e) {
        e.preventDefault();
        updateProfile();
    });

    // Botones de test
    $('.start-test-btn').on('click', function() {
        const difficulty = $(this).data('difficulty');
        startTest(difficulty);
    });

    // Finalizar test
    $('#finishTestBtn').on('click', function() {
        finishTest();
    });

    // Navegación de preguntas
    $('#nextQuestionBtn').on('click', function() {
        nextQuestion();
    });

    $('#prevQuestionBtn').on('click', function() {
        prevQuestion();
    });

    // Formulario de agregar pregunta
    $('#addQuestionForm').on('submit', function(e) {
        e.preventDefault();
        addQuestion();
    });
}

// Funciones de modal que faltaban
function showLoginModal() {
    $('#loginModal').modal('show');
}

function showRegisterModal() {
    $('#registerModal').modal('show');
}

function showAddQuestionModal() {
    $('#addQuestionModal').modal('show');
}

function checkAuthStatus() {
    const token = localStorage.getItem('token');
    if (token) {
        // Verificar token válido
        $.ajax({
            url: API_BASE_URL + 'auth/profile',
            method: 'GET',
            headers: {
                'Authorization': 'Bearer ' + token
            },
            success: function(response) {
                if (response.success) {
                    currentUser = response.data;
                    updateUIForLoggedInUser();
                    startLifeRegenerationTimer();
                } else {
                    localStorage.removeItem('token');
                    updateUIForGuest();
                }
            },
            error: function() {
                localStorage.removeItem('token');
                updateUIForGuest();
            }
        });
    } else {
        updateUIForGuest();
    }
}

function updateUIForLoggedInUser() {
    console.log('Actualizando UI para usuario logueado:', currentUser);
    
    // Ocultar elementos de invitado
    $('.guest-only').hide();
    
    // Mostrar elementos de usuario
    $('.user-only').show();
    
    // Mostrar elementos de admin si corresponde
    if (currentUser.role === 'admin') {
        $('.admin-only').show();
        console.log('Usuario es administrador');
    } else {
        $('.admin-only').hide();
    }
    
    // Mostrar menú de usuario y ocultar botones de autenticación
    $('#userMenu').show();
    $('#userMenu').css('display', 'flex !important');
    $('#authButtons').hide();
    
    // Actualizar información del usuario
    $('#userName').text(currentUser.first_name + ' ' + currentUser.last_name);
    $('#userRole').text(currentUser.role === 'admin' ? 'Administrador' : 'Usuario');
    $('#userLives').text(currentUser.lives || 3);
    
    // Actualizar vidas en la sección de tests
    $('#currentLives').text(currentUser.lives || 3);
    
    console.log('UI actualizada correctamente');
}

function updateUIForGuest() {
    console.log('Actualizando UI para invitado');
    
    // Mostrar elementos de invitado
    $('.guest-only').show();
    
    // Ocultar elementos de usuario y admin
    $('.user-only').hide();
    $('.admin-only').hide();
    
    // Ocultar menú de usuario y mostrar botones de autenticación
    $('#userMenu').hide();
    $('#userMenu').css('display', 'none !important');
    $('#authButtons').show();
    $('#authButtons').css('display', 'flex !important');
    
    // Limpiar cualquier estado del dropdown
    $('.dropdown-menu').removeClass('show');
    $('.dropdown-toggle').removeClass('show');
    
    // Limpiar información del usuario
    $('#userName').text('Usuario');
    $('#userRole').text('Usuario');
    $('#userLives').text('3');
    
    currentUser = null;
    
    if (lifeRegenerationTimer) {
        clearInterval(lifeRegenerationTimer);
        lifeRegenerationTimer = null;
    }
    
    console.log('UI actualizada para invitado');
}

function showSection(sectionName) {
    if (!sectionName) {
        sectionName = 'home';
    }
    
    $('section').hide();
    const targetSection = $('#' + sectionName);
    
    if (targetSection.length > 0) {
        targetSection.show();
        
        // Actualizar navegación activa
        $('.nav-link').removeClass('active');
        $('[href="#' + sectionName + '"]').addClass('active');
        
        // Cargar contenido específico según la sección
        switch(sectionName) {
            case 'tests':
                if (currentUser) {
                    loadUserLives();
                } else {
                    showSection('home');
                    Swal.fire('Acceso Restringido', 'Debes iniciar sesión para realizar tests', 'warning');
                }
                break;
            case 'history':
                if (currentUser) {
                    loadHistory();
                } else {
                    showSection('home');
                    Swal.fire('Acceso Restringido', 'Debes iniciar sesión para ver tu historial', 'warning');
                }
                break;
            case 'profile':
                if (currentUser) {
                    loadProfile();
                } else {
                    showSection('home');
                    Swal.fire('Acceso Restringido', 'Debes iniciar sesión para ver tu perfil', 'warning');
                }
                break;
            case 'stats':
                if (currentUser) {
                    loadUserStats();
                } else {
                    showSection('home');
                    Swal.fire('Acceso Restringido', 'Debes iniciar sesión para ver tus estadísticas', 'warning');
                }
                break;
            case 'admin':
                if (currentUser && currentUser.role === 'admin') {
                    loadAdminDashboard();
                } else {
                    showSection('home');
                    Swal.fire('Acceso Denegado', 'Solo los administradores pueden acceder a esta sección', 'error');
                }
                break;
            case 'home':
                loadPublicStats();
                break;
        }
    } else {
        // Si la sección no existe, mostrar home
        showSection('home');
    }
}

function loadPublicStats() {
    $.ajax({
        url: API_BASE_URL + 'sessions/public-stats',
        method: 'GET',
        success: function(response) {
            if (response.success) {
                $('#totalUsers').text(response.data.total_users);
                $('#totalTests').text(response.data.total_tests);
                $('#totalQuestions').text(response.data.total_questions);
                $('#averageScore').text(response.data.average_score + '%');
            }
        },
        error: function() {
            // Valores por defecto
            $('#totalUsers').text('0');
            $('#totalTests').text('0');
            $('#totalQuestions').text('0');
            $('#averageScore').text('0%');
        }
    });
}

function loadUserLives() {
    if (!currentUser) return;
    
    $.ajax({
        url: API_BASE_URL + 'auth/lives',
        method: 'GET',
        headers: {
            'Authorization': 'Bearer ' + localStorage.getItem('token')
        },
        success: function(response) {
            if (response.success) {
                currentUser.lives = response.data.lives;
                $('#currentLives').text(response.data.lives);
                $('#userLives').text(response.data.lives);
                
                if (!response.data.can_take_test) {
                    $('.start-test-btn').prop('disabled', true);
                    $('#lifeRegenerationInfo').show();
                    
                    if (response.data.last_life_lost) {
                        const lastLost = new Date(response.data.last_life_lost);
                        const now = new Date();
                        const diff = now - lastLost;
                        const minutesPassed = Math.floor(diff / (1000 * 60));
                        const minutesLeft = Math.max(0, 5 - minutesPassed);
                        
                        if (minutesLeft > 0) {
                            $('#regenerationTime').text(minutesLeft + ' minutos');
                        } else {
                            $('#lifeRegenerationInfo').hide();
                            $('.start-test-btn').prop('disabled', false);
                        }
                    }
                } else {
                    $('.start-test-btn').prop('disabled', false);
                    $('#lifeRegenerationInfo').hide();
                }
            }
        }
    });
}

function startLifeRegenerationTimer() {
    if (lifeRegenerationTimer) {
        clearInterval(lifeRegenerationTimer);
    }
    
    lifeRegenerationTimer = setInterval(function() {
        if (currentUser) {
            loadUserLives();
        }
    }, 30000); // Verificar cada 30 segundos
}

function login() {
    const email = $('#loginEmail').val();
    const password = $('#loginPassword').val();
    
    if (!email || !password) {
        Swal.fire('Error', 'Por favor completa todos los campos', 'error');
        return;
    }
    
    $.ajax({
        url: API_BASE_URL + 'auth/login',
        method: 'POST',
        contentType: 'application/json',
        data: JSON.stringify({
            email: email,
            password: password
        }),
        success: function(response) {
            if (response.success) {
                localStorage.setItem('token', response.data.token);
                currentUser = response.data.user;
                
                $('#loginModal').modal('hide');
                $('#loginForm')[0].reset();
                
                updateUIForLoggedInUser();
                startLifeRegenerationTimer();
                showSection('home');
                
                Swal.fire('¡Bienvenido!', 'Has iniciado sesión correctamente', 'success');
            } else {
                Swal.fire('Error', response.message || 'Error al iniciar sesión', 'error');
            }
        },
        error: function(xhr) {
            const response = xhr.responseJSON;
            Swal.fire('Error', response?.message || 'Error al iniciar sesión', 'error');
        }
    });
}

function register() {
    const formData = {
        username: $('#registerUsername').val(),
        email: $('#registerEmail').val(),
        password: $('#registerPassword').val(),
        first_name: $('#registerFirstName').val(),
        last_name: $('#registerLastName').val()
    };
    
    // Validaciones
    if (!formData.username || !formData.email || !formData.password || !formData.first_name || !formData.last_name) {
        Swal.fire('Error', 'Por favor completa todos los campos', 'error');
        return;
    }
    
    if (formData.password.length < 6) {
        Swal.fire('Error', 'La contraseña debe tener al menos 6 caracteres', 'error');
        return;
    }
    
    $.ajax({
        url: API_BASE_URL + 'auth/register',
        method: 'POST',
        contentType: 'application/json',
        data: JSON.stringify(formData),
        success: function(response) {
            if (response.success) {
                $('#registerModal').modal('hide');
                $('#registerForm')[0].reset();
                
                Swal.fire('¡Registro Exitoso!', 'Tu cuenta ha sido creada. Ahora puedes iniciar sesión.', 'success');
            } else {
                Swal.fire('Error', response.message || 'Error al registrar usuario', 'error');
            }
        },
        error: function(xhr) {
            const response = xhr.responseJSON;
            Swal.fire('Error', response?.message || 'Error al registrar usuario', 'error');
        }
    });
}

function logout() {
    localStorage.removeItem('token');
    currentUser = null;
    updateUIForGuest();
    showSection('home');
    
    Swal.fire('Sesión Cerrada', 'Has cerrado sesión correctamente', 'info');
}

function loadProfile() {
    if (!currentUser) return;
    
    // Cargar datos del perfil
    $('#profileFirstName').val(currentUser.first_name);
    $('#profileLastName').val(currentUser.last_name);
    $('#profileEmail').val(currentUser.email);
    $('#profileUsername').val(currentUser.username);
}

function updateProfile() {
    const formData = {
        first_name: $('#profileFirstName').val(),
        last_name: $('#profileLastName').val(),
        email: $('#profileEmail').val(),
        username: $('#profileUsername').val()
    };
    
    // Validaciones
    if (!formData.first_name || !formData.last_name || !formData.email || !formData.username) {
        Swal.fire('Error', 'Por favor completa todos los campos', 'error');
        return;
    }
    
    $.ajax({
        url: API_BASE_URL + 'auth/profile',
        method: 'PUT',
        contentType: 'application/json',
        headers: {
            'Authorization': 'Bearer ' + localStorage.getItem('token')
        },
        data: JSON.stringify(formData),
        success: function(response) {
            if (response.success) {
                currentUser = response.data;
                updateUIForLoggedInUser();
                Swal.fire('Éxito', 'Perfil actualizado correctamente', 'success');
            } else {
                Swal.fire('Error', response.message || 'Error al actualizar perfil', 'error');
            }
        },
        error: function(xhr) {
            const response = xhr.responseJSON;
            Swal.fire('Error', response?.message || 'Error al actualizar perfil', 'error');
        }
    });
}

function loadUserStats() {
    $.ajax({
        url: API_BASE_URL + 'sessions/stats',
        method: 'GET',
        headers: {
            'Authorization': 'Bearer ' + localStorage.getItem('token')
        },
        success: function(response) {
            if (response.success) {
                const stats = response.data;
                $('#statsTestsCompleted').text(stats.total_tests || 0);
                $('#statsAverageScore').text((stats.average_score || 0) + '%');
                $('#statsBestScore').text((stats.best_score || 0) + '%');
                $('#statsPassedTests').text(stats.passed_tests || 0);
            }
        },
        error: function() {
            // Mostrar datos por defecto si hay error
            $('#statsTestsCompleted').text('0');
            $('#statsAverageScore').text('0%');
            $('#statsBestScore').text('0%');
            $('#statsPassedTests').text('0');
        }
    });
}

function startTest(difficulty) {
    if (!currentUser) {
        Swal.fire('Error', 'Debes iniciar sesión para realizar tests', 'error');
        return;
    }
    
    $.ajax({
        url: API_BASE_URL + 'questions/start-test',
        method: 'POST',
        contentType: 'application/json',
        headers: {
            'Authorization': 'Bearer ' + localStorage.getItem('token')
        },
        data: JSON.stringify({
            difficulty: difficulty
        }),
        success: function(response) {
            if (response.success) {
                currentTest = {
                    questions: response.data.questions,
                    currentQuestion: 0,
                    answers: {},
                    startTime: Date.now(),
                    difficulty: difficulty
                };
                
                showTestInterface();
                displayQuestion(0);
                startTimer();
            } else {
                Swal.fire('Error', response.message || 'Error al iniciar el test', 'error');
            }
        },
        error: function(xhr) {
            const response = xhr.responseJSON;
            Swal.fire('Error', response?.message || 'Error al iniciar el test', 'error');
        }
    });
}

function showTestInterface() {
    showSection('test-interface');
    $('#testProgress').show();
    updateProgressBar();
}

function displayQuestion(questionIndex) {
    if (!currentTest || !currentTest.questions[questionIndex]) {
        return;
    }
    
    const question = currentTest.questions[questionIndex];
    const isAnswered = currentTest.answers[questionIndex] !== undefined;
    
    $('#questionText').text(question.question_text);
    $('#answer1Label').text(question.answer1);
    $('#answer2Label').text(question.answer2);
    $('#answer3Label').text(question.answer3);
    
    // Reset radio buttons
    $('input[name="answer"]').prop('checked', false);
    
    // Check previously selected answer
    if (isAnswered) {
        $(`input[name="answer"][value="${currentTest.answers[questionIndex]}"]`).prop('checked', true);
    }
    
    // Show/hide image if exists
    if (question.image_path) {
        $('#questionImage').attr('src', question.image_path).show();
        $('#questionImageContainer').show();
    } else {
        $('#questionImageContainer').hide();
    }
    
    // Update navigation buttons
    $('#prevQuestionBtn').prop('disabled', questionIndex === 0);
    $('#nextQuestionBtn').prop('disabled', questionIndex === currentTest.questions.length - 1);
    
    // Update progress
    updateProgressBar();
}

function updateProgressBar() {
    if (!currentTest) return;
    
    const total = currentTest.questions.length;
    const answered = Object.keys(currentTest.answers).length;
    const current = currentTest.currentQuestion + 1;
    
    const progress = (answered / total) * 100;
    $('#progressBar').css('width', progress + '%');
    $('#progressText').text(`${answered}/${total} respondidas`);
    $('#questionCounter').text(`Pregunta ${current} de ${total}`);
}

function nextQuestion() {
    if (currentTest && currentTest.currentQuestion < currentTest.questions.length - 1) {
        saveCurrentAnswer();
        currentTest.currentQuestion++;
        displayQuestion(currentTest.currentQuestion);
    }
}

function prevQuestion() {
    if (currentTest && currentTest.currentQuestion > 0) {
        saveCurrentAnswer();
        currentTest.currentQuestion--;
        displayQuestion(currentTest.currentQuestion);
    }
}

function saveCurrentAnswer() {
    const selectedAnswer = $('input[name="answer"]:checked').val();
    if (selectedAnswer) {
        currentTest.answers[currentTest.currentQuestion] = parseInt(selectedAnswer);
        updateProgressBar();
    }
}

function startTimer() {
    const duration = 20 * 60; // 20 minutos
    let timeLeft = duration;
    
    testTimer = setInterval(function() {
        timeLeft--;
        const minutes = Math.floor(timeLeft / 60);
        const seconds = timeLeft % 60;
        
        $('#testTimer').text(`${minutes}:${seconds.toString().padStart(2, '0')}`);
        
        if (timeLeft <= 0) {
            clearInterval(testTimer);
            finishTest();
        }
    }, 1000);
}

function finishTest() {
    if (testTimer) {
        clearInterval(testTimer);
    }
    
    saveCurrentAnswer();
    
    // Preparar respuestas para enviar
    const answers = [];
    currentTest.questions.forEach((question, index) => {
        const userAnswer = currentTest.answers[index];
        if (userAnswer) {
            answers.push({
                question_id: question.id,
                user_answer: userAnswer
            });
        }
    });
    
    $.ajax({
        url: API_BASE_URL + 'questions/finish-test',
        method: 'POST',
        contentType: 'application/json',
        headers: {
            'Authorization': 'Bearer ' + localStorage.getItem('token')
        },
        data: JSON.stringify({
            difficulty: currentTest.difficulty,
            answers: answers
        }),
        success: function(response) {
            if (response.success) {
                const result = response.data;
                
                // Mostrar resultados
                Swal.fire({
                    title: result.passed ? '¡Test Aprobado!' : 'Test No Aprobado',
                    html: `
                        <div class="text-center">
                            <h3>Resultados</h3>
                            <p><strong>Puntuación:</strong> ${result.score}%</p>
                            <p><strong>Respuestas correctas:</strong> ${result.correct_answers}/${result.total_questions}</p>
                            <p><strong>Estado:</strong> ${result.passed ? 'Aprobado' : 'No Aprobado'}</p>
                            ${result.lives_lost > 0 ? `<p><strong>Vidas perdidas:</strong> ${result.lives_lost}</p>` : ''}
                        </div>
                    `,
                    icon: result.passed ? 'success' : 'warning',
                    confirmButtonText: 'Ver Historial'
                }).then((result) => {
                    showSection('history');
                    loadHistory();
                });
                
                // Actualizar vidas del usuario
                if (currentUser) {
                    loadUserLives();
                }
            } else {
                Swal.fire('Error', response.message || 'Error al finalizar el test', 'error');
            }
        },
        error: function(xhr) {
            const response = xhr.responseJSON;
            Swal.fire('Error', response?.message || 'Error al finalizar el test', 'error');
        }
    });
    
    // Limpiar test actual
    currentTest = null;
    showSection('tests');
}

function loadHistory() {
    $.ajax({
        url: API_BASE_URL + 'sessions/by-user',
        method: 'GET',
        headers: {
            'Authorization': 'Bearer ' + localStorage.getItem('token')
        },
        success: function(response) {
            if (response.success) {
                const historyContainer = $('#historyContainer');
                historyContainer.empty();
                
                if (response.data.length === 0) {
                    historyContainer.html('<div class="text-center"><p>No hay historial de tests disponible.</p></div>');
                    return;
                }
                
                response.data.forEach(session => {
                    const sessionCard = `
                        <div class="col-md-6 mb-3">
                            <div class="card">
                                <div class="card-body">
                                    <h6 class="card-title">Test #${session.id}</h6>
                                    <p class="card-text">
                                        <small class="text-muted">${new Date(session.completed_at).toLocaleDateString()}</small>
                                        <br>
                                        <span class="badge bg-${session.difficulty === 'easy' ? 'success' : session.difficulty === 'medium' ? 'warning' : 'danger'}">${session.difficulty}</span>
                                    </p>
                                    <div class="d-flex justify-content-between">
                                        <span class="badge bg-${session.passed ? 'success' : 'warning'}">${session.score}%</span>
                                        <span class="text-muted">${session.correct_answers}/${session.question_count}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;
                    historyContainer.append(sessionCard);
                });
            }
        },
        error: function() {
            Swal.fire('Error', 'Error al cargar el historial', 'error');
        }
    });
}

function loadAdminDashboard() {
    console.log('Cargando dashboard del administrador...');
    
    // Mostrar loading
    $('#admin .card-body').addClass('loading');
    
    // Cargar estadísticas del admin
    $.ajax({
        url: API_BASE_URL + 'admin/stats',
        method: 'GET',
        headers: {
            'Authorization': 'Bearer ' + localStorage.getItem('token')
        },
        success: function(response) {
            console.log('Respuesta de estadísticas:', response);
            if (response.success) {
                const stats = response.data;
                $('#adminTotalQuestions').text(stats.total_questions || 0);
                $('#adminTotalUsers').text(stats.unique_users || 0);
                $('#adminTotalTests').text(stats.total_tests || 0);
                $('#adminAverageScore').text((Math.round(stats.average_score || 0)) + '%');
            } else {
                Swal.fire('Error', response.message || 'Error al cargar estadísticas', 'error');
            }
        },
        error: function(xhr) {
            console.error('Error al cargar estadísticas:', xhr);
            const response = xhr.responseJSON;
            Swal.fire('Error', response?.message || 'Error al cargar el dashboard', 'error');
        },
        complete: function() {
            $('#admin .card-body').removeClass('loading');
        }
    });
    
    // Cargar preguntas, usuarios y estadísticas
    loadAdminQuestions();
    loadAdminUsers();
    loadAdminStats();
}

function loadAdminQuestions() {
    console.log('Cargando preguntas del administrador...');
    const questionsContainer = $('#questionsContainer');
    questionsContainer.addClass('loading');
    
    $.ajax({
        url: API_BASE_URL + 'questions',
        method: 'GET',
        headers: {
            'Authorization': 'Bearer ' + localStorage.getItem('token')
        },
        success: function(response) {
            console.log('Respuesta de preguntas:', response);
            if (response.success) {
                questionsContainer.empty();
                
                if (response.data && response.data.length > 0) {
                    response.data.forEach(question => {
                        const questionCard = `
                            <div class="card mb-3 question-admin-card">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div class="flex-grow-1">
                                            <h6 class="card-title">Pregunta #${question.id}</h6>
                                            <p class="card-text">${question.question_text}</p>
                                            <div class="row mt-2">
                                                <div class="col-md-4">
                                                    <small class="text-muted">Respuesta 1: ${question.answer1}</small>
                                                </div>
                                                <div class="col-md-4">
                                                    <small class="text-muted">Respuesta 2: ${question.answer2}</small>
                                                </div>
                                                <div class="col-md-4">
                                                    <small class="text-muted">Respuesta 3: ${question.answer3}</small>
                                                </div>
                                            </div>
                                            <div class="mt-2">
                                                <span class="badge bg-success">Correcta: ${question.correct_answer}</span>
                                                ${question.image_path ? '<span class="badge bg-info ms-1">Con imagen</span>' : ''}
                                            </div>
                                        </div>
                                        <div class="action-buttons">
                                            <button class="btn btn-sm btn-primary edit-question-btn" data-id="${question.id}" title="Editar">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            <button class="btn btn-sm btn-danger delete-question-btn" data-id="${question.id}" title="Eliminar">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        `;
                        questionsContainer.append(questionCard);
                    });
                } else {
                    questionsContainer.html(`
                        <div class="empty-state">
                            <i class="bi bi-question-circle"></i>
                            <h5>No hay preguntas</h5>
                            <p>Agrega la primera pregunta usando el botón de arriba</p>
                        </div>
                    `);
                }
            } else {
                Swal.fire('Error', response.message || 'Error al cargar las preguntas', 'error');
            }
        },
        error: function(xhr) {
            console.error('Error al cargar preguntas:', xhr);
            const response = xhr.responseJSON;
            Swal.fire('Error', response?.message || 'Error al cargar las preguntas', 'error');
            questionsContainer.html(`
                <div class="error-state">
                    <i class="bi bi-exclamation-triangle"></i>
                    <h5>Error al cargar preguntas</h5>
                    <p>No se pudieron cargar las preguntas. Intenta de nuevo.</p>
                </div>
            `);
        },
        complete: function() {
            questionsContainer.removeClass('loading');
        }
    });
}

function loadAdminUsers() {
    console.log('Cargando usuarios del administrador...');
    const usersContainer = $('#usersContainer');
    usersContainer.addClass('loading');
    
    $.ajax({
        url: API_BASE_URL + 'admin/users',
        method: 'GET',
        headers: {
            'Authorization': 'Bearer ' + localStorage.getItem('token')
        },
        success: function(response) {
            console.log('Respuesta de usuarios:', response);
            if (response.success) {
                usersContainer.empty();
                
                if (response.data && response.data.length > 0) {
                    response.data.forEach(user => {
                        const userCard = `
                            <div class="card mb-3 user-admin-card">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div class="flex-grow-1">
                                            <h6 class="card-title">${user.first_name} ${user.last_name}</h6>
                                            <p class="card-text">
                                                <small class="text-muted">${user.email}</small><br>
                                                <span class="badge bg-${user.role === 'admin' ? 'danger' : 'primary'}">${user.role}</span>
                                                <span class="badge bg-warning ms-1">Vidas: ${user.lives || 0}</span>
                                            </p>
                                        </div>
                                        <div class="action-buttons">
                                            <button class="btn btn-sm btn-success add-life-btn" data-id="${user.id}" data-lives="1" title="+1 Vida">
                                                <i class="bi bi-heart-fill"></i> +1
                                            </button>
                                            <button class="btn btn-sm btn-warning add-life-btn" data-id="${user.id}" data-lives="2" title="+2 Vidas">
                                                <i class="bi bi-heart-fill"></i> +2
                                            </button>
                                            <button class="btn btn-sm btn-info add-life-btn" data-id="${user.id}" data-lives="3" title="+3 Vidas">
                                                <i class="bi bi-heart-fill"></i> +3
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        `;
                        usersContainer.append(userCard);
                    });
                } else {
                    usersContainer.html(`
                        <div class="empty-state">
                            <i class="bi bi-people"></i>
                            <h5>No hay usuarios</h5>
                            <p>No se encontraron usuarios registrados</p>
                        </div>
                    `);
                }
            } else {
                Swal.fire('Error', response.message || 'Error al cargar los usuarios', 'error');
            }
        },
        error: function(xhr) {
            console.error('Error al cargar usuarios:', xhr);
            const response = xhr.responseJSON;
            Swal.fire('Error', response?.message || 'Error al cargar los usuarios', 'error');
            usersContainer.html(`
                <div class="error-state">
                    <i class="bi bi-exclamation-triangle"></i>
                    <h5>Error al cargar usuarios</h5>
                    <p>No se pudieron cargar los usuarios. Intenta de nuevo.</p>
                </div>
            `);
        },
        complete: function() {
            usersContainer.removeClass('loading');
        }
    });
}

function addQuestion() {
    // Mostrar loading en el botón
    const submitBtn = $('#addQuestionForm button[type="submit"]');
    const originalText = submitBtn.html();
    submitBtn.html('<i class="bi bi-hourglass-split"></i> Agregando...');
    submitBtn.prop('disabled', true);
    
    const formData = {
        question_text: $('#questionText').val().trim(),
        answer1: $('#questionAnswer1').val().trim(),
        answer2: $('#questionAnswer2').val().trim(),
        answer3: $('#questionAnswer3').val().trim(),
        correct_answer: parseInt($('input[name="correctAnswer"]:checked').val())
    };
    
    // Validaciones mejoradas
    if (!formData.question_text) {
        Swal.fire('Error', 'La pregunta es obligatoria', 'error');
        resetSubmitButton();
        return;
    }
    
    if (!formData.answer1 || !formData.answer2 || !formData.answer3) {
        Swal.fire('Error', 'Todas las respuestas son obligatorias', 'error');
        resetSubmitButton();
        return;
    }
    
    if (!formData.correct_answer || formData.correct_answer < 1 || formData.correct_answer > 3) {
        Swal.fire('Error', 'Debes seleccionar una respuesta correcta', 'error');
        resetSubmitButton();
        return;
    }
    
    // Verificar que las respuestas sean diferentes
    const answers = [formData.answer1, formData.answer2, formData.answer3];
    const uniqueAnswers = [...new Set(answers)];
    if (uniqueAnswers.length !== 3) {
        Swal.fire('Error', 'Las tres respuestas deben ser diferentes', 'error');
        resetSubmitButton();
        return;
    }
    
    $.ajax({
        url: API_BASE_URL + 'questions',
        method: 'POST',
        contentType: 'application/json',
        headers: {
            'Authorization': 'Bearer ' + localStorage.getItem('token')
        },
        data: JSON.stringify(formData),
        success: function(response) {
            if (response.success) {
                $('#addQuestionModal').modal('hide');
                $('#addQuestionForm')[0].reset();
                loadAdminQuestions();
                Swal.fire({
                    icon: 'success',
                    title: '¡Pregunta agregada!',
                    text: 'La pregunta se ha agregado correctamente al sistema',
                    timer: 2000,
                    showConfirmButton: false
                });
            } else {
                Swal.fire('Error', response.message || 'Error al agregar pregunta', 'error');
            }
        },
        error: function(xhr) {
            const response = xhr.responseJSON;
            Swal.fire('Error', response?.message || 'Error al agregar pregunta. Verifica tu conexión.', 'error');
        },
        complete: function() {
            resetSubmitButton();
        }
    });
    
    function resetSubmitButton() {
        submitBtn.html(originalText);
        submitBtn.prop('disabled', false);
    }
}

function loadAdminStats() {
    console.log('Cargando estadísticas detalladas del administrador...');
    const statsContainer = $('#adminStatsContainer');
    statsContainer.addClass('loading');
    
    $.ajax({
        url: API_BASE_URL + 'admin/stats',
        method: 'GET',
        headers: {
            'Authorization': 'Bearer ' + localStorage.getItem('token')
        },
        success: function(response) {
            console.log('Respuesta de estadísticas detalladas:', response);
            if (response.success) {
                const stats = response.data;
                statsContainer.html(`
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-body">
                                    <h6 class="card-title">Estadísticas de Tests</h6>
                                    <ul class="list-unstyled">
                                        <li><strong>Total de tests:</strong> ${stats.total_tests || 0}</li>
                                        <li><strong>Tests aprobados:</strong> ${stats.passed_tests || 0}</li>
                                        <li><strong>Tests fallidos:</strong> ${stats.failed_tests || 0}</li>
                                        <li><strong>Promedio de puntuación:</strong> ${Math.round(stats.average_score || 0)}%</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-body">
                                    <h6 class="card-title">Estadísticas por Dificultad</h6>
                                    <ul class="list-unstyled">
                                        <li><strong>Tests fáciles:</strong> ${stats.easy_tests || 0}</li>
                                        <li><strong>Tests medios:</strong> ${stats.medium_tests || 0}</li>
                                        <li><strong>Tests difíciles:</strong> ${stats.hard_tests || 0}</li>
                                        <li><strong>Usuarios únicos:</strong> ${stats.unique_users || 0}</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                `);
            } else {
                Swal.fire('Error', response.message || 'Error al cargar estadísticas', 'error');
            }
        },
        error: function(xhr) {
            console.error('Error al cargar estadísticas detalladas:', xhr);
            const response = xhr.responseJSON;
            Swal.fire('Error', response?.message || 'Error al cargar estadísticas', 'error');
        },
        complete: function() {
            statsContainer.removeClass('loading');
        }
    });
}

// Event listeners para admin
$(document).on('click', '.add-life-btn', function() {
    const userId = $(this).data('id');
    const lives = $(this).data('lives') || 1;
    const button = $(this);
    
    // Mostrar loading en el botón
    const originalText = button.html();
    button.html('<i class="bi bi-hourglass-split"></i>');
    button.prop('disabled', true);
    
    $.ajax({
        url: API_BASE_URL + 'auth/add-lives',
        method: 'POST',
        contentType: 'application/json',
        headers: {
            'Authorization': 'Bearer ' + localStorage.getItem('token')
        },
        data: JSON.stringify({
            user_id: userId,
            lives: lives
        }),
        success: function(response) {
            if (response.success) {
                loadAdminUsers();
                Swal.fire({
                    icon: 'success',
                    title: '¡Vidas agregadas!',
                    text: `Se agregaron ${lives} vida(s) al usuario`,
                    timer: 2000,
                    showConfirmButton: false
                });
            } else {
                Swal.fire('Error', response.message || 'Error al agregar vidas', 'error');
            }
        },
        error: function(xhr) {
            const response = xhr.responseJSON;
            Swal.fire('Error', response?.message || 'Error al agregar vidas. Verifica tu conexión.', 'error');
        },
        complete: function() {
            button.html(originalText);
            button.prop('disabled', false);
        }
    });
});

$(document).on('click', '.delete-question-btn', function() {
    const questionId = $(this).data('id');
    const button = $(this);
    
    Swal.fire({
        title: '¿Estás seguro?',
        text: 'Esta acción no se puede deshacer y eliminará la pregunta permanentemente',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar',
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            // Mostrar loading en el botón
            const originalText = button.html();
            button.html('<i class="bi bi-hourglass-split"></i>');
            button.prop('disabled', true);
            
            $.ajax({
                url: API_BASE_URL + 'questions/' + questionId,
                method: 'DELETE',
                headers: {
                    'Authorization': 'Bearer ' + localStorage.getItem('token')
                },
                success: function(response) {
                    if (response.success) {
                        loadAdminQuestions();
                        Swal.fire({
                            icon: 'success',
                            title: '¡Pregunta eliminada!',
                            text: 'La pregunta se ha eliminado correctamente',
                            timer: 2000,
                            showConfirmButton: false
                        });
                    } else {
                        Swal.fire('Error', response.message || 'Error al eliminar pregunta', 'error');
                    }
                },
                error: function(xhr) {
                    const response = xhr.responseJSON;
                    Swal.fire('Error', response?.message || 'Error al eliminar pregunta. Verifica tu conexión.', 'error');
                },
                complete: function() {
                    button.html(originalText);
                    button.prop('disabled', false);
                }
            });
        }
    });
});

// Event listener para editar preguntas
$(document).on('click', '.edit-question-btn', function() {
    const questionId = $(this).data('id');
    
    Swal.fire({
        title: 'Función en desarrollo',
        text: 'La edición de preguntas estará disponible próximamente',
        icon: 'info',
        confirmButtonText: 'Entendido'
    });
});

// Event listener para tabs del administrador
$(document).on('click', '#stats-tab', function() {
    loadAdminStats();
});