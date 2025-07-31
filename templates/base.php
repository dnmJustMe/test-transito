<!DOCTYPE html>
<html lang="es" class="h-100">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Test Licencia Cuba' ?></title>
    <meta name="description" content="<?= $description ?? 'Simulador oficial del examen teórico para licencia de conducir en Cuba' ?>">
    <meta name="author" content="Test Licencia Cuba">
    <meta name="keywords" content="licencia cuba, test conducir, examen teórico, permiso conducción, preguntas licencia">
    
    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?= BASE_URL . $_SERVER['REQUEST_URI'] ?>">
    <meta property="og:title" content="<?= $title ?? 'Test Licencia Cuba' ?>">
    <meta property="og:description" content="<?= $description ?? 'Simulador oficial del examen teórico para licencia de conducir en Cuba' ?>">
    <meta property="og:image" content="<?= BASE_URL ?>/img/logo.png">
    
    <!-- Twitter -->
    <meta property="twitter:card" content="summary_large_image">
    <meta property="twitter:url" content="<?= BASE_URL . $_SERVER['REQUEST_URI'] ?>">
    <meta property="twitter:title" content="<?= $title ?? 'Test Licencia Cuba' ?>">
    <meta property="twitter:description" content="<?= $description ?? 'Simulador oficial del examen teórico para licencia de conducir en Cuba' ?>">
    <meta property="twitter:image" content="<?= BASE_URL ?>/img/logo.png">
    
    <!-- Favicon -->
    <link rel="icon" href="<?= BASE_URL ?>/img/favicon.png" type="image/png">
    <link rel="shortcut icon" href="<?= BASE_URL ?>/img/favicon.png">
    <link rel="apple-touch-icon" href="<?= BASE_URL ?>/img/favicon.png">
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    
    <!-- Animate.css -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    
    <!-- AOS (Animate On Scroll) -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    
    <!-- Swiper CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.css">
    
    <!-- Particles.js for background effects -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/particles.js/2.0.0/particles.min.css">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?= ASSETS_URL ?>/css/app.css">
    
    <!-- Custom CSS for specific pages -->
    <?php if (isset($customCSS)): ?>
        <?php foreach ($customCSS as $css): ?>
            <link rel="stylesheet" href="<?= ASSETS_URL ?>/css/<?= $css ?>">
        <?php endforeach; ?>
    <?php endif; ?>
    
    <!-- CSRF Token -->
    <meta name="csrf-token" content="<?= generateCSRFToken() ?>">
    
    <!-- Configuration for JS -->
    <script>
        window.App = {
            baseUrl: '<?= BASE_URL ?>',
            apiUrl: '<?= API_URL ?>',
            assetsUrl: '<?= ASSETS_URL ?>',
            csrfToken: '<?= generateCSRFToken() ?>',
            user: <?= json_encode($currentUser ?? null) ?>,
            config: {
                siteName: '<?= SITE_NAME ?>',
                maxFileSize: <?= MAX_FILE_SIZE ?>,
                allowedImageTypes: <?= json_encode(ALLOWED_IMAGE_TYPES) ?>,
                defaultPageSize: <?= DEFAULT_PAGE_SIZE ?>,
                minQuestionsPerTest: <?= MIN_QUESTIONS_PER_TEST ?>,
                maxQuestionsPerTest: <?= MAX_QUESTIONS_PER_TEST ?>,
                defaultPassingScore: <?= DEFAULT_PASSING_SCORE ?>
            }
        };
    </script>
</head>

<body class="d-flex flex-column h-100">
    <!-- Loading Overlay -->
    <div id="loadingOverlay" class="loading-overlay">
        <div class="loading-spinner">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Cargando...</span>
            </div>
            <p class="mt-3">Cargando...</p>
        </div>
    </div>

    <!-- Alert Container -->
    <div id="alertContainer" class="alert-container"></div>

    <!-- Navigation -->
    <?php include 'partials/navigation.php'; ?>

    <!-- Main Content -->
    <main class="flex-shrink-0">
        <?= $content ?? '' ?>
    </main>

    <!-- Footer -->
    <?php include 'partials/footer.php'; ?>

    <!-- Back to Top Button -->
    <button id="backToTop" class="btn-back-to-top" title="Volver arriba">
        <i class="fas fa-chevron-up"></i>
    </button>

    <!-- JavaScript Libraries -->
    <!-- jQuery -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <!-- AOS (Animate On Scroll) -->
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    
    <!-- Swiper JS -->
    <script src="https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.js"></script>
    
    <!-- Chart.js for statistics -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <!-- Particles.js for background effects -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/particles.js/2.0.0/particles.min.js"></script>
    
    <!-- CountUp.js for animated numbers -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/countup.js/2.6.2/countUp.umd.js"></script>
    
    <!-- Typed.js for typing animations -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/typed.js/2.0.16/typed.umd.js"></script>
    
    <!-- Custom JavaScript -->
    <script src="<?= ASSETS_URL ?>/js/app.js"></script>
    
    <!-- Custom JavaScript for specific pages -->
    <?php if (isset($customJS)): ?>
        <?php foreach ($customJS as $js): ?>
            <script src="<?= ASSETS_URL ?>/js/<?= $js ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>
    
    <!-- Inline JavaScript -->
    <?php if (isset($inlineJS)): ?>
        <script><?= $inlineJS ?></script>
    <?php endif; ?>

    <!-- Initialize AOS -->
    <script>
        AOS.init({
            duration: 800,
            easing: 'ease-in-out',
            once: true,
            mirror: false
        });
    </script>

    <!-- Google Analytics (if configured) -->
    <?php if (getSystemConfig('google_analytics_id')): ?>
        <script async src="https://www.googletagmanager.com/gtag/js?id=<?= getSystemConfig('google_analytics_id') ?>"></script>
        <script>
            window.dataLayer = window.dataLayer || [];
            function gtag(){dataLayer.push(arguments);}
            gtag('js', new Date());
            gtag('config', '<?= getSystemConfig('google_analytics_id') ?>');
        </script>
    <?php endif; ?>
</body>
</html>