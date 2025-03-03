<?php
// Include language handler
require_once __DIR__ . '/language.php';

// Get current language
$currentLang = isset($_SESSION['language']) ? $_SESSION['language'] : 'id';
$lang = loadLanguage($currentLang);

// Get user theme preference if logged in
$theme = 'light';
if (isset($_SESSION['user_id'])) {
    $stmt = $pdo->prepare("SELECT theme FROM user_settings WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $setting = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($setting && $setting['theme']) {
        $theme = $setting['theme'];
    }
}
?>
<!DOCTYPE html>
<html lang="<?php echo $currentLang; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Diabetes Clustering Dashboard</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="/css/style.css">
    <link rel="stylesheet" href="/css/sidebar.css">
    
    <!-- Theme CSS -->
    <link rel="stylesheet" href="/css/<?php echo $theme; ?>.css">
    
    <meta name="description" content="Diabetes Clustering Analysis using DBSCAN algorithm">
    <meta name="author" content="Diabetes Data Team">
    
    <?php if (isset($show_map_index) && $show_map_index): ?>
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"></script>
<style>
.map-legend {
    margin-top: 10px;
}

.legend-item {
    display: inline-block;
    width: 20px;
    height: 20px;
    border-radius: 3px;
    margin-right: 5px;
    border: 1px solid rgba(0,0,0,0.2);
}

.map-controls {
    width: 220px;
}

.leaflet-popup-content {
    margin: 10px;
}

.leaflet-popup-content p {
    margin: 5px 0;
}
</style>
<?php endif; ?>

</head>
<body class="<?php echo $theme; ?>-theme">
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container-fluid px-md-4">
            <a class="navbar-brand">
                <i class="bi bi-grid-3x3-gap-fill"></i>
                <span>Diabetes Clustering DBSCAN</span>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" 
                    aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="/"><i class="bi bi-house-door me-1"></i><?php echo $lang['home']; ?></a>
                    </li>
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="/admin/dashboard.php">
                                <i class="bi bi-speedometer2 me-1"></i><?php echo $lang['dashboard']; ?>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/admin/settings.php">
                                <i class="bi bi-gear me-1"></i><?php echo $lang['settings']; ?>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/admin/logout.php">
                                <i class="bi bi-box-arrow-right me-1"></i><?php echo $lang['logout']; ?>
                            </a>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="/admin/login.php">
                                <i class="bi bi-box-arrow-in-right me-1"></i><?php echo $lang['login']; ?>
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>
    
    <!-- Main Content Container -->
    <div class="container-fluid mt-4">
        <?php 
        // Container is closed in footer.php 
        ?>