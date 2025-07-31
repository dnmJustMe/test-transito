// Configuración global
const API_BASE_URL = 'http://localhost/test-transito/api/';
let currentUser = null;
let currentTest = null;
let testTimer = null;
let testStartTime = null;

// Inicialización de la aplicación
$(document).ready(function() {
    checkAuthStatus();
    setupEventListeners();
    loadCategories();
});

// Configuración de SweetAlert2
const Toast = Swal.mixin({
    toast: true,
    position: 'top-end',
    showConfirmButton: false,
    timer: 3000,
    timerProgressBar: true
});

// Event listeners
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

    // Formularios
    $('#loginForm').on('submit', function(e) {
        e.preventDefault();
        login();
    });

    $('#registerForm').on('submit', function(e) {
        e.preventDefault();
        register();
    });

    // Validaciones en tiempo real
    $('input, select, textarea').on('input', function() {
        validateField($(this));
    });
}

// Funciones de autenticación
function checkAuthStatus() {
    const token = localStorage.getItem('auth_token');
    if (token) {
        // Verificar token con el servidor
        $.ajax({
            url: API_BASE_URL + 'auth/profile',
            method: 'GET',
            headers: {
                'Authorization': 'Bearer ' + token
            },
            success: function(response) {
                if (response.success) {
                    currentUser = response.user;
                    updateUIForAuthenticatedUser();
                } else {
                    logout();
                }
            },
            error: function() {
                logout();
            }
        });
    } else {
        updateUIForUnauthenticatedUser();
    }
}

function showLoginModal() {
    $('#loginModal').modal('show');
}

function showRegisterModal() {
    $('#registerModal').modal('show');
}

function login() {
    const email = $('#loginEmail').val();
    const password = $('#loginPassword').val();

    if (!validateForm('#loginForm')) {
        return;
    }

    showLoading('Iniciando sesión...');

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
                localStorage.setItem('auth_token', response.token);
                currentUser = response.user;
                updateUIForAuthenticatedUser();
                $('#loginModal').modal('hide');
                Toast.fire({
                    icon: 'success',
                    title: 'Sesión iniciada exitosamente'
                });
            } else {
                showError('Error en la autenticación');
            }
        },
        error: function(xhr) {
            const error = xhr.responseJSON?.error || 'Error al iniciar sesión';
            showError(error);
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

    if (!validateForm('#registerForm')) {
        return;
    }

    showLoading('Registrando usuario...');

    $.ajax({
        url: API_BASE_URL + 'auth/register',
        method: 'POST',
        contentType: 'application/json',
        data: JSON.stringify(formData),
        success: function(response) {
            if (response.success) {
                localStorage.setItem('auth_token', response.token);
                currentUser = response.user;
                updateUIForAuthenticatedUser();
                $('#registerModal').modal('hide');
                Toast.fire({
                    icon: 'success',
                    title: 'Usuario registrado exitosamente'
                });
            } else {
                showError('Error en el registro');
            }
        },
        error: function(xhr) {
            const error = xhr.responseJSON?.error || 'Error al registrar usuario';
            showError(error);
        }
    });
}

function logout() {
    localStorage.removeItem('auth_token');
    currentUser = null;
    updateUIForUnauthenticatedUser();
    showSection('home');
    Toast.fire({
        icon: 'info',
        title: 'Sesión cerrada'
    });
}

function updateUIForAuthenticatedUser() {
    $('#auth-buttons').hide();
    $('#user-menu').show();
    $('#user-name').text(currentUser.first_name + ' ' + currentUser.last_name);
    
    if (currentUser.role === 'admin') {
        $('.admin-only').show();
    } else {
        $('.admin-only').hide();
    }
}

function updateUIForUnauthenticatedUser() {
    $('#auth-buttons').show();
    $('#user-menu').hide();
    $('.admin-only').hide();
}

// Funciones de navegación
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
                loadCategories();
                break;
            case 'history':
                loadHistory();
                break;
            case 'admin':
                if (currentUser && currentUser.role === 'admin') {
                    loadAdminDashboard();
                } else {
                    showSection('home');
                }
                break;
        }
    } else {
        // Si la sección no existe, mostrar home
        showSection('home');
    }
}

// Funciones de categorías y tests
function loadCategories() {
    $.ajax({
        url: API_BASE_URL + 'categories/with-count',
        method: 'GET',
        success: function(response) {
            if (response.success) {
                displayCategories(response.data);
            }
        },
        error: function() {
            showError('Error al cargar categorías');
        }
    });
}

function displayCategories(categories) {
    const container = $('#categories-container');
    container.empty();
    
    categories.forEach(function(category) {
        const card = `
            <div class="col-md-6 col-lg-4 mb-4">
                <div class="category-card animate__animated animate__fadeInUp" onclick="startTestByCategory(${category.id})">
                    <div class="category-icon">
                        <i class="bi bi-question-circle"></i>
                    </div>
                    <div class="category-title">${category.name}</div>
                    <div class="category-description">${category.description || 'Sin descripción'}</div>
                    <div class="category-stats">
                        <i class="bi bi-collection"></i> ${category.question_count || 0} preguntas
                    </div>
                </div>
            </div>
        `;
        container.append(card);
    });
}

function startTest() {
    if (!currentUser) {
        showLoginModal();
        return;
    }
    showSection('tests');
}

function startTestByCategory(categoryId) {
    if (!currentUser) {
        showLoginModal();
        return;
    }

    Swal.fire({
        title: '¿Comenzar test?',
        text: 'Selecciona el número de preguntas para tu test',
        icon: 'question',
        input: 'select',
        inputOptions: {
            10: '10 preguntas',
            20: '20 preguntas',
            30: '30 preguntas'
        },
        inputPlaceholder: 'Selecciona...',
        showCancelButton: true,
        confirmButtonText: 'Comenzar',
        cancelButtonText: 'Cancelar',
        inputValidator: (value) => {
            if (!value) {
                return 'Debes seleccionar un número de preguntas';
            }
        }
    }).then((result) => {
        if (result.isConfirmed) {
            initializeTest(categoryId, result.value);
        }
    });
}

function initializeTest(categoryId, questionCount) {
    showLoading('Preparando test...');

    $.ajax({
        url: API_BASE_URL + 'questions/start-test',
        method: 'POST',
        headers: {
            'Authorization': 'Bearer ' + localStorage.getItem('auth_token')
        },
        contentType: 'application/json',
        data: JSON.stringify({
            category_id: categoryId,
            limit: questionCount
        }),
        success: function(response) {
            if (response.success) {
                currentTest = {
                    sessionId: response.session_id,
                    questions: response.questions,
                    currentQuestion: 0,
                    answers: {},
                    startTime: Date.now()
                };
                
                startTestTimer();
                showTestInterface();
                displayQuestion(0);
            } else {
                showError('Error al iniciar el test');
            }
        },
        error: function() {
            showError('Error al iniciar el test');
        }
    });
}

function showTestInterface() {
    showSection('test-interface');
    updateQuestionCounter();
}

function displayQuestion(questionIndex) {
    if (!currentTest || questionIndex >= currentTest.questions.length) {
        return;
    }

    const question = currentTest.questions[questionIndex];
    const container = $('#question-container');
    
    let imageHtml = '';
    if (question.image_path) {
        imageHtml = `
            <div class="text-center mb-3">
                <img src="assets/img/questions/${question.image_path}" 
                     alt="Imagen de la pregunta" 
                     class="question-image img-fluid">
            </div>
        `;
    }

    const options = [
        { letter: 'A', text: question.option_a },
        { letter: 'B', text: question.option_b },
        { letter: 'C', text: question.option_c }
    ];

    let optionsHtml = '';
    options.forEach((option, index) => {
        const isSelected = currentTest.answers[questionIndex] === option.letter;
        const selectedClass = isSelected ? 'selected' : '';
        
        optionsHtml += `
            <div class="option-item ${selectedClass}" onclick="selectAnswer(${questionIndex}, '${option.letter}')">
                <div class="option-letter">${option.letter}</div>
                <div class="option-text">${option.text}</div>
            </div>
        `;
    });

    container.html(`
        <div class="question-text">
            <strong>Pregunta ${questionIndex + 1}:</strong> ${question.question_text}
        </div>
        ${imageHtml}
        <div class="options-container">
            ${optionsHtml}
        </div>
    `);

    currentTest.currentQuestion = questionIndex;
    updateQuestionCounter();
    updateNavigationButtons();
}

function selectAnswer(questionIndex, answer) {
    if (!currentTest) return;

    currentTest.answers[questionIndex] = answer;
    
    // Actualizar UI
    $('.option-item').removeClass('selected');
    $(`.option-item`).eq(['A', 'B', 'C'].indexOf(answer)).addClass('selected');
    
    // Enviar respuesta al servidor
    submitAnswerToServer(questionIndex, answer);
}

function submitAnswerToServer(questionIndex, answer) {
    const timeSpent = Math.floor((Date.now() - currentTest.startTime) / 1000);
    
    $.ajax({
        url: API_BASE_URL + 'questions/submit-answer',
        method: 'POST',
        headers: {
            'Authorization': 'Bearer ' + localStorage.getItem('auth_token')
        },
        contentType: 'application/json',
        data: JSON.stringify({
            session_id: currentTest.sessionId,
            question_id: currentTest.questions[questionIndex].id,
            answer: answer,
            time_spent: timeSpent
        }),
        success: function(response) {
            if (response.success) {
                // Mostrar feedback visual
                if (response.is_correct) {
                    Toast.fire({
                        icon: 'success',
                        title: '¡Correcto!'
                    });
                } else {
                    Toast.fire({
                        icon: 'error',
                        title: 'Incorrecto'
                    });
                }
            }
        },
        error: function() {
            // Silenciar errores para no interrumpir el test
        }
    });
}

function nextQuestion() {
    if (!currentTest) return;
    
    if (currentTest.currentQuestion < currentTest.questions.length - 1) {
        displayQuestion(currentTest.currentQuestion + 1);
    } else {
        finishTest();
    }
}

function previousQuestion() {
    if (!currentTest || currentTest.currentQuestion > 0) {
        displayQuestion(currentTest.currentQuestion - 1);
    }
}

function updateQuestionCounter() {
    if (!currentTest) return;
    
    $('#question-counter').text(
        `${currentTest.currentQuestion + 1}/${currentTest.questions.length}`
    );
}

function updateNavigationButtons() {
    const prevBtn = $('.btn-secondary');
    const nextBtn = $('.btn-primary');
    
    if (currentTest.currentQuestion === 0) {
        prevBtn.prop('disabled', true);
    } else {
        prevBtn.prop('disabled', false);
    }
    
    if (currentTest.currentQuestion === currentTest.questions.length - 1) {
        nextBtn.html('Finalizar <i class="bi bi-check-circle"></i>');
    } else {
        nextBtn.html('Siguiente <i class="bi bi-arrow-right"></i>');
    }
}

function startTestTimer() {
    testStartTime = Date.now();
    testTimer = setInterval(updateTimer, 1000);
}

function updateTimer() {
    if (!testStartTime) return;
    
    const elapsed = Math.floor((Date.now() - testStartTime) / 1000);
    const minutes = Math.floor(elapsed / 60);
    const seconds = elapsed % 60;
    
    $('#timer').text(
        `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`
    );
    
    // Advertencia cuando quedan menos de 5 minutos
    if (elapsed >= 900) { // 15 minutos
        $('#timer').addClass('timer-warning');
    }
}

function finishTest() {
    if (!currentTest) return;
    
    Swal.fire({
        title: '¿Finalizar test?',
        text: '¿Estás seguro de que quieres finalizar el test?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Sí, finalizar',
        cancelButtonText: 'Continuar',
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            submitTestResults();
        }
    });
}

function submitTestResults() {
    showLoading('Procesando resultados...');
    
    $.ajax({
        url: API_BASE_URL + 'questions/finish-test/' + currentTest.sessionId,
        method: 'POST',
        headers: {
            'Authorization': 'Bearer ' + localStorage.getItem('auth_token')
        },
        success: function(response) {
            if (response.success) {
                clearInterval(testTimer);
                showTestResults(response);
            } else {
                showError('Error al finalizar el test');
            }
        },
        error: function() {
            showError('Error al finalizar el test');
        }
    });
}

function showTestResults(results) {
    const passed = results.passed;
    const score = results.score;
    
    Swal.fire({
        title: passed ? '¡Felicitaciones!' : 'Test completado',
        html: `
            <div class="text-center">
                <div class="mb-3">
                    <h2 class="text-${passed ? 'success' : 'danger'}">${score}%</h2>
                    <p>Puntuación obtenida</p>
                </div>
                <div class="row text-center">
                    <div class="col-6">
                        <h4>${results.correct_answers}</h4>
                        <small>Correctas</small>
                    </div>
                    <div class="col-6">
                        <h4>${results.total_questions}</h4>
                        <small>Total</small>
                    </div>
                </div>
                <div class="mt-3">
                    <div class="progress">
                        <div class="progress-bar" style="width: ${score}%"></div>
                    </div>
                </div>
                <p class="mt-3">
                    ${passed ? 
                        '<span class="badge bg-success">APROBADO</span>' : 
                        '<span class="badge bg-danger">REPROBADO</span>'
                    }
                </p>
            </div>
        `,
        icon: passed ? 'success' : 'info',
        confirmButtonText: 'Ver Historial',
        showCancelButton: true,
        cancelButtonText: 'Nuevo Test'
    }).then((result) => {
        if (result.isConfirmed) {
            showSection('history');
        } else {
            showSection('tests');
        }
    });
    
    currentTest = null;
}

// Funciones de historial
function loadHistory() {
    if (!currentUser) {
        showSection('home');
        return;
    }
    
    $.ajax({
        url: API_BASE_URL + 'sessions/',
        method: 'GET',
        headers: {
            'Authorization': 'Bearer ' + localStorage.getItem('auth_token')
        },
        success: function(response) {
            if (response.success) {
                displayHistory(response.data);
            }
        },
        error: function() {
            showError('Error al cargar historial');
        }
    });
}

function displayHistory(sessions) {
    const tbody = $('#history-table');
    tbody.empty();
    
    if (sessions.length === 0) {
        tbody.html(`
            <tr>
                <td colspan="5" class="text-center text-muted">
                    <i class="bi bi-inbox"></i> No hay tests realizados
                </td>
            </tr>
        `);
        return;
    }
    
    sessions.forEach(function(session) {
        const date = new Date(session.created_at);
        const status = getStatusBadge(session.status);
        const score = session.score || 0;
        
        const row = `
            <tr>
                <td>${date.toLocaleDateString()} ${date.toLocaleTimeString()}</td>
                <td>${session.test_name || 'Test General'}</td>
                <td>
                    <span class="badge bg-${score >= 70 ? 'success' : 'danger'}">
                        ${score}%
                    </span>
                </td>
                <td>${status}</td>
                <td>
                    <button class="btn btn-sm btn-outline-primary" onclick="viewSessionDetails(${session.id})">
                        <i class="bi bi-eye"></i> Ver
                    </button>
                </td>
            </tr>
        `;
        tbody.append(row);
    });
}

function getStatusBadge(status) {
    const badges = {
        'completed': '<span class="badge bg-success">Completado</span>',
        'in_progress': '<span class="badge bg-warning">En Progreso</span>',
        'abandoned': '<span class="badge bg-danger">Abandonado</span>'
    };
    return badges[status] || '<span class="badge bg-secondary">Desconocido</span>';
}

function viewSessionDetails(sessionId) {
    $.ajax({
        url: API_BASE_URL + 'sessions/' + sessionId,
        method: 'GET',
        headers: {
            'Authorization': 'Bearer ' + localStorage.getItem('auth_token')
        },
        success: function(response) {
            if (response.success) {
                showSessionDetails(response.data);
            }
        },
        error: function() {
            showError('Error al cargar detalles de la sesión');
        }
    });
}

function showSessionDetails(data) {
    const session = data.session;
    const answers = data.answers;
    
    let answersHtml = '';
    answers.forEach(function(answer, index) {
        const isCorrect = answer.is_correct;
        const userAnswer = answer.user_answer;
        const correctAnswer = answer.correct_answer;
        
        answersHtml += `
            <div class="card mb-3">
                <div class="card-body">
                    <h6>Pregunta ${index + 1}</h6>
                    <p>${answer.question_text}</p>
                    <div class="mb-2">
                        <strong>Tu respuesta:</strong> 
                        <span class="badge bg-${isCorrect ? 'success' : 'danger'}">${userAnswer}</span>
                    </div>
                    ${!isCorrect ? `
                        <div class="mb-2">
                            <strong>Respuesta correcta:</strong> 
                            <span class="badge bg-success">${correctAnswer}</span>
                        </div>
                    ` : ''}
                    ${answer.explanation ? `
                        <div class="alert alert-info">
                            <strong>Explicación:</strong> ${answer.explanation}
                        </div>
                    ` : ''}
                </div>
            </div>
        `;
    });
    
    Swal.fire({
        title: `Detalles del Test - ${session.test_name}`,
        html: `
            <div class="text-start">
                <div class="row mb-3">
                    <div class="col-6">
                        <strong>Puntuación:</strong> ${session.score}%
                    </div>
                    <div class="col-6">
                        <strong>Estado:</strong> ${getStatusBadge(session.status)}
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-6">
                        <strong>Correctas:</strong> ${session.correct_answers}
                    </div>
                    <div class="col-6">
                        <strong>Total:</strong> ${session.total_questions}
                    </div>
                </div>
                <hr>
                <h6>Revisión de respuestas:</h6>
                <div style="max-height: 400px; overflow-y: auto;">
                    ${answersHtml}
                </div>
            </div>
        `,
        width: '800px',
        confirmButtonText: 'Cerrar'
    });
}

// Funciones de administración
function loadAdminDashboard() {
    if (!currentUser || currentUser.role !== 'admin') {
        showSection('home');
        return;
    }
    
    const content = `
        <div class="row">
            <div class="col-md-3 mb-4">
                <div class="stats-card">
                    <div class="stats-number" id="total-questions">-</div>
                    <div class="stats-label">Preguntas</div>
                </div>
            </div>
            <div class="col-md-3 mb-4">
                <div class="stats-card">
                    <div class="stats-number" id="total-categories">-</div>
                    <div class="stats-label">Categorías</div>
                </div>
            </div>
            <div class="col-md-3 mb-4">
                <div class="stats-card">
                    <div class="stats-number" id="total-users">-</div>
                    <div class="stats-label">Usuarios</div>
                </div>
            </div>
            <div class="col-md-3 mb-4">
                <div class="stats-card">
                    <div class="stats-number" id="total-tests">-</div>
                    <div class="stats-label">Tests</div>
                </div>
            </div>
        </div>
    `;
    
    $('#admin-content').html(content);
    loadAdminStats();
}

function loadAdminStats() {
    // Cargar estadísticas básicas
    // Esta función se puede expandir para cargar datos reales
    $('#total-questions').text('0');
    $('#total-categories').text('0');
    $('#total-users').text('0');
    $('#total-tests').text('0');
}

function showAddQuestionModal() {
    loadCategoriesForSelect();
    $('#addQuestionModal').modal('show');
}

function loadCategoriesForSelect() {
    $.ajax({
        url: API_BASE_URL + 'categories/',
        method: 'GET',
        success: function(response) {
            if (response.success) {
                const select = $('#questionCategory');
                select.empty();
                select.append('<option value="">Seleccionar categoría...</option>');
                
                response.data.forEach(function(category) {
                    select.append(`<option value="${category.id}">${category.name}</option>`);
                });
            }
        }
    });
}

function addQuestion() {
    const formData = {
        category_id: $('#questionCategory').val(),
        question_text: $('#questionText').val(),
        option_a: $('#optionA').val(),
        option_b: $('#optionB').val(),
        option_c: $('#optionC').val(),
        correct_answer: $('#correctAnswer').val(),
        explanation: $('#questionExplanation').val()
    };
    
    if (!validateForm('#addQuestionForm')) {
        return;
    }
    
    showLoading('Agregando pregunta...');
    
    $.ajax({
        url: API_BASE_URL + 'questions/',
        method: 'POST',
        headers: {
            'Authorization': 'Bearer ' + localStorage.getItem('auth_token')
        },
        contentType: 'application/json',
        data: JSON.stringify(formData),
        success: function(response) {
            if (response.success) {
                $('#addQuestionModal').modal('hide');
                Toast.fire({
                    icon: 'success',
                    title: 'Pregunta agregada exitosamente'
                });
                clearForm('#addQuestionForm');
            } else {
                showError('Error al agregar pregunta');
            }
        },
        error: function() {
            showError('Error al agregar pregunta');
        }
    });
}

function showAddCategoryModal() {
    // Implementar modal para agregar categorías
    Swal.fire({
        title: 'Agregar Categoría',
        html: `
            <input id="categoryName" class="swal2-input" placeholder="Nombre de la categoría">
            <textarea id="categoryDescription" class="swal2-textarea" placeholder="Descripción (opcional)"></textarea>
        `,
        showCancelButton: true,
        confirmButtonText: 'Agregar',
        cancelButtonText: 'Cancelar',
        preConfirm: () => {
            const name = document.getElementById('categoryName').value;
            const description = document.getElementById('categoryDescription').value;
            
            if (!name) {
                Swal.showValidationMessage('El nombre es requerido');
                return false;
            }
            
            return { name, description };
        }
    }).then((result) => {
        if (result.isConfirmed) {
            addCategory(result.value);
        }
    });
}

function addCategory(data) {
    $.ajax({
        url: API_BASE_URL + 'categories/',
        method: 'POST',
        headers: {
            'Authorization': 'Bearer ' + localStorage.getItem('auth_token')
        },
        contentType: 'application/json',
        data: JSON.stringify(data),
        success: function(response) {
            if (response.success) {
                Toast.fire({
                    icon: 'success',
                    title: 'Categoría agregada exitosamente'
                });
                loadCategories();
            } else {
                showError('Error al agregar categoría');
            }
        },
        error: function() {
            showError('Error al agregar categoría');
        }
    });
}

// Funciones de utilidad
function validateForm(formSelector) {
    let isValid = true;
    $(formSelector + ' [required]').each(function() {
        if (!validateField($(this))) {
            isValid = false;
        }
    });
    return isValid;
}

function validateField(field) {
    const value = field.val();
    const type = field.attr('type');
    const name = field.attr('name') || field.attr('id');
    
    // Remover clases de error previas
    field.removeClass('is-invalid');
    field.siblings('.invalid-feedback').remove();
    
    let isValid = true;
    let errorMessage = '';
    
    // Validaciones específicas
    if (type === 'email' && value) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(value)) {
            isValid = false;
            errorMessage = 'Email inválido';
        }
    }
    
    if (type === 'password' && value) {
        if (value.length < 6) {
            isValid = false;
            errorMessage = 'La contraseña debe tener al menos 6 caracteres';
        }
    }
    
    if (field.prop('required') && !value) {
        isValid = false;
        errorMessage = 'Este campo es requerido';
    }
    
    // Mostrar error si es inválido
    if (!isValid) {
        field.addClass('is-invalid');
        field.after(`<div class="invalid-feedback">${errorMessage}</div>`);
    }
    
    return isValid;
}

function clearForm(formSelector) {
    $(formSelector + ' input, ' + formSelector + ' textarea, ' + formSelector + ' select').val('');
    $(formSelector + ' .is-invalid').removeClass('is-invalid');
    $(formSelector + ' .invalid-feedback').remove();
}

function showLoading(message) {
    Swal.fire({
        title: message,
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });
}

function showError(message) {
    Swal.fire({
        icon: 'error',
        title: 'Error',
        text: message
    });
}

function showStats() {
    if (!currentUser) {
        showLoginModal();
        return;
    }
    
    $.ajax({
        url: API_BASE_URL + 'sessions/stats',
        method: 'GET',
        headers: {
            'Authorization': 'Bearer ' + localStorage.getItem('auth_token')
        },
        success: function(response) {
            if (response.success) {
                showUserStats(response.data);
            }
        },
        error: function() {
            showError('Error al cargar estadísticas');
        }
    });
}

function showUserStats(stats) {
    const testStats = stats.test_stats;
    const answerStats = stats.answer_stats;
    
    Swal.fire({
        title: 'Mis Estadísticas',
        html: `
            <div class="row text-center">
                <div class="col-6 mb-3">
                    <div class="stats-card">
                        <div class="stats-number">${testStats.total_tests || 0}</div>
                        <div class="stats-label">Tests Completados</div>
                    </div>
                </div>
                <div class="col-6 mb-3">
                    <div class="stats-card">
                        <div class="stats-number">${Math.round(testStats.average_score || 0)}%</div>
                        <div class="stats-label">Promedio</div>
                    </div>
                </div>
                <div class="col-6 mb-3">
                    <div class="stats-card">
                        <div class="stats-number">${testStats.best_score || 0}%</div>
                        <div class="stats-label">Mejor Puntuación</div>
                    </div>
                </div>
                <div class="col-6 mb-3">
                    <div class="stats-card">
                        <div class="stats-number">${testStats.passed_tests || 0}</div>
                        <div class="stats-label">Tests Aprobados</div>
                    </div>
                </div>
            </div>
        `,
        width: '600px',
        confirmButtonText: 'Cerrar'
    });
}