// Configuración global
const API_BASE_URL = 'http://localhost/test-transito/api/';
let currentUser = null;
let currentTest = null;
let testTimer = null;

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

    // Botones de acción
    $('#startTestBtn').on('click', function() {
        startTest();
    });

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
}

function updateUIForGuest() {
    $('.guest-only').show();
    $('.user-only').hide();
    $('.admin-only').hide();
    
    $('#userMenu').hide();
    $('#authButtons').show();
    
    currentUser = null;
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
                    loadCategories();
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
            case 'admin':
                if (currentUser && currentUser.role === 'admin') {
                    loadAdminDashboard();
                } else {
                    showSection('home');
                    Swal.fire('Acceso Denegado', 'Solo los administradores pueden acceder a esta sección', 'error');
                }
                break;
            case 'home':
                loadHomeStats();
                break;
        }
    } else {
        // Si la sección no existe, mostrar home
        showSection('home');
    }
}

function loadHomeStats() {
    // Cargar estadísticas públicas
    $.ajax({
        url: API_BASE_URL + 'categories/with-count',
        method: 'GET',
        success: function(response) {
            if (response.success) {
                let totalQuestions = 0;
                response.data.forEach(category => {
                    totalQuestions += parseInt(category.question_count || 0);
                });
                
                $('#totalCategories').text(response.data.length);
                $('#totalQuestions').text(totalQuestions);
                $('#totalUsers').text('100+'); // Placeholder
            }
        },
        error: function() {
            $('#totalCategories').text('20');
            $('#totalQuestions').text('100');
            $('#totalUsers').text('100+');
        }
    });
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
                showSection('home');
                
                Swal.fire('¡Bienvenido!', 'Has iniciado sesión correctamente', 'success');
            } else {
                Swal.fire('Error', response.error || 'Error al iniciar sesión', 'error');
            }
        },
        error: function(xhr) {
            const response = xhr.responseJSON;
            Swal.fire('Error', response?.error || 'Error al iniciar sesión', 'error');
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
                Swal.fire('Error', response.error || 'Error al registrar usuario', 'error');
            }
        },
        error: function(xhr) {
            const response = xhr.responseJSON;
            Swal.fire('Error', response?.error || 'Error al registrar usuario', 'error');
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

function loadCategories() {
    $.ajax({
        url: API_BASE_URL + 'categories/with-count',
        method: 'GET',
        success: function(response) {
            if (response.success) {
                const categoriesContainer = $('#categoriesContainer');
                categoriesContainer.empty();
                
                response.data.forEach(category => {
                    const categoryCard = `
                        <div class="col-md-6 col-lg-4 mb-4">
                            <div class="card h-100 category-card" data-category-id="${category.id}">
                                <div class="card-body">
                                    <h5 class="card-title">${category.name}</h5>
                                    <p class="card-text">${category.description || ''}</p>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="badge bg-primary">${category.question_count || 0} preguntas</span>
                                        <button class="btn btn-success btn-sm start-test-btn" data-category-id="${category.id}">
                                            Iniciar Test
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;
                    categoriesContainer.append(categoryCard);
                });
                
                // Event listeners para botones de test
                $('.start-test-btn').on('click', function() {
                    const categoryId = $(this).data('category-id');
                    startTest(categoryId);
                });
            }
        },
        error: function() {
            Swal.fire('Error', 'Error al cargar las categorías', 'error');
        }
    });
}

function startTest(categoryId = null) {
    const testData = {
        category_id: categoryId,
        question_count: 20
    };
    
    $.ajax({
        url: API_BASE_URL + 'questions/start-test',
        method: 'POST',
        contentType: 'application/json',
        headers: {
            'Authorization': 'Bearer ' + localStorage.getItem('token')
        },
        data: JSON.stringify(testData),
        success: function(response) {
            if (response.success) {
                currentTest = {
                    questions: response.data.questions,
                    currentQuestion: 0,
                    answers: {},
                    startTime: Date.now()
                };
                
                showTestInterface();
                displayQuestion(0);
                startTimer();
            } else {
                Swal.fire('Error', response.error || 'Error al iniciar el test', 'error');
            }
        },
        error: function(xhr) {
            const response = xhr.responseJSON;
            Swal.fire('Error', response?.error || 'Error al iniciar el test', 'error');
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
    $('#answer1').text(question.answer1);
    $('#answer2').text(question.answer2);
    $('#answer3').text(question.answer3);
    
    // Reset radio buttons
    $('input[name="answer"]').prop('checked', false);
    
    // Check previously selected answer
    if (isAnswered) {
        $(`input[name="answer"][value="${currentTest.answers[questionIndex]}"]`).prop('checked', true);
    }
    
    // Show/hide image if exists
    if (question.image_url) {
        $('#questionImage').attr('src', question.image_url).show();
    } else {
        $('#questionImage').hide();
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
    
    // Calcular resultados
    let correctAnswers = 0;
    const totalQuestions = currentTest.questions.length;
    
    currentTest.questions.forEach((question, index) => {
        const userAnswer = currentTest.answers[index];
        if (userAnswer === question.correct_answer) {
            correctAnswers++;
        }
    });
    
    const score = Math.round((correctAnswers / totalQuestions) * 100);
    const timeTaken = Math.floor((Date.now() - currentTest.startTime) / 1000);
    
    // Mostrar resultados
    Swal.fire({
        title: 'Test Completado',
        html: `
            <div class="text-center">
                <h3>Resultados</h3>
                <p><strong>Puntuación:</strong> ${score}%</p>
                <p><strong>Respuestas correctas:</strong> ${correctAnswers}/${totalQuestions}</p>
                <p><strong>Tiempo utilizado:</strong> ${Math.floor(timeTaken / 60)}:${(timeTaken % 60).toString().padStart(2, '0')}</p>
            </div>
        `,
        icon: score >= 70 ? 'success' : 'warning',
        confirmButtonText: 'Ver Historial'
    }).then((result) => {
        showSection('history');
        loadHistory();
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
                                        <small class="text-muted">${new Date(session.created_at).toLocaleDateString()}</small>
                                    </p>
                                    <div class="d-flex justify-content-between">
                                        <span class="badge bg-${session.score >= 70 ? 'success' : 'warning'}">${session.score}%</span>
                                        <span class="text-muted">${session.correct_answers}/${session.total_questions}</span>
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
        url: API_BASE_URL + 'categories/with-count',
        method: 'GET',
        success: function(response) {
            if (response.success) {
                const adminContainer = $('#adminContainer');
                adminContainer.empty();
                
                response.data.forEach(category => {
                    const categoryRow = `
                        <tr>
                            <td>${category.name}</td>
                            <td>${category.question_count || 0}</td>
                            <td>
                                <button class="btn btn-sm btn-primary edit-category-btn" data-id="${category.id}">Editar</button>
                                <button class="btn btn-sm btn-success add-question-btn" data-category-id="${category.id}">Agregar Pregunta</button>
                            </td>
                        </tr>
                    `;
                    adminContainer.append(categoryRow);
                });
            }
        },
        error: function() {
            Swal.fire('Error', 'Error al cargar el dashboard', 'error');
        }
    });
}

// Event listeners para admin
$(document).on('click', '.add-question-btn', function() {
    const categoryId = $(this).data('category-id');
    $('#addQuestionModal').modal('show');
    $('#questionCategory').val(categoryId);
});

$('#addQuestionForm').on('submit', function(e) {
    e.preventDefault();
    
    const formData = {
        category_id: $('#questionCategory').val(),
        nro: $('#questionNro').val(),
        question_text: $('#questionText').val(),
        answer1: $('#questionAnswer1').val(),
        answer2: $('#questionAnswer2').val(),
        answer3: $('#questionAnswer3').val(),
        correct_answer: parseInt($('input[name="correctAnswer"]:checked').val()),
        article_reference: $('#questionArticle').val()
    };
    
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
                loadAdminDashboard();
                Swal.fire('Éxito', 'Pregunta agregada correctamente', 'success');
            } else {
                Swal.fire('Error', response.error || 'Error al agregar pregunta', 'error');
            }
        },
        error: function(xhr) {
            const response = xhr.responseJSON;
            Swal.fire('Error', response?.error || 'Error al agregar pregunta', 'error');
        }
    });
});