<?php
/**
 * User Settings Page
 * 
 * This page allows users to customize their experience by setting preferences
 * such as theme, chart display options, language, default sorting, and map settings.
 */
session_start();
require_once '../config/database.php';
require_once '../includes/language.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$success_message = '';
$error_message = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $theme = $_POST['theme'];
    // Convert checkbox values to integers (1 if checked, 0 if not)
    $show_chart_index = isset($_POST['show_chart_index']) ? 1 : 0;
    $show_chart_dashboard = isset($_POST['show_chart_dashboard']) ? 1 : 0;
    $show_map_index = isset($_POST['show_map_index']) ? 1 : 0;
    $default_sort = $_POST['default_sort'];
    $language = $_POST['language'];
    $map_default_zoom = $_POST['map_default_zoom'];
    $map_default_center = $_POST['map_default_center'];
    $map_default_color = $_POST['map_default_color'];

    try {
        $stmt = $pdo->prepare("INSERT INTO user_settings (
                               user_id, theme, show_chart_index, show_chart_dashboard, 
                               default_sort, language, show_map_index, map_default_zoom,
                               map_default_center, map_default_color) 
                           VALUES (
                               :user_id, :theme, :show_chart_index, :show_chart_dashboard, 
                               :default_sort, :language, :show_map_index, :map_default_zoom,
                               :map_default_center, :map_default_color)
                           ON DUPLICATE KEY UPDATE 
                               theme = VALUES(theme), 
                               show_chart_index = VALUES(show_chart_index), 
                               show_chart_dashboard = VALUES(show_chart_dashboard), 
                               default_sort = VALUES(default_sort),
                               language = VALUES(language),
                               show_map_index = VALUES(show_map_index),
                               map_default_zoom = VALUES(map_default_zoom),
                               map_default_center = VALUES(map_default_center),
                               map_default_color = VALUES(map_default_color)");

        $stmt->execute([
            ':user_id' => $_SESSION['user_id'],
            ':theme' => $theme,
            ':show_chart_index' => $show_chart_index,
            ':show_chart_dashboard' => $show_chart_dashboard,
            ':default_sort' => $default_sort,
            ':language' => $language,
            ':show_map_index' => $show_map_index,
            ':map_default_zoom' => $map_default_zoom,
            ':map_default_center' => $map_default_center,
            ':map_default_color' => $map_default_color
        ]);

        // Update session with new settings to apply immediately
        $_SESSION['user_settings'] = [
            'theme' => $theme,
            'show_chart_index' => $show_chart_index,
            'show_chart_dashboard' => $show_chart_dashboard,
            'default_sort' => $default_sort,
            'language' => $language,
            'show_map_index' => $show_map_index,
            'map_default_zoom' => $map_default_zoom,
            'map_default_center' => $map_default_center,
            'map_default_color' => $map_default_color
        ];

        // Set language for immediate use
        $_SESSION['language'] = $language;
        
        $success_message = ($language == 'en') ? "Settings updated successfully!" : "Pengaturan berhasil diperbarui!";
    } catch (PDOException $e) {
        $error_message = ($language == 'en') ? "Error updating settings: " . $e->getMessage() : "Kesalahan memperbarui pengaturan: " . $e->getMessage();
    }
}

// Fetch current settings
$stmt = $pdo->prepare("SELECT * FROM user_settings WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$settings = $stmt->fetch(PDO::FETCH_ASSOC);

// If no settings found, use defaults
if (!$settings) {
    $settings = [
        'theme' => 'light',
        'show_chart_index' => 1,
        'show_chart_dashboard' => 1,
        'default_sort' => 'jumlah_penderita',
        'language' => 'id', // Default to Indonesian
        'show_map_index' => 1,
        'map_default_zoom' => 5,
        'map_default_center' => '-2.5, 118',
        'map_default_color' => 'penderita'
    ];
}

// Store settings in session for use across pages
$_SESSION['user_settings'] = $settings;
$_SESSION['language'] = $settings['language'] ?? 'id';

// Load language strings
$lang = loadLanguage($_SESSION['language']);

include '../includes/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <?php include '../includes/sidebar.php'; ?>

        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2"><?php echo $lang['settings']; ?></h1>
            </div>

            <?php if ($success_message): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle me-2"></i><?php echo $success_message; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <?php if ($error_message): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-triangle me-2"></i><?php echo $error_message; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <div class="card shadow-sm">
                <div class="card-body">
                    <h5 class="card-title mb-4">
                        <i class="bi bi-sliders me-2"></i><?php echo $lang['application_preferences']; ?>
                    </h5>
                    
                    <form method="POST">
                        <!-- Language Selection -->
                        <div class="mb-4">
                            <label for="language" class="form-label fw-bold"><?php echo $lang['language']; ?></label>
                            <select class="form-select" id="language" name="language">
                                <option value="id" <?php echo ($settings['language'] ?? 'id') === 'id' ? 'selected' : ''; ?>>Bahasa Indonesia</option>
                                <option value="en" <?php echo ($settings['language'] ?? 'id') === 'en' ? 'selected' : ''; ?>>English</option>
                            </select>
                            <div class="form-text"><?php echo $lang['language_help']; ?></div>
                        </div>

                        <!-- Theme Selection -->
                        <div class="mb-4">
                            <label for="theme" class="form-label fw-bold"><?php echo $lang['theme']; ?></label>
                            <select class="form-select" id="theme" name="theme">
                                <option value="light" <?php echo $settings['theme'] === 'light' ? 'selected' : ''; ?>><?php echo $lang['light_theme']; ?></option>
                                <option value="dark" <?php echo $settings['theme'] === 'dark' ? 'selected' : ''; ?>><?php echo $lang['dark_theme']; ?></option>
                            </select>
                            <div class="form-text"><?php echo $lang['theme_help']; ?></div>
                        </div>

                        <!-- Chart Display Options -->
                        <div class="mb-4">
                            <label class="form-label fw-bold"><?php echo $lang['chart_display_options']; ?></label>
                            <div class="form-check form-switch mb-2">
                                <input type="checkbox" class="form-check-input" id="show_chart_index" name="show_chart_index" <?php echo $settings['show_chart_index'] ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="show_chart_index"><?php echo $lang['show_chart_index']; ?></label>
                            </div>

                            <div class="form-check form-switch">
                                <input type="checkbox" class="form-check-input" id="show_chart_dashboard" name="show_chart_dashboard" <?php echo $settings['show_chart_dashboard'] ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="show_chart_dashboard"><?php echo $lang['show_chart_dashboard']; ?></label>
                            </div>
                            <div class="form-text"><?php echo $lang['chart_display_help']; ?></div>
                        </div>

                        <!-- Map Settings Section -->
                        <div class="mb-4">
                            <label class="form-label fw-bold"><?php echo $lang['map_settings']; ?></label>
                            
                            <!-- Show Map Toggle -->
                            <div class="form-check form-switch mb-2">
                                <input type="checkbox" class="form-check-input" id="show_map_index" name="show_map_index" 
                                       <?php echo $settings['show_map_index'] ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="show_map_index"><?php echo $lang['show_map_index']; ?></label>
                            </div>
                            
                            <!-- Map Zoom Level -->
                            <div class="mb-3 mt-3">
                                <label for="map_default_zoom" class="form-label"><?php echo $lang['default_zoom']; ?></label>
                                <select class="form-select" id="map_default_zoom" name="map_default_zoom">
                                    <?php for ($i = 4; $i <= 8; $i++): ?>
                                        <option value="<?php echo $i; ?>" <?php echo ($settings['map_default_zoom'] ?? 5) == $i ? 'selected' : ''; ?>><?php echo $i; ?></option>
                                    <?php endfor; ?>
                                </select>
                                <div class="form-text"><?php echo $lang['zoom_help']; ?></div>
                            </div>
                            
                            <!-- Default Map Center -->
                            <div class="mb-3">
                                <label for="map_default_center" class="form-label"><?php echo $lang['default_center']; ?></label>
                                <select class="form-select" id="map_default_center" name="map_default_center">
                                    <option value="-2.5, 118" <?php echo ($settings['map_default_center'] ?? '-2.5, 118') == '-2.5, 118' ? 'selected' : ''; ?>>Indonesia (Default)</option>
<option value="5.5, 95.3" <?php echo ($settings['map_default_center'] ?? '-2.5, 118') == '5.5, 95.3' ? 'selected' : ''; ?>>Aceh</option>
                                </select>
                                <div class="form-text"><?php echo $lang['center_help']; ?></div>
                            </div>
                            
                            <!-- Default Color Scheme -->
                            <div class="mb-3">
                                <label for="map_default_color" class="form-label"><?php echo $lang['default_color']; ?></label>
                                <select class="form-select" id="map_default_color" name="map_default_color">
                                    <option value="penderita" <?php echo ($settings['map_default_color'] ?? 'penderita') == 'penderita' ? 'selected' : ''; ?>><?php echo $lang['by_patients']; ?></option>
                                    <option value="kematian" <?php echo ($settings['map_default_color'] ?? 'penderita') == 'kematian' ? 'selected' : ''; ?>><?php echo $lang['by_deaths']; ?></option>
                                    <option value="cluster" <?php echo ($settings['map_default_color'] ?? 'penderita') == 'cluster' ? 'selected' : ''; ?>><?php echo $lang['by_cluster']; ?></option>
                                </select>
                                <div class="form-text"><?php echo $lang['color_help']; ?></div>
                            </div>
                            
                            <div class="form-text"><?php echo $lang['map_display_help']; ?></div>
                        </div>

                        <!-- Default Sorting -->
                        <div class="mb-4">
                            <label for="default_sort" class="form-label fw-bold"><?php echo $lang['default_sorting']; ?></label>
                            <select class="form-select" id="default_sort" name="default_sort">
                                <option value="wilayah" <?php echo $settings['default_sort'] === 'wilayah' ? 'selected' : ''; ?>><?php echo $lang['region']; ?></option>
                                <option value="jumlah_penduduk" <?php echo $settings['default_sort'] === 'jumlah_penduduk' ? 'selected' : ''; ?>><?php echo $lang['population']; ?></option>
                                <option value="tahun" <?php echo $settings['default_sort'] === 'tahun' ? 'selected' : ''; ?>><?php echo $lang['year']; ?></option>
                                <option value="jumlah_penderita" <?php echo $settings['default_sort'] === 'jumlah_penderita' ? 'selected' : ''; ?>><?php echo $lang['patient_count']; ?></option>
                                <option value="jumlah_kematian" <?php echo $settings['default_sort'] === 'jumlah_kematian' ? 'selected' : ''; ?>><?php echo $lang['death_count']; ?></option>
                                <option value="cluster" <?php echo $settings['default_sort'] === 'cluster' ? 'selected' : ''; ?>><?php echo $lang['cluster']; ?></option>
                            </select>
                            <div class="form-text"><?php echo $lang['default_sort_help']; ?></div>
                        </div>

                        <!-- Submit Button -->
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save me-2"></i><?php echo $lang['save_settings']; ?>
                        </button>
                    </form>
                </div>
            </div>
            
            <!-- Additional Settings Information -->
            <div class="card shadow-sm mt-4">
                <div class="card-body">
                    <h5 class="card-title">
                        <i class="bi bi-info-circle me-2"></i><?php echo $lang['about_settings']; ?>
                    </h5>
                    <p><?php echo $lang['settings_description']; ?></p>
                    
                    <h6 class="mt-3"><?php echo $lang['theme']; ?></h6>
                    <ul>
                        <li><strong><?php echo $lang['light_theme']; ?>:</strong> <?php echo $lang['light_theme_desc']; ?></li>
                        <li><strong><?php echo $lang['dark_theme']; ?>:</strong> <?php echo $lang['dark_theme_desc']; ?></li>
                    </ul>
                    
                    <h6 class="mt-3"><?php echo $lang['language']; ?></h6>
                    <p><?php echo $lang['language_description']; ?></p>
                    
                    <h6 class="mt-3"><?php echo $lang['chart_display']; ?></h6>
                    <p><?php echo $lang['chart_display_description']; ?></p>
                    
                    <h6 class="mt-3"><?php echo $lang['default_sorting']; ?></h6>
                    <p><?php echo $lang['default_sort_description']; ?></p>
                </div>
            </div>
        </main>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Add visual feedback for switches
    const switches = document.querySelectorAll('.form-check-input[type="checkbox"]');
    switches.forEach(switchEl => {
        switchEl.addEventListener('change', function() {
            if (this.checked) {
                this.classList.add('bg-primary');
            } else {
                this.classList.remove('bg-primary');
            }
        });
        
        // Initial state
        if (switchEl.checked) {
            switchEl.classList.add('bg-primary');
        }
    });
});
</script>

<?php include '../includes/footer.php'; ?>