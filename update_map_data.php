<?php
/**
 * Pembaruan Data Peta
 * 
 * Script ini memperbarui data peta dengan memproses file GeoJSON mentah
 * dan menerapkan pemetaan wilayah. Script ini dapat dijalankan via cron job.
 */
require_once 'config/database.php';

// Tidak ada akses langsung melalui browser
if (php_sapi_name() != 'cli') {
    die("Script ini hanya dapat dijalankan dari command line.");
}

// Periksa apakah file GeoJSON mentah ada
$raw_file = 'data/raw_boundaries.geojson';
if (!file_exists($raw_file)) {
    die("File GeoJSON mentah tidak ditemukan di $raw_file\n");
}

echo "Membaca file GeoJSON mentah...\n";
$raw_content = file_get_contents($raw_file);
$raw_geojson = json_decode($raw_content, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    die("Format GeoJSON tidak valid: " . json_last_error_msg() . "\n");
}

// Buat tabel region_mappings jika belum ada
function createMappingTableIfNotExists($pdo) {
    $pdo->exec("CREATE TABLE IF NOT EXISTS region_mappings (
        id INT AUTO_INCREMENT PRIMARY KEY,
        geojson_name VARCHAR(100) NOT NULL,
        database_region VARCHAR(100) NOT NULL,
        UNIQUE KEY (geojson_name)
    )");
}

createMappingTableIfNotExists($pdo);

// Dapatkan semua nama wilayah dari database
echo "Mengambil wilayah dari database...\n";
$stmt = $pdo->query("SELECT wilayah FROM diabetes_data GROUP BY wilayah");
$dbRegions = $stmt->fetchAll(PDO::FETCH_COLUMN);

// Dapatkan pemetaan yang sudah ada
$stmt = $pdo->query("SELECT geojson_name, database_region FROM region_mappings");
$existingMappings = [];
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $existingMappings[$row['geojson_name']] = $row['database_region'];
}

echo "Memproses " . count($raw_geojson['features']) . " fitur...\n";
$mapped_features = [];
$mapped_count = 0;
$unmapped_count = 0;

foreach ($raw_geojson['features'] as $feature) {
    // Ekstrak nama wilayah dari properti
    $geoJSONName = null;
    $possibleProps = ['name', 'NAMAPROP', 'NAME_1', 'wilayah', 'NAMA', 'NAME', 'PROVINSI'];
    
    foreach ($possibleProps as $prop) {
        if (isset($feature['properties'][$prop])) {
            $geoJSONName = $feature['properties'][$prop];
            break;
        }
    }
    
    $bestMatch = null;
    
    // Periksa pemetaan yang sudah ada
    if ($geoJSONName && isset($existingMappings[$geoJSONName])) {
        $bestMatch = $existingMappings[$geoJSONName];
    } elseif ($geoJSONName) {
        // Kecocokan langsung
        if (in_array($geoJSONName, $dbRegions)) {
            $bestMatch = $geoJSONName;
        } else {
            // Coba pencocokan fuzzy
            foreach ($dbRegions as $region) {
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
                echo "Menambahkan pemetaan: $geoJSONName -> $bestMatch\n";
            } catch (PDOException $e) {
                // Abaikan duplikat
            }
        }
    }
    
    // Perbarui properti feature
    if ($bestMatch) {
        $feature['properties']['name'] = $bestMatch;
        $feature['properties']['wilayah'] = $bestMatch;
        $mapped_count++;
    } else {
        $unmapped_count++;
        echo "Tidak dapat memetakan: " . ($geoJSONName ?? "Tidak diketahui") . "\n";
    }
    
    $mapped_features[] = $feature;
}

$raw_geojson['features'] = $mapped_features;

// Simpan GeoJSON yang telah diproses
$output_file = 'data/processed_boundaries.geojson';
$success = file_put_contents($output_file, json_encode($raw_geojson, JSON_PRETTY_PRINT));

if ($success !== false) {
    echo "Berhasil! GeoJSON yang diproses disimpan ke $output_file\n";
    echo "Terpetakan: $mapped_count, Tidak terpetakan: $unmapped_count\n";
} else {
    echo "Error menyimpan GeoJSON yang diproses\n";
}