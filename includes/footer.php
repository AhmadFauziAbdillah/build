<?php
/**
 * Global Footer Template
 * 
 * This file contains the HTML footer, JavaScript includes, and closing tags for the application.
 * It handles conditional container closing based on the page type and includes sidebar toggle functionality.
 */

// Load language handler if not already loaded
if (!function_exists('getText')) {
    require_once __DIR__ . '/language.php';
}

// Get current language
$currentLang = isset($_SESSION['language']) ? $_SESSION['language'] : 'id';
$lang = loadLanguage($currentLang);

// Determine if this is admin section for container closing
$is_admin = strpos($_SERVER['PHP_SELF'], '/admin/') !== false;

// Close container div if not in admin section
if (!$is_admin): 
?>
        </div> <!-- Close container from header.php -->
<?php endif; ?>

        <!-- Footer -->
        <footer class="footer">
            <div class="container">
                <div class="footer-brand">
                    <i class="bi bi-grid-3x3-gap-fill me-2"></i>
                    Diabetes Clustering Dashboard
                </div>
                <div class="footer-nav">
                    <a href="/"><?php echo $lang['home']; ?></a>
                    <a href="/about.php">About</a>
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <a href="/admin/dashboard.php"><?php echo $lang['dashboard']; ?></a>
                    <?php else: ?>
                        <a href="/admin/login.php"><?php echo $lang['login']; ?></a>
                    <?php endif; ?>
                </div>
                <div class="social-icons">
                    <a href="#" title="GitHub"><i class="bi bi-github"></i></a>
                    <a href="#" title="Twitter"><i class="bi bi-twitter"></i></a>
                    <a href="#" title="LinkedIn"><i class="bi bi-linkedin"></i></a>
                </div>
                <p class="copyright">&copy; <?= date('Y') ?> DBSCAN Clustering Analysis for Diabetes Data</p>
            </div>
        </footer>
        
        <!-- Bootstrap JS Bundle with Popper -->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
        
        <!-- Global JavaScript -->
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // Initialize tooltips
                var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
                var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
                    return new bootstrap.Tooltip(tooltipTriggerEl);
                });
                
                // Initialize popovers
                var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
                var popoverList = popoverTriggerList.map(function(popoverTriggerEl) {
                    return new bootstrap.Popover(popoverTriggerEl);
                });
                
                // Animate elements with fade-in class
                document.querySelectorAll('.fade-in').forEach(function(element, index) {
                    element.style.opacity = '0';
                    element.style.animationDelay = (index * 0.1) + 's';
                    setTimeout(function() {
                        element.style.opacity = '1';
                    }, 100);
                });
                
                // Sidebar toggle functionality
                const sidebarToggle = document.getElementById('sidebar-toggle');
                const mobileSidebarToggle = document.getElementById('mobile-sidebar-toggle');
                const sidebar = document.getElementById('sidebar');
                const mainContent = document.querySelector('main');
                
                // Function to toggle sidebar
                function toggleSidebar() {
                    if (sidebar) {
                        sidebar.classList.toggle('sidebar-collapsed');
                        
                        // Update cookie to remember state
                        const isCollapsed = sidebar.classList.contains('sidebar-collapsed');
                        document.cookie = `sidebar_collapsed=${isCollapsed}; path=/; max-age=31536000`; // 1 year
                        
                        // Toggle main content width
                        if (mainContent) {
                            if (isCollapsed) {
                                mainContent.classList.remove('col-md-9', 'col-lg-10');
                                mainContent.classList.add('col-md-11', 'col-lg-11');
                            } else {
                                mainContent.classList.remove('col-md-11', 'col-lg-11');
                                mainContent.classList.add('col-md-9', 'col-lg-10');
                            }
                        }
                        
                        // Toggle icon direction
                        const icon = sidebarToggle.querySelector('i');
                        if (icon) {
                            if (isCollapsed) {
                                icon.classList.remove('bi-chevron-left');
                                icon.classList.add('bi-chevron-right');
                            } else {
                                icon.classList.remove('bi-chevron-right');
                                icon.classList.add('bi-chevron-left');
                            }
                        }
                    }
                }
                
                // Desktop sidebar toggle
                if (sidebarToggle) {
                    sidebarToggle.addEventListener('click', function(e) {
                        e.preventDefault();
                        toggleSidebar();
                    });
                }
                
                // Mobile sidebar toggle
                if (mobileSidebarToggle) {
                    mobileSidebarToggle.addEventListener('click', function(e) {
                        e.preventDefault();
                        if (sidebar) {
                            sidebar.classList.toggle('mobile-show');
                        }
                    });
                }
                
                // Close mobile sidebar when clicking outside
                document.addEventListener('click', function(e) {
                    if (sidebar && sidebar.classList.contains('mobile-show')) {
                        // Check if click is outside the sidebar
                        if (!sidebar.contains(e.target) && e.target !== mobileSidebarToggle) {
                            sidebar.classList.remove('mobile-show');
                        }
                    }
                });
                
                // Handle initial state
                if (sidebar && sidebar.classList.contains('sidebar-collapsed') && mainContent) {
                    mainContent.classList.remove('col-md-9', 'col-lg-10');
                    mainContent.classList.add('col-md-11', 'col-lg-11');
                    
                    const icon = sidebarToggle ? sidebarToggle.querySelector('i') : null;
                    if (icon) {
                        icon.classList.remove('bi-chevron-left');
                        icon.classList.add('bi-chevron-right');
                    }
                }
            });
        </script>
    </body>
</html>