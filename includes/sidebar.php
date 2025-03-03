<?php
/**
 * Sidebar Navigation
 * 
 * This file provides the collapsible sidebar navigation for the admin dashboard.
 * It includes a toggle button and highlights the current page.
 */

// Load language handler if not already loaded
if (!function_exists('getText')) {
    require_once __DIR__ . '/language.php';
}

// Get current language
$currentLang = isset($_SESSION['language']) ? $_SESSION['language'] : 'id';
$lang = loadLanguage($currentLang);

// Get current page for active menu highlight
$current_page = basename($_SERVER['PHP_SELF']);

// Get the sidebar state from cookie (default to expanded)
$sidebarCollapsed = isset($_COOKIE['sidebar_collapsed']) && $_COOKIE['sidebar_collapsed'] === 'true';
$sidebarClass = $sidebarCollapsed ? 'sidebar-collapsed' : '';
?>

<!-- Sidebar Toggle Button (Mobile) -->
<button class="btn btn-link d-md-none sidebar-toggler" id="mobile-sidebar-toggle">
    <i class="bi bi-list fs-4"></i>
</button>

<!-- Sidebar -->
<nav id="sidebar" class="col-md-3 col-lg-2 d-md-block sidebar <?php echo $sidebarClass; ?>">
    <div class="position-sticky pt-3">
        <!-- Sidebar Header with Toggle Button -->
        <div class="sidebar-header d-flex justify-content-between align-items-center px-3 mb-2">
            <h5 class="sidebar-title"><?php echo $lang['dashboard']; ?></h5>
            <button class="btn btn-link sidebar-toggle p-0" id="sidebar-toggle" title="Toggle Sidebar">
                <i class="bi bi-chevron-left"></i>
            </button>
        </div>
        
        <ul class="nav flex-column">
            <!-- Dashboard -->
            <li class="nav-item">
                <a class="nav-link <?= $current_page === 'dashboard.php' ? 'active' : '' ?>" href="/admin/dashboard.php">
                    <i class="bi bi-house-door me-2"></i>
                    <span class="sidebar-text"><?php echo $lang['dashboard']; ?></span>
                </a>
            </li>
            
            <!-- Input Data -->
            <li class="nav-item">
                <a class="nav-link <?= $current_page === 'data_input.php' ? 'active' : '' ?>" href="/admin/data_input.php">
                    <i class="bi bi-file-earmark-plus me-2"></i>
                    <span class="sidebar-text"><?php echo $lang['add_data']; ?></span>
                </a>
            </li>
            
            <!-- Manage Data (Ubah Data) -->
            <li class="nav-item">
                <a class="nav-link <?= $current_page === 'delete_data.php' || $current_page === 'edit_data.php' ? 'active' : '' ?>" href="/admin/delete_data.php">
                    <i class="bi bi-file-earmark-minus me-2"></i>
                    <span class="sidebar-text"><?php echo $lang['manage_data']; ?></span>
                </a>
            </li>
            
            <!-- DBSCAN Clustering -->
            <li class="nav-item">
                <a class="nav-link <?= $current_page === 'dbscan_clustering.php' ? 'active' : '' ?>" href="/admin/dbscan_clustering.php">
                    <i class="bi bi-diagram-3 me-2"></i>
                    <span class="sidebar-text">DBSCAN Clustering</span>
                </a>
            </li>
            
            <!-- Region Coordinates Management -->
            <li class="nav-item">
                <a class="nav-link <?= $current_page === 'region_coordinates.php' ? 'active' : '' ?>" href="/admin/region_coordinates.php">
                    <i class="bi bi-pin-map me-2"></i>
                    <span class="sidebar-text"><?php echo $lang['region_coordinates'] ?? 'Koordinat Wilayah'; ?></span>
                </a>
            </li>
            
            <!-- Region Boundaries Management -->
            <li class="nav-item">
                <a class="nav-link <?= $current_page === 'region_boundaries.php' ? 'active' : '' ?>" href="/admin/region_boundaries.php">
                    <i class="bi bi-geo-alt me-2"></i>
                    <span class="sidebar-text">Batas Wilayah</span>
                </a>
            </li>
            
            <!-- Settings -->
            <li class="nav-item">
                <a class="nav-link <?= $current_page === 'settings.php' ? 'active' : '' ?>" href="/admin/settings.php">
                    <i class="bi bi-gear me-2"></i>
                    <span class="sidebar-text"><?php echo $lang['settings']; ?></span>
                </a>
            </li>
            
            <!-- Separator Line -->
            <li class="nav-item">
                <hr class="sidebar-divider my-2">
            </li>
            
            <!-- Logout -->
            <li class="nav-item">
                <a class="nav-link" href="/admin/logout.php">
                    <i class="bi bi-box-arrow-right me-2"></i>
                    <span class="sidebar-text"><?php echo $lang['logout']; ?></span>
                </a>
            </li>
        </ul>
    </div>
</nav>