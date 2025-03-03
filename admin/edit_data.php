<?php
/**
 * Edit Data Page
 * 
 * This file handles editing a single diabetes data record.
 * It displays a form with the current data and processes form submissions to update the database.
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

// Check if ID is provided in URL
if (!isset($_GET['id'])) {
    header("Location: delete_data.php");
    exit();
}

$id = $_GET['id'];

// Handle form submission for updating
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get form data
    $id = $_POST['id'];
    $wilayah = $_POST['wilayah'];
    $jumlah_penduduk = $_POST['jumlah_penduduk']; 
    $tahun = $_POST['tahun'];
    $jumlah_penderita = $_POST['jumlah_penderita'];
    $jumlah_kematian = $_POST['jumlah_kematian'];

    try {
        // Update the database
        $stmt = $pdo->prepare("UPDATE diabetes_data SET wilayah = ?, jumlah_penduduk = ?, tahun = ?, jumlah_penderita = ?, jumlah_kematian = ? WHERE id = ?");
        $stmt->execute([$wilayah, $jumlah_penduduk, $tahun, $jumlah_penderita, $jumlah_kematian, $id]);
        $success_message = "Data updated successfully!";
        
        // Refresh data after update
        $stmt = $pdo->prepare("SELECT * FROM diabetes_data WHERE id = ?");
        $stmt->execute([$id]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $error_message = "Error updating record: " . $e->getMessage();
    }
} else {
    // Fetch the record data for display in the form
    $stmt = $pdo->prepare("SELECT * FROM diabetes_data WHERE id = ?");
    $stmt->execute([$id]);
    $data = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$data) {
        $error_message = "Record not found.";
    }
}

// Include header and sidebar
include '../includes/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <?php include '../includes/sidebar.php'; ?>

        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Edit Data</h1>
                <a href="delete_data.php" class="btn btn-secondary">
                    <i class="bi bi-arrow-left me-2"></i>Back to List
                </a>
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

            <?php if (isset($data)): ?>
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title mb-4">
                            <i class="bi bi-pencil-square me-2"></i>
                            Edit Record #<?php echo htmlspecialchars($data['id']); ?>
                        </h5>
                        
                        <form method="POST">
                            <input type="hidden" name="id" value="<?php echo htmlspecialchars($data['id']); ?>">
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="wilayah" class="form-label">Wilayah</label>
                                    <div class="input-group">
                                        <span class="input-group-text">
                                            <i class="bi bi-geo-alt"></i>
                                        </span>
                                        <input type="text" class="form-control" id="wilayah" name="wilayah" 
                                            value="<?php echo htmlspecialchars($data['wilayah']); ?>" required>
                                    </div>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="jumlah_penduduk" class="form-label">Jumlah Penduduk</label>
                                    <div class="input-group">
                                        <span class="input-group-text">
                                            <i class="bi bi-people"></i>
                                        </span>
                                        <input type="number" class="form-control" id="jumlah_penduduk" name="jumlah_penduduk" 
                                            value="<?php echo htmlspecialchars($data['jumlah_penduduk'] ?? 0); ?>" required>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label for="tahun" class="form-label">Tahun</label>
                                    <div class="input-group">
                                        <span class="input-group-text">
                                            <i class="bi bi-calendar"></i>
                                        </span>
                                        <input type="number" class="form-control" id="tahun" name="tahun" 
                                            value="<?php echo htmlspecialchars($data['tahun']); ?>" required
                                            min="2000" max="<?php echo date('Y'); ?>">
                                    </div>
                                </div>
                                
                                <div class="col-md-4 mb-3">
                                    <label for="jumlah_penderita" class="form-label">Jumlah Penderita</label>
                                    <div class="input-group">
                                        <span class="input-group-text">
                                            <i class="bi bi-heart-pulse"></i>
                                        </span>
                                        <input type="number" class="form-control" id="jumlah_penderita" name="jumlah_penderita" 
                                            value="<?php echo htmlspecialchars($data['jumlah_penderita']); ?>" required>
                                    </div>
                                </div>
                                
                                <div class="col-md-4 mb-3">
                                    <label for="jumlah_kematian" class="form-label">Jumlah Kematian</label>
                                    <div class="input-group">
                                        <span class="input-group-text">
                                            <i class="bi bi-heart"></i>
                                        </span>
                                        <input type="number" class="form-control" id="jumlah_kematian" name="jumlah_kematian" 
                                            value="<?php echo htmlspecialchars($data['jumlah_kematian']); ?>" required>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <div class="card bg-light">
                                        <div class="card-body">
                                            <h6 class="card-subtitle mb-2 text-muted">Cluster Assignment</h6>
                                            <p class="card-text">
                                                <span class="badge bg-<?php 
                                                    switch ((int)($data['cluster'] ?? -1)) {
                                                        case 0: echo 'success'; break;
                                                        case 1: echo 'warning'; break;
                                                        case 2: echo 'danger'; break;
                                                        default: echo 'secondary';
                                                    }
                                                ?>">
                                                    <?php echo isset($data['cluster']) ? htmlspecialchars($data['cluster']) : 'Not Assigned'; ?>
                                                </span>
                                                <small class="text-muted ms-2">
                                                    (Cluster assignment is managed by the DBSCAN algorithm)
                                                </small>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="card bg-light">
                                        <div class="card-body">
                                            <h6 class="card-subtitle mb-2 text-muted">Created Date</h6>
                                            <p class="card-text">
                                                <i class="bi bi-clock me-2"></i>
                                                <?php echo isset($data['created_at']) ? date('d M Y H:i', strtotime($data['created_at'])) : 'Not available'; ?>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="d-flex justify-content-between">
                                <a href="delete_data.php" class="btn btn-secondary">
                                    <i class="bi bi-x-circle me-2"></i>Cancel
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-check-circle me-2"></i>Update Record
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            <?php else: ?>
                <div class="alert alert-warning">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    Record not found or could not be loaded.
                </div>
                <div class="d-grid gap-2 col-6 mx-auto mt-3">
                    <a href="delete_data.php" class="btn btn-primary">
                        <i class="bi bi-arrow-left me-2"></i>Return to Data List
                    </a>
                </div>
            <?php endif; ?>
        </main>
    </div>
</div>

<?php include '../includes/footer.php'; ?>