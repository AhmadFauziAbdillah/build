<?php
session_start();
require_once '../config/database.php';

// Include language handler
require_once '../includes/language.php';

// Get current language (default to Indonesian)
$currentLang = isset($_SESSION['language']) ? $_SESSION['language'] : 'id';
$lang = loadLanguage($currentLang);

// Generate a random CAPTCHA code
function generateCaptchaCode($length = 6) {
    // Use only uppercase letters and numbers for better readability
    $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $captcha = '';
    $max = strlen($characters) - 1;
    
    for ($i = 0; $i < $length; $i++) {
        $captcha .= $characters[rand(0, $max)];
    }
    
    return $captcha;
}

// Generate a new CAPTCHA code if not exists
if (!isset($_SESSION['captcha'])) {
    $_SESSION['captcha'] = generateCaptchaCode();
}

$error = '';
$current_captcha = $_SESSION['captcha']; // Store current CAPTCHA before processing

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $captcha = isset($_POST['captcha']) ? trim($_POST['captcha']) : '';
    
    // Verify CAPTCHA first - use case-insensitive comparison and trim input
    if (strcasecmp(trim($captcha), trim($current_captcha)) !== 0) {
        $error = $lang['captcha_failed'];
    } else {
        // Verify user credentials
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            
            // Get user settings
            $stmt = $pdo->prepare("SELECT * FROM user_settings WHERE user_id = ?");
            $stmt->execute([$user['id']]);
            $settings = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Store settings in session
            if ($settings) {
                $_SESSION['user_settings'] = $settings;
                $_SESSION['language'] = $settings['language'] ?? 'id';
            }
            
            header("Location: dashboard.php");
            exit();
        } else {
            $error = $lang['login_failed'];
        }
    }
    
    // Generate a new CAPTCHA only after validation
    $_SESSION['captcha'] = generateCaptchaCode();
}
?>

<!DOCTYPE html>
<html lang="<?php echo $currentLang; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Diabetes Clustering DBSCAN</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            margin: 0;
            padding: 0;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        
        .top-navbar {
            background-color: #0d6efd;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            padding: 0.75rem 1rem;
        }
        
        .navbar-brand {
            color: white;
            font-size: 1.25rem;
            font-weight: 500;
            text-decoration: none;
            display: flex;
            align-items: center;
        }
        
        .navbar-brand i {
            margin-right: 0.75rem;
            font-size: 1.25rem;
        }
        
        .nav-link {
            color: rgba(255,255,255,0.85);
            text-decoration: none;
            transition: color 0.15s ease-in-out;
        }
        
        .nav-link:hover {
            color: white;
        }
        
        .main-content {
            flex: 1;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 2rem 1rem;
        }
        
        .login-form {
            background: white;
            border-radius: 0.5rem;
            box-shadow: 0 0.5rem 1rem rgba(0,0,0,0.15);
            width: 100%;
            max-width: 450px;
            padding: 2rem;
        }
        
        .login-header {
            text-align: center;
            margin-bottom: 1.5rem;
        }
        
        .login-header h1 {
            font-size: 1.75rem;
            font-weight: 500;
            color: #212529;
            margin: 0;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .login-header h1 i {
            margin-right: 0.5rem;
            color: #0d6efd;
        }
        
        .form-label {
            font-weight: 500;
            color: #495057;
            margin-bottom: 0.5rem;
        }
        
        .form-control {
            padding: 0.75rem 1rem;
            font-size: 1rem;
            border: 1px solid #ced4da;
            border-radius: 0.375rem;
        }
        
        .input-group {
            position: relative;
            margin-bottom: 1.5rem;
        }
        
        .input-icon {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: #6c757d;
            z-index: 10;
        }
        
        .form-control {
            padding-left: 2.5rem;
        }
        
        .captcha-container {
            margin-bottom: 1rem;
        }
        
        .captcha-box {
            background-color: #f1f3f5;
            border: 1px dashed #ced4da;
            border-radius: 0.375rem;
            padding: 0.75rem;
            font-family: 'Courier New', monospace;
            font-weight: bold;
            font-size: 1.25rem;
            letter-spacing: 2px;
            text-align: center;
            user-select: none;
            position: relative;
            height: 50px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 0.5rem;
            overflow: hidden;
        }
        
        .captcha-box::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: repeating-linear-gradient(
                45deg,
                rgba(0, 0, 0, 0.05),
                rgba(0, 0, 0, 0.05) 10px,
                rgba(0, 0, 0, 0) 10px,
                rgba(0, 0, 0, 0) 20px
            );
            pointer-events: none;
        }
        
        .captcha-refresh {
            text-align: right;
            margin-bottom: 0.5rem;
        }
        
        .captcha-refresh a {
            color: #0d6efd;
            text-decoration: none;
            font-size: 0.875rem;
            display: inline-flex;
            align-items: center;
        }
        
        .captcha-refresh a i {
            margin-right: 0.25rem;
        }
        
        .captcha-refresh a:hover {
            text-decoration: underline;
        }
        
        .captcha-help {
            font-size: 0.875rem;
            color: #6c757d;
            margin-top: 0.5rem;
        }
        
        .btn-login {
            background-color: #0d6efd;
            border-color: #0d6efd;
            color: white;
            font-weight: 500;
            padding: 0.75rem 1rem;
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .btn-login i {
            margin-right: 0.5rem;
        }
        
        .footer {
            text-align: center;
            padding: 1rem;
            color: #6c757d;
            font-size: 0.875rem;
        }
        
        .alert {
            border-radius: 0.375rem;
            padding: 1rem;
            margin-bottom: 1.5rem;
        }
        
        .alert-danger {
            background-color: #f8d7da;
            border-color: #f5c2c7;
            color: #842029;
        }
        
        /* Language selector */
        .language-selector {
            text-align: center;
            margin-top: 1rem;
        }
        
        .language-selector a {
            color: #6c757d;
            text-decoration: none;
            padding: 0.25rem 0.5rem;
            margin: 0 0.25rem;
            border-radius: 0.25rem;
            font-size: 0.875rem;
        }
        
        .language-selector a.active {
            background-color: #e9ecef;
            font-weight: 500;
        }
        
        .language-selector a:hover {
            background-color: #f8f9fa;
        }
    </style>
</head>
<body>
    <!-- Top Navbar -->
    <nav class="top-navbar">
        <div class="container-fluid d-flex justify-content-between align-items-center">
            <a class="navbar-brand" href="/">
                <i class="bi bi-grid-3x3-gap-fill"></i>
                Diabetes Clustering DBSCAN
            </a>
            <a href="/" class="nav-link">
                <i class="bi bi-house-door me-1"></i><?php echo $lang['home']; ?>
            </a>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="main-content">
        <div class="login-form">
            <div class="login-header">
                <h1><i class="bi bi-lock"></i> <?php echo $lang['admin_login']; ?></h1>
            </div>
            
            <?php if ($error): ?>
                <div class="alert alert-danger" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                <!-- Username -->
                <div class="mb-3">
                    <label for="username" class="form-label"><?php echo $lang['username']; ?></label>
                    <div class="input-group">
                        <i class="bi bi-person input-icon"></i>
                        <input type="text" class="form-control" id="username" name="username" required>
                    </div>
                </div>
                
                <!-- Password -->
                <div class="mb-3">
                    <label for="password" class="form-label"><?php echo $lang['password']; ?></label>
                    <div class="input-group">
                        <i class="bi bi-key input-icon"></i>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                </div>
                
                <!-- CAPTCHA -->
                <div class="mb-4">
                    <label for="captcha" class="form-label"><?php echo $lang['captcha_verification']; ?></label>
                    <div class="captcha-container">
                        <div class="captcha-box" id="captcha-text">
                            <?php echo htmlspecialchars($_SESSION['captcha']); ?>
                        </div>
                        <div class="captcha-refresh">
                            <a href="#" id="refresh-captcha">
                                <i class="bi bi-arrow-repeat"></i> <?php echo $lang['refresh_captcha']; ?>
                            </a>
                        </div>
                        <div class="input-group">
                            <i class="bi bi-shield-lock input-icon"></i>
                            <input type="text" class="form-control" id="captcha" name="captcha" required autocomplete="off">
                        </div>
                        <div class="captcha-help">
                            <?php echo $lang['captcha_help']; ?>
                        </div>
                    </div>
                </div>
                
                <!-- Login Button -->
                <button type="submit" class="btn btn-login">
                    <i class="bi bi-box-arrow-in-right"></i> <?php echo $lang['login']; ?>
                </button>
            </form>
            
            <!-- Language Selector -->
            <div class="language-selector mt-4">
                <a href="?lang=id" class="<?php echo $currentLang === 'id' ? 'active' : ''; ?>" id="lang-id">Bahasa Indonesia</a>
                <span class="text-muted">|</span>
                <a href="?lang=en" class="<?php echo $currentLang === 'en' ? 'active' : ''; ?>" id="lang-en">English</a>
            </div>
        </div>
    </div>
    
    <!-- Footer -->
    <div class="footer">
        &copy; <?php echo date('Y'); ?> Diabetes Clustering Dashboard
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Handle CAPTCHA refresh
            document.getElementById('refresh-captcha').addEventListener('click', function(e) {
                e.preventDefault();
                
                // Make AJAX request to refresh CAPTCHA
                fetch('refresh_captcha.php')
                    .then(response => response.text())
                    .then(captcha => {
                        document.getElementById('captcha-text').innerText = captcha;
                        document.getElementById('captcha').value = ''; // Clear the input field
                    })
                    .catch(error => console.error('Error refreshing CAPTCHA:', error));
            });
            
            // Handle language selection
            document.getElementById('lang-id').addEventListener('click', function(e) {
                e.preventDefault();
                document.cookie = "lang=id; path=/; max-age=31536000";
                window.location.reload();
            });
            
            document.getElementById('lang-en').addEventListener('click', function(e) {
                e.preventDefault();
                document.cookie = "lang=en; path=/; max-age=31536000";
                window.location.reload();
            });
        });
    </script>
</body>
</html>