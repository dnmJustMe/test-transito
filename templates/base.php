<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Test Licencia Cuba' ?></title>
    <meta name="description" content="<?= $description ?? 'Simulador oficial del examen teórico para licencia de conducir en Cuba' ?>">
    <meta name="author" content="DNMJustMe">
    <meta name="keywords" content="licencia cuba, test conducir, examen teórico, permiso conducción, preguntas licencia">
    
    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?= BASE_URL . $_SERVER['REQUEST_URI'] ?>">
    <meta property="og:title" content="<?= $title ?? 'Test Licencia Cuba' ?>">
    <meta property="og:description" content="<?= $description ?? 'Simulador oficial del examen teórico para licencia de conducir en Cuba' ?>">
    <meta property="og:image" content="<?= BASE_URL ?>/assets/images/logo.png">
    
    <!-- Twitter -->
    <meta property="twitter:card" content="summary_large_image">
    <meta property="twitter:url" content="<?= BASE_URL . $_SERVER['REQUEST_URI'] ?>">
    <meta property="twitter:title" content="<?= $title ?? 'Test Licencia Cuba' ?>">
    <meta property="twitter:description" content="<?= $description ?? 'Simulador oficial del examen teórico para licencia de conducir en Cuba' ?>">
    <meta property="twitter:image" content="<?= BASE_URL ?>/assets/images/logo.png">
    
    <!-- Favicon -->
    <link rel="icon" href="<?= BASE_URL ?>/assets/images/favicon.ico" type="image/x-icon">
    <link rel="apple-touch-icon" href="<?= BASE_URL ?>/assets/images/logo.png">
    
    <!-- CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/animate.css@4.1.1/animate.min.css">
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
            csrfToken: '<?= generateCSRFToken() ?>',
            user: <?= json_encode($currentUser ?? null) ?>,
            config: {
                minQuestions: <?= MIN_QUESTIONS_PER_TEST ?>,
                maxQuestions: <?= MAX_QUESTIONS_PER_TEST ?>,
                passingScore: <?= DEFAULT_PASSING_SCORE ?>
            }
        };
    </script>
</head>
<body class="<?= $bodyClass ?? '' ?>">
    <!-- Loading Spinner -->
    <div id="loadingSpinner" class="loading-spinner d-none">
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Cargando...</span>
        </div>
    </div>
    
    <!-- Navigation -->
    <?php if (!isset($hideNavigation) || !$hideNavigation): ?>
        <?php include 'partials/navigation.php'; ?>
    <?php endif; ?>
    
    <!-- Alert Container -->
    <div id="alertContainer" class="alert-container"></div>
    
    <!-- Main Content -->
    <main class="main-content <?= $mainClass ?? '' ?>">
        <?php if (isset($showBreadcrumb) && $showBreadcrumb): ?>
            <?php include 'partials/breadcrumb.php'; ?>
        <?php endif; ?>
        
        <?= $content ?? '' ?>
    </main>
    
    <!-- Footer -->
    <?php if (!isset($hideFooter) || !$hideFooter): ?>
        <?php include 'partials/footer.php'; ?>
    <?php endif; ?>
    
    <!-- Modals Container -->
    <div id="modalsContainer"></div>
    
    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="<?= ASSETS_URL ?>/js/app.js"></script>
    
    <!-- Custom JS for specific pages -->
    <?php if (isset($customJS)): ?>
        <?php foreach ($customJS as $js): ?>
            <script src="<?= ASSETS_URL ?>/js/<?= $js ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>
    
    <!-- Inline Scripts -->
    <?php if (isset($inlineScripts)): ?>
        <script>
            <?= $inlineScripts ?>
        </script>
    <?php endif; ?>
    
    <!-- Google Analytics -->
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