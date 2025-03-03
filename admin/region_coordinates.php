<?php
/**
 * Region Coordinates Management Page
 * 
 * This file allows administrators to manage geographic coordinates for regions
 * that are used in the map visualization.
 */
session_start();
require_once '../config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$success_message = '';
$error_message = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add_coordinates'])) {
        // Add new coordinates
        $wilayah = trim($_POST['wilayah']);
        $latitude = floatval($_POST['latitude']);
        $longitude = floatval($_POST['longitude']);
        
        try {
            $stmt = $pdo->prepare("INSERT INTO region_coordinates (wilayah, latitude, longitude) 
                                   VALUES (?, ?, ?) 
                                   ON DUPLICATE KEY UPDATE latitude = ?, longitude = ?");
            $stmt->execute([$wilayah, $latitude, $longitude, $latitude, $longitude]);
            $success_message = "Koordinat untuk wilayah \"$wilayah\" berhasil disimpan.";
        } catch (PDOException $e) {
            $error_message = "Error: " . $e->getMessage();
        }
    } elseif (isset($_POST['delete'])) {
        // Delete coordinates
        $id = $_POST['id'];
        try {
            $stmt = $pdo->prepare("DELETE FROM region_coordinates WHERE id = ?");
            $stmt->execute([$id]);
            $success_message = "Koordinat berhasil dihapus.";
        } catch (PDOException $e) {
            $error_message = "Error deleting coordinates: " . $e->getMessage();
        }
    } elseif (isset($_POST['import_coordinates'])) {
        // Import coordinates from all regions in diabetes_data
        try {
            // Get all unique regions
            $stmt = $pdo->query("SELECT DISTINCT wilayah FROM diabetes_data 
                                 WHERE wilayah NOT IN (SELECT wilayah FROM region_coordinates)");
            $missing_regions = $stmt->fetchAll(PDO::FETCH_COLUMN);
            
            $imported = 0;
            
            // For demonstration, we'll add some dummy coordinates
            // In a real application, you might want to use a geocoding API
            foreach ($missing_regions as $region) {
                // Generate random coordinates within Indonesia (very simplified)
                $lat = mt_rand(-10000, 10000) / 1000; // Range: -10 to 10
                $lng = mt_rand(95000, 140000) / 1000; // Range: 95 to 140
                
                $stmt = $pdo->prepare("INSERT INTO region_coordinates (wilayah, latitude, longitude) VALUES (?, ?, ?)");
                $stmt->execute([$region, $lat, $lng]);
                $imported++;
            }
            
            if ($imported > 0) {
                $success_message = "Berhasil mengimpor $imported wilayah dengan koordinat sementara. Silakan sesuaikan koordinat yang benar.";
            } else {
                $success_message = "Semua wilayah sudah memiliki koordinat.";
            }
        } catch (PDOException $e) {
            $error_message = "Error importing coordinates: " . $e->getMessage();
        }
    }
}

// Get regions that don't have coordinates
$stmt = $pdo->query("SELECT DISTINCT d.wilayah 
                     FROM diabetes_data d 
                     LEFT JOIN region_coordinates c ON d.wilayah = c.wilayah 
                     WHERE c.wilayah IS NULL");
$missing_coordinates = $stmt->fetchAll(PDO::FETCH_COLUMN);

// Get all coordinates with pagination
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$items_per_page = 10;
$offset = ($page - 1) * $items_per_page;

// Search functionality
$search = isset($_GET['search']) ? $_GET['search'] : '';

if (!empty($search)) {
    // Search query
    $query = "SELECT * FROM region_coordinates 
              WHERE wilayah LIKE :search 
              ORDER BY wilayah ASC
              LIMIT :offset, :limit";
    
    $stmt = $pdo->prepare($query);
    $search_param = "%$search%";
    $stmt->bindParam(':search', $search_param, PDO::PARAM_STR);
    $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
    $stmt->bindParam(':limit', $items_per_page, PDO::PARAM_INT);
    $stmt->execute();
    
    // Get total count for pagination
    $count_query = "SELECT COUNT(*) FROM region_coordinates WHERE wilayah LIKE :search";
    $count_stmt = $pdo->prepare($count_query);
    $count_stmt->bindParam(':search', $search_param, PDO::PARAM_STR);
    $count_stmt->execute();
    $total_items = $count_stmt->fetchColumn();
} else {
    // Regular query with no search
    $query = "SELECT * FROM region_coordinates ORDER BY wilayah ASC LIMIT :offset, :limit";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
    $stmt->bindParam(':limit', $items_per_page, PDO::PARAM_INT);
    $stmt->execute();
    
    // Get total count for pagination
    $count_query = "SELECT COUNT(*) FROM region_coordinates";
    $count_stmt = $pdo->query($count_query);
    $total_items = $count_stmt->fetchColumn();
}

$coordinates = $stmt->fetchAll();
$total_pages = ceil($total_items / $items_per_page);

// Include header
include '../includes/header.php';

// Function to generate page URL
function pageUrl($pageNum) {
    global $search;
    $params = ['page' => $pageNum];
    
    if (!empty($search)) {
        $params['search'] = $search;
    }
    
    return "?" . http_build_query($params);
}
?>

<div class="container-fluid">
    <div class="row">
        <?php include '../includes/sidebar.php'; ?>

        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Kelola Koordinat Wilayah</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <form method="POST" class="me-2">
                        <button type="submit" name="import_coordinates" class="btn btn-sm btn-outline-secondary">
                            <i class="bi bi-cloud-download me-1"></i> Import Koordinat Wilayah
                        </button>
                    </form>
                </div>
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

            <!-- Missing Coordinates Warning -->
            <?php if (!empty($missing_coordinates)): ?>
                <div class="alert alert-warning" role="alert">
                    <h5 class="alert-heading"><i class="bi bi-exclamation-triangle me-2"></i>Wilayah Tanpa Koordinat</h5>
                    <p>Beberapa wilayah tidak memiliki koordinat dan tidak akan tampil di peta:</p>
                    <ul class="mb-0">
                        <?php foreach (array_slice($missing_coordinates, 0, 5) as $region): ?>
                            <li><?php echo htmlspecialchars($region); ?></li>
                        <?php endforeach; ?>
                        <?php if (count($missing_coordinates) > 5): ?>
                            <li>... dan <?php echo count($missing_coordinates) - 5; ?> wilayah lainnya</li>
                        <?php endif; ?>
                    </ul>
                    <hr>
                    <p class="mb-0">Gunakan tombol "Import Koordinat Wilayah" untuk menambahkan koordinat sementara untuk semua wilayah.</p>
                </div>
            <?php endif; ?>

            <div class="row">
                <!-- Add Coordinates Form -->
                <div class="col-lg-4 mb-4">
                    <div class="card shadow-sm">
                        <div class="card-body">
                            <h5 class="card-title mb-3">
                                <i class="bi bi-pin-map me-2"></i>
                                Tambah/Edit Koordinat
                            </h5>
                            
                            <form method="POST">
                                <div class="mb-3">
                                    <label for="wilayah" class="form-label">Wilayah</label>
                                    <div class="input-group">
                                        <span class="input-group-text">
                                            <i class="bi bi-geo-alt"></i>
                                        </span>
                                        <input type="text" class="form-control" id="wilayah" name="wilayah" required
                                               placeholder="Nama wilayah">
                                    </div>
                                    <div class="form-text">Masukkan nama wilayah yang sama persis dengan data diabetes.</div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="latitude" class="form-label">Latitude</label>
                                    <div class="input-group">
                                        <span class="input-group-text">
                                            <i class="bi bi-compass"></i>
                                        </span>
                                        <input type="number" step="0.000001" class="form-control" id="latitude" 
                                               name="latitude" required placeholder="-6.2088">
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="longitude" class="form-label">Longitude</label>
                                    <div class="input-group">
                                        <span class="input-group-text">
                                            <i class="bi bi-compass"></i>
                                        </span>
                                        <input type="number" step="0.000001" class="form-control" id="longitude" 
                                               name="longitude" required placeholder="106.8456">
                                    </div>
                                </div>
                                
                                <button type="submit" name="add_coordinates" class="btn btn-primary w-100">
                                    <i class="bi bi-save me-2"></i>Simpan Koordinat
                                </button>
                            </form>
                            
                            <div class="mt-4">
                                <div class="alert alert-info">
                                    <h6 class="alert-heading">Mencari Koordinat:</h6>
                                    <p class="small mb-1">Anda dapat mencari koordinat latitude/longitude dengan cara:</p>
                                    <ul class="small">
                                        <li>Gunakan Google Maps, klik kanan lokasi dan pilih "What's here?"</li>
                                        <li>Gunakan layanan seperti <a href="https://www.latlong.net/" target="_blank">LatLong.net</a></li>
                                        <li>Untuk Indonesia, koordinat Latitude berkisar -11 hingga 6</li>
                                        <li>Untuk Indonesia, koordinat Longitude berkisar 95 hingga 141</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Coordinates List -->
                <div class="col-lg-8">
                    <div class="card shadow-sm">
                        <div class="card-body">
                            <h5 class="card-title mb-3">
                                <i class="bi bi-list-ul me-2"></i>
                                Daftar Koordinat Wilayah
                            </h5>
                            
                            <!-- Search Box -->
                            <div class="mb-3">
                                <form method="GET" class="row g-2">
                                    <div class="col">
                                        <div class="input-group">
                                            <input type="text" class="form-control" placeholder="Cari wilayah..." 
                                                   name="search" value="<?php echo htmlspecialchars($search); ?>">
                                            <button class="btn btn-primary" type="submit">
                                                <i class="bi bi-search"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <?php if (!empty($search)): ?>
                                    <div class="col-auto">
                                        <a href="region_coordinates.php" class="btn btn-outline-secondary">
                                            <i class="bi bi-x-circle me-1"></i> Reset
                                        </a>
                                    </div>
                                    <?php endif; ?>
                                </form>
                            </div>
                            
                            <!-- Coordinates Table -->
                            <div class="table-responsive">
                                <table class="table table-hover table-striped">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Wilayah</th>
                                            <th>Latitude</th>
                                            <th>Longitude</th>
                                            <th>Terakhir Diperbarui</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (count($coordinates) > 0): ?>
                                            <?php foreach ($coordinates as $coord): ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars($coord['wilayah']); ?></td>
                                                    <td><?php echo number_format($coord['latitude'], 6); ?></td>
                                                    <td><?php echo number_format($coord['longitude'], 6); ?></td>
                                                    <td><?php echo date('d M Y H:i', strtotime($coord['updated_at'])); ?></td>
                                                    <td>
                                                        <div class="btn-group" role="group">
                                                            <button type="button" class="btn btn-sm btn-primary edit-btn"
                                                                    data-wilayah="<?php echo htmlspecialchars($coord['wilayah']); ?>"
                                                                    data-lat="<?php echo $coord['latitude']; ?>"
                                                                    data-lng="<?php echo $coord['longitude']; ?>">
                                                                <i class="bi bi-pencil"></i>
                                                            </button>
                                                            <form method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus koordinat untuk <?php echo htmlspecialchars($coord['wilayah']); ?>?');" style="display:inline;">
                                                                <input type="hidden" name="id" value="<?php echo $coord['id']; ?>">
                                                                <button type="submit" name="delete" class="btn btn-sm btn-danger">
                                                                    <i class="bi bi-trash"></i>
                                                                </button>
                                                            </form>
                                                        </div>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="5" class="text-center py-4">
                                                    <div class="text-muted">
                                                        <i class="bi bi-geo-alt display-6 d-block mb-3"></i>
                                                        <p>Tidak ada data koordinat wilayah.</p>
                                                        <p>Silakan tambahkan koordinat untuk mulai menggunakan peta.</p>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                            
                            <!-- Pagination -->
                            <?php if ($total_pages > 1): ?>
                                <nav aria-label="Page navigation" class="mt-4">
                                    <ul class="pagination justify-content-center">
                                        <li class="page-item<?php echo $page <= 1 ? ' disabled' : ''; ?>">
                                            <a class="page-link" href="<?php echo $page > 1 ? pageUrl($page - 1) : '#'; ?>" tabindex="-1">
                                                <i class="bi bi-chevron-left"></i> Previous
                                            </a>
                                        </li>
                                        
                                        <?php 
                                        $start_page = max(1, min($page - 2, $total_pages - 4));
                                        $end_page = min($total_pages, max($page + 2, 5));
                                        
                                        for ($i = $start_page; $i <= $end_page; $i++): 
                                        ?>
                                            <li class="page-item<?php echo $i == $page ? ' active' : ''; ?>">
                                                <a class="page-link" href="<?php echo pageUrl($i); ?>"><?php echo $i; ?></a>
                                            </li>
                                        <?php endfor; ?>
                                        
                                        <li class="page-item<?php echo $page >= $total_pages ? ' disabled' : ''; ?>">
                                            <a class="page-link" href="<?php echo $page < $total_pages ? pageUrl($page + 1) : '#'; ?>">
                                                Next <i class="bi bi-chevron-right"></i>
                                            </a>
                                        </li>
                                    </ul>
                                </nav>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Fill form with data for editing
    const editButtons = document.querySelectorAll('.edit-btn');
    
    editButtons.forEach(button => {
        button.addEventListener('click', function() {
            const wilayah = this.getAttribute('data-wilayah');
            const lat = this.getAttribute('data-lat');
            const lng = this.getAttribute('data-lng');
            
            document.getElementById('wilayah').value = wilayah;
            document.getElementById('latitude').value = lat;
            document.getElementById('longitude').value = lng;
            
            // Scroll to the form
            document.querySelector('.card-title').scrollIntoView({ behavior: 'smooth' });
        });
    });
});
</script>

<?php include '../includes/footer.php'; ?>