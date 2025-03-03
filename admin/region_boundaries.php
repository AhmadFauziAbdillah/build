<?php
/**
 * Region Boundaries Manager
 * 
 * File ini menangani pemetaan batas wilayah GeoJSON ke wilayah database
 * untuk Dashboard Clustering Diabetes.
 */
session_start();
require_once '../config/database.php';

// Periksa apakah pengguna sudah login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$success_message = '';
$error_message = '';

// Tangani pengiriman formulir
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['upload_geojson'])) {
        // Menangani unggahan file GeoJSON
        if (isset($_FILES['geojson_file']) && $_FILES['geojson_file']['error'] == 0) {
            $file_tmp = $_FILES['geojson_file']['tmp_name'];
            $file_content = file_get_contents($file_tmp);
            
            if ($file_content === false) {
                $error_message = "Error membaca file yang diunggah";
            } else {
                $geojson = json_decode($file_content, true);
                
                if (json_last_error() !== JSON_ERROR_NONE) {
                    $error_message = "Format GeoJSON tidak valid: " . json_last_error_msg();
                } else {
                    // Proses data GeoJSON
                    try {
                        $mappedGeoJSON = mapGeoJSONToRegions($geojson, $pdo);
                        
                        // Simpan GeoJSON yang telah diproses
                        $save_path = '../data/processed_boundaries.geojson';
                        $saved = file_put_contents($save_path, json_encode($mappedGeoJSON, JSON_PRETTY_PRINT));
                        
                        if ($saved !== false) {
                            $success_message = "GeoJSON berhasil diproses dan disimpan. " .
                                              "Berhasil memetakan " . count($mappedGeoJSON['features']) . " wilayah.";
                        } else {
                            $error_message = "Error menyimpan GeoJSON yang diproses";
                        }
                    } catch (Exception $e) {
                        $error_message = "Error memproses GeoJSON: " . $e->getMessage();
                    }
                }
            }
        } else {
            $error_message = "Silakan pilih file GeoJSON yang valid";
        }
    } elseif (isset($_POST['update_mapping'])) {
        // Menangani pembaruan pemetaan manual
        $geojson_name = $_POST['geojson_name'];
        $database_region = $_POST['database_region'];
        
        try {
            $stmt = $pdo->prepare("INSERT INTO region_mappings (geojson_name, database_region) 
                                  VALUES (?, ?) 
                                  ON DUPLICATE KEY UPDATE database_region = ?");
            $stmt->execute([$geojson_name, $database_region, $database_region]);
            $success_message = "Pemetaan wilayah berhasil diperbarui";
        } catch (PDOException $e) {
            $error_message = "Error memperbarui pemetaan: " . $e->getMessage();
        }
    }
}

/**
 * Memetakan properti GeoJSON ke wilayah database
 * 
 * @param array $geoJSON Data GeoJSON sebagai array asosiatif
 * @param PDO $pdo Koneksi database
 * @return array Data GeoJSON yang telah dipetakan
 */
function mapGeoJSONToRegions($geoJSON, $pdo) {
    // Pertama, buat tabel region_mappings jika belum ada
    createMappingTableIfNotExists($pdo);
    
    $features = $geoJSON['features'];
    $mapped = [];
    
    // Dapatkan semua nama wilayah dari database
    $stmt = $pdo->query("SELECT wilayah FROM diabetes_data GROUP BY wilayah");
    $dbRegions = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    // Dapatkan pemetaan yang sudah ada
    $stmt = $pdo->query("SELECT geojson_name, database_region FROM region_mappings");
    $existingMappings = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $existingMappings[$row['geojson_name']] = $row['database_region'];
    }
    
    foreach ($features as $feature) {
        // Properti mungkin disebut berbeda dalam GeoJSON Anda
        // Periksa nama properti umum yang digunakan untuk identifikasi wilayah
        $geoJSONName = null;
        $possibleProps = ['name', 'NAMAPROP', 'NAME_1', 'wilayah', 'NAMA', 'NAME', 'PROVINSI'];
        
        foreach ($possibleProps as $prop) {
            if (isset($feature['properties'][$prop])) {
                $geoJSONName = $feature['properties'][$prop];
                break;
            }
        }
        
        $bestMatch = null;
        
        // Periksa pemetaan yang sudah ada terlebih dahulu
        if ($geoJSONName && isset($existingMappings[$geoJSONName])) {
            $bestMatch = $existingMappings[$geoJSONName];
        } elseif ($geoJSONName) {
            // Kecocokan langsung
            if (in_array($geoJSONName, $dbRegions)) {
                $bestMatch = $geoJSONName;
            } else {
                // Coba pencocokan fuzzy
                foreach ($dbRegions as $region) {
                    // Konversi ke huruf kecil dan hapus spasi untuk perbandingan
                    $normalizedGeoName = strtolower(str_replace(' ', '', $geoJSONName));
                    $normalizedDbName = strtolower(str_replace(' ', '', $region));
                    
                    if ($normalizedGeoName == $normalizedDbName || 
                        strpos($normalizedGeoName, $normalizedDbName) !== false ||
                        strpos($normalizedDbName, $normalizedGeoName) !== false) {
                        $bestMatch = $region;
                        break;
                    }
                }
            }
            
            // Simpan pemetaan untuk penggunaan di masa mendatang
            if ($bestMatch && !isset($existingMappings[$geoJSONName])) {
                try {
                    $stmt = $pdo->prepare("INSERT INTO region_mappings (geojson_name, database_region) VALUES (?, ?)");
                    $stmt->execute([$geoJSONName, $bestMatch]);
                } catch (PDOException $e) {
                    // Abaikan duplikat
                }
            }
        }
        
        // Perbarui properti feature dengan nama wilayah yang benar
        if ($bestMatch) {
            $feature['properties']['name'] = $bestMatch;
            $feature['properties']['wilayah'] = $bestMatch;
        }
        
        $mapped[] = $feature;
    }
    
    $geoJSON['features'] = $mapped;
    return $geoJSON;
}

/**
 * Membuat tabel region_mappings jika belum ada
 * 
 * @param PDO $pdo Koneksi database
 */
function createMappingTableIfNotExists($pdo) {
    $pdo->exec("CREATE TABLE IF NOT EXISTS region_mappings (
        id INT AUTO_INCREMENT PRIMARY KEY,
        geojson_name VARCHAR(100) NOT NULL,
        database_region VARCHAR(100) NOT NULL,
        UNIQUE KEY (geojson_name)
    )");
}

// Ambil pemetaan yang ada untuk ditampilkan
$mappings = [];
try {
    $stmt = $pdo->query("SELECT * FROM region_mappings ORDER BY geojson_name");
    $mappings = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // Tabel mungkin belum ada, buat terlebih dahulu
    createMappingTableIfNotExists($pdo);
}

// Ambil semua wilayah database untuk dropdown
$dbRegions = [];
try {
    $stmt = $pdo->query("SELECT DISTINCT wilayah FROM diabetes_data ORDER BY wilayah");
    $dbRegions = $stmt->fetchAll(PDO::FETCH_COLUMN);
} catch (PDOException $e) {
    $error_message = "Error mengambil wilayah: " . $e->getMessage();
}

// Periksa apakah file yang diproses sudah ada
$processed_file_exists = file_exists('../data/processed_boundaries.geojson');

include '../includes/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <?php include '../includes/sidebar.php'; ?>

        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Kelola Batas Wilayah</h1>
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

            <div class="row">
                <!-- GeoJSON Upload Card -->
                <div class="col-lg-6 mb-4">
                    <div class="card shadow-sm">
                        <div class="card-body">
                            <h5 class="card-title mb-3">
                                <i class="bi bi-upload me-2"></i>
                                Upload File GeoJSON
                            </h5>
                            
                            <form method="POST" enctype="multipart/form-data">
                                <div class="mb-3">
                                    <label for="geojson_file" class="form-label">File GeoJSON</label>
                                    <div class="input-group">
                                        <input type="file" class="form-control" id="geojson_file" name="geojson_file" 
                                               accept=".geojson,.json" required>
                                        <button type="submit" name="upload_geojson" class="btn btn-primary">
                                            <i class="bi bi-upload me-1"></i> Upload
                                        </button>
                                    </div>
                                    <div class="form-text">Upload file GeoJSON yang berisi batas-batas wilayah.</div>
                                </div>
                            </form>
                            
                            <?php if ($processed_file_exists): ?>
                                <div class="alert alert-info">
                                    <i class="bi bi-info-circle me-2"></i>
                                    File GeoJSON sudah diproses dan tersedia untuk digunakan dalam peta.
                                </div>
                                <div class="d-grid gap-2">
                                    <a href="../data/processed_boundaries.geojson" class="btn btn-outline-primary" download>
                                        <i class="bi bi-download me-1"></i> Download Processed GeoJSON
                                    </a>
                                </div>
                            <?php endif; ?>
                            
                            <div class="mt-4">
                                <h6 class="mb-2">Struktur GeoJSON yang Diharapkan:</h6>
                                <pre class="bg-light p-3 rounded small">
{
  "type": "FeatureCollection",
  "features": [
    {
      "type": "Feature",
      "properties": {
        "name": "Nama Wilayah"
      },
      "geometry": {
        "type": "Polygon",
        "coordinates": [[[lng, lat], ...]]
      }
    },
    ...
  ]
}
                                </pre>
                            </div>
                            
                            <div class="mt-3">
                                <h6 class="mb-2">Sumber Data GeoJSON:</h6>
                                <ul>
                                    <li>Badan Informasi Geospasial (BIG): <a href="https://tanahair.indonesia.go.id" target="_blank">tanahair.indonesia.go.id</a></li>
                                    <li>OpenStreetMap: <a href="https://openstreetmap.org" target="_blank">openstreetmap.org</a></li>
                                    <li>GADM: <a href="https://gadm.org" target="_blank">gadm.org</a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Manual Mapping Card -->
                <div class="col-lg-6">
                    <div class="card shadow-sm">
                        <div class="card-body">
                            <h5 class="card-title mb-3">
                                <i class="bi bi-link me-2"></i>
                                Pemetaan Nama Wilayah
                            </h5>
                            
                            <form method="POST" class="mb-4">
                                <div class="row g-3">
                                    <div class="col-md-5">
                                        <label for="geojson_name" class="form-label">Nama di GeoJSON</label>
                                        <input type="text" class="form-control" id="geojson_name" name="geojson_name" 
                                               required placeholder="Nama di file GeoJSON">
                                    </div>
                                    
                                    <div class="col-md-5">
                                        <label for="database_region" class="form-label">Nama di Database</label>
                                        <select class="form-select" id="database_region" name="database_region" required>
                                            <option value="">-- Pilih Wilayah --</option>
                                            <?php foreach ($dbRegions as $region): ?>
                                                <option value="<?php echo htmlspecialchars($region); ?>">
                                                    <?php echo htmlspecialchars($region); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    
                                    <div class="col-md-2 d-flex align-items-end">
                                        <button type="submit" name="update_mapping" class="btn btn-primary w-100">
                                            <i class="bi bi-save me-1"></i> Simpan
                                        </button>
                                    </div>
                                </div>
                            </form>
                            
                            <h6 class="mt-4 mb-3">Daftar Pemetaan Wilayah</h6>
                            
                            <?php if (count($mappings) > 0): ?>
                                <div class="table-responsive">
                                    <table class="table table-hover table-striped">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Nama di GeoJSON</th>
                                                <th>Nama di Database</th>
                                                <th>Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($mappings as $mapping): ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars($mapping['geojson_name']); ?></td>
                                                    <td><?php echo htmlspecialchars($mapping['database_region']); ?></td>
                                                    <td>
                                                        <button class="btn btn-sm btn-primary edit-mapping"
                                                                data-geojson="<?php echo htmlspecialchars($mapping['geojson_name']); ?>"
                                                                data-region="<?php echo htmlspecialchars($mapping['database_region']); ?>">
                                                            <i class="bi bi-pencil"></i>
                                                        </button>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php else: ?>
                                <div class="alert alert-warning">
                                    <i class="bi bi-exclamation-triangle me-2"></i>
                                    Belum ada pemetaan wilayah. Upload file GeoJSON atau tambahkan pemetaan secara manual.
                                </div>
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
    // Handle edit mapping button clicks
    const editButtons = document.querySelectorAll('.edit-mapping');
    
    editButtons.forEach(button => {
        button.addEventListener('click', function() {
            const geoJSONName = this.getAttribute('data-geojson');
            const dbRegion = this.getAttribute('data-region');
            
            document.getElementById('geojson_name').value = geoJSONName;
            
            const selectElement = document.getElementById('database_region');
            for (let i = 0; i < selectElement.options.length; i++) {
                if (selectElement.options[i].value === dbRegion) {
                    selectElement.selectedIndex = i;
                    break;
                }
            }
            
            // Scroll to the form
            document.querySelector('form').scrollIntoView({ behavior: 'smooth' });
        });
    });
});
</script>

<?php include '../includes/footer.php'; ?>