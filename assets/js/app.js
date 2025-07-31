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
    setupEventListeners();
    checkAuthStatus();
    showSection('home');
    loadPublicStats();
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
    $('.guest-only').hide();
    $('.user-only').show();
    $('.admin-only').toggle(currentUser.role === 'admin');
    
    $('#userMenu').show();
    $('#authButtons').hide();
    
    $('#userName').text(currentUser.first_name + ' ' + currentUser.last_name);
    $('#userRole').text(currentUser.role === 'admin' ? 'Administrador' : 'Usuario');
    $('#userLives').text(currentUser.lives);
    
    // Actualizar vidas en la sección de tests
    $('#currentLives').text(currentUser.lives);
}

function updateUIForGuest() {
    $('.guest-only').show();
    $('.user-only').hide();
    $('.admin-only').hide();
    
    $('#userMenu').hide();
    $('#authButtons').show();
    
    currentUser = null;
    
    if (lifeRegenerationTimer) {
        clearInterval(lifeRegenerationTimer);
        lifeRegenerationTimer = null;
    }
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
    // Cargar estadísticas del admin
    $.ajax({
        url: API_BASE_URL + 'admin/stats',
        method: 'GET',
        headers: {
            'Authorization': 'Bearer ' + localStorage.getItem('token')
        },
        success: function(response) {
            if (response.success) {
                const stats = response.data;
                $('#adminTotalQuestions').text(stats.total_questions || 0);
                $('#adminTotalUsers').text(stats.total_users || 0);
                $('#adminTotalTests').text(stats.total_tests || 0);
                $('#adminAverageScore').text((stats.average_score || 0) + '%');
            }
        },
        error: function() {
            Swal.fire('Error', 'Error al cargar el dashboard', 'error');
        }
    });
    
    // Cargar preguntas
    loadAdminQuestions();
    loadAdminUsers();
}

function loadAdminQuestions() {
    $.ajax({
        url: API_BASE_URL + 'questions',
        method: 'GET',
        headers: {
            'Authorization': 'Bearer ' + localStorage.getItem('token')
        },
        success: function(response) {
            if (response.success) {
                const questionsContainer = $('#questionsContainer');
                questionsContainer.empty();
                
                response.data.questions.forEach(question => {
                    const questionCard = `
                        <div class="card mb-3">
                            <div class="card-body">
                                <h6 class="card-title">Pregunta #${question.id}</h6>
                                <p class="card-text">${question.question_text}</p>
                                <div class="row">
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
                                <div class="mt-2">
                                    <button class="btn btn-sm btn-primary edit-question-btn" data-id="${question.id}">Editar</button>
                                    <button class="btn btn-sm btn-danger delete-question-btn" data-id="${question.id}">Eliminar</button>
                                </div>
                            </div>
                        </div>
                    `;
                    questionsContainer.append(questionCard);
                });
            }
        },
        error: function() {
            Swal.fire('Error', 'Error al cargar las preguntas', 'error');
        }
    });
}

function loadAdminUsers() {
    $.ajax({
        url: API_BASE_URL + 'admin/users',
        method: 'GET',
        headers: {
            'Authorization': 'Bearer ' + localStorage.getItem('token')
        },
        success: function(response) {
            if (response.success) {
                const usersContainer = $('#usersContainer');
                usersContainer.empty();
                
                response.data.forEach(user => {
                    const userCard = `
                        <div class="card mb-3">
                            <div class="card-body">
                                <h6 class="card-title">${user.first_name} ${user.last_name}</h6>
                                <p class="card-text">
                                    <small class="text-muted">${user.email}</small><br>
                                    <span class="badge bg-${user.role === 'admin' ? 'danger' : 'primary'}">${user.role}</span>
                                    <span class="badge bg-warning ms-1">Vidas: ${user.lives}</span>
                                </p>
                                <div class="mt-2">
                                    <button class="btn btn-sm btn-success add-life-btn" data-id="${user.id}">+1 Vida</button>
                                    <button class="btn btn-sm btn-warning add-life-btn" data-id="${user.id}" data-lives="2">+2 Vidas</button>
                                    <button class="btn btn-sm btn-info add-life-btn" data-id="${user.id}" data-lives="3">+3 Vidas</button>
                                </div>
                            </div>
                        </div>
                    `;
                    usersContainer.append(userCard);
                });
            }
        },
        error: function() {
            Swal.fire('Error', 'Error al cargar los usuarios', 'error');
        }
    });
}

function addQuestion() {
    const formData = {
        question_text: $('#questionText').val(),
        answer1: $('#questionAnswer1').val(),
        answer2: $('#questionAnswer2').val(),
        answer3: $('#questionAnswer3').val(),
        correct_answer: parseInt($('input[name="correctAnswer"]:checked').val())
    };
    
    // Validaciones
    if (!formData.question_text || !formData.answer1 || !formData.answer2 || !formData.answer3 || !formData.correct_answer) {
        Swal.fire('Error', 'Por favor completa todos los campos', 'error');
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
                Swal.fire('Éxito', 'Pregunta agregada correctamente', 'success');
            } else {
                Swal.fire('Error', response.message || 'Error al agregar pregunta', 'error');
            }
        },
        error: function(xhr) {
            const response = xhr.responseJSON;
            Swal.fire('Error', response?.message || 'Error al agregar pregunta', 'error');
        }
    });
}

// Event listeners para admin
$(document).on('click', '.add-life-btn', function() {
    const userId = $(this).data('id');
    const lives = $(this).data('lives') || 1;
    
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
                Swal.fire('Éxito', 'Vidas agregadas correctamente', 'success');
            } else {
                Swal.fire('Error', response.message || 'Error al agregar vidas', 'error');
            }
        },
        error: function(xhr) {
            const response = xhr.responseJSON;
            Swal.fire('Error', response?.message || 'Error al agregar vidas', 'error');
        }
    });
});

$(document).on('click', '.delete-question-btn', function() {
    const questionId = $(this).data('id');
    
    Swal.fire({
        title: '¿Estás seguro?',
        text: 'Esta acción no se puede deshacer',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: API_BASE_URL + 'questions/' + questionId,
                method: 'DELETE',
                headers: {
                    'Authorization': 'Bearer ' + localStorage.getItem('token')
                },
                success: function(response) {
                    if (response.success) {
                        loadAdminQuestions();
                        Swal.fire('Eliminado', 'Pregunta eliminada correctamente', 'success');
                    } else {
                        Swal.fire('Error', response.message || 'Error al eliminar pregunta', 'error');
                    }
                },
                error: function(xhr) {
                    const response = xhr.responseJSON;
                    Swal.fire('Error', response?.message || 'Error al eliminar pregunta', 'error');
                }
            });
        }
    });
});