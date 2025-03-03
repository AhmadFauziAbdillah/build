<?php
session_start();
require_once '../config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$success_message = '';
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Process form submission
    $wilayah = $_POST['wilayah'];
    $jumlah_penduduk = $_POST['jumlah_penduduk']; // New field
    $tahun = $_POST['tahun'];
    $jumlah_penderita = $_POST['jumlah_penderita'];
    $jumlah_kematian = $_POST['jumlah_kematian'];

    try {
        $stmt = $pdo->prepare("INSERT INTO diabetes_data (wilayah, jumlah_penduduk, tahun, jumlah_penderita, jumlah_kematian) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$wilayah, $jumlah_penduduk, $tahun, $jumlah_penderita, $jumlah_kematian]);
        $success_message = "Data successfully added!";
    } catch (PDOException $e) {
        $error_message = "Database Error: " . $e->getMessage();
    }
}

include '../includes/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <?php include '../includes/sidebar.php'; ?>

        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Input Data Diabetes</h1>
            </div>

            <?php if ($success_message): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle me-2"></i><?php echo $success_message; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <?php if ($error_message): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    <h5 class="alert-heading">Error Details:</h5>
                    <p><?php echo $error_message; ?></p>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <div class="row">
                <div class="col-lg-6">
                    <div class="card shadow-sm">
                        <div class="card-body">
                            <h5 class="card-title mb-4">
                                <i class="bi bi-file-earmark-plus me-2"></i>
                                Masukkan Data Statistik Diabetes
                            </h5>
                            <form method="POST">
                                <div class="mb-3">
                                    <label for="wilayah" class="form-label">Wilayah</label>
                                    <div class="input-group">
                                        <span class="input-group-text">
                                            <i class="bi bi-geo-alt"></i>
                                        </span>
                                        <input type="text" class="form-control" id="wilayah" name="wilayah" required>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="jumlah_penduduk" class="form-label">Jumlah Penduduk</label>
                                    <div class="input-group">
                                        <span class="input-group-text">
                                            <i class="bi bi-people"></i>
                                        </span>
                                        <input type="number" class="form-control" id="jumlah_penduduk" name="jumlah_penduduk" required>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="tahun" class="form-label">Tahun</label>
                                    <div class="input-group">
                                        <span class="input-group-text">
                                            <i class="bi bi-calendar"></i>
                                        </span>
                                        <input type="number" class="form-control" id="tahun" name="tahun" 
                                               min="2000" max="<?php echo date('Y'); ?>" required>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="jumlah_penderita" class="form-label">Jumlah Penderita</label>
                                    <div class="input-group">
                                        <span class="input-group-text">
                                            <i class="bi bi-heart-pulse"></i>
                                        </span>
                                        <input type="number" class="form-control" id="jumlah_penderita" name="jumlah_penderita" required>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="jumlah_kematian" class="form-label">Jumlah Kematian</label>
                                    <div class="input-group">
                                        <span class="input-group-text">
                                            <i class="bi bi-heart"></i>
                                        </span>
                                        <input type="number" class="form-control" id="jumlah_kematian" name="jumlah_kematian" required>
                                    </div>
                                </div>
                                
                                <div class="d-grid gap-2">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-check-lg me-2"></i>Submit
                                    </button>
                                    <button type="reset" class="btn btn-secondary">
                                        <i class="bi bi-arrow-counterclockwise me-2"></i>Reset
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-6">
                    <div class="card shadow-sm">
                        <div class="card-body">
                            <h5 class="card-title mb-3">
                                <i class="bi bi-info-circle me-2"></i>
                                Panduan Input Data
                            </h5>
                            <div class="alert alert-info">
                                <h6 class="alert-heading">Format Data yang Benar:</h6>
                                <ul class="mb-0">
                                    <li><strong>Wilayah:</strong> Nama provinsi atau kabupaten</li>
                                    <li><strong>Jumlah Penduduk:</strong> Total populasi wilayah</li>
                                    <li><strong>Tahun:</strong> Tahun data (2000-sekarang)</li>
                                    <li><strong>Jumlah Penderita:</strong> Total kasus diabetes</li>
                                    <li><strong>Jumlah Kematian:</strong> Total kematian akibat diabetes</li>
                                </ul>
                            </div>
                            <p class="text-muted">
                                Data yang dimasukkan akan digunakan untuk analisis clustering menggunakan
                                algoritma DBSCAN untuk mengidentifikasi pola penyebaran diabetes.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<?php include '../includes/footer.php'; ?>