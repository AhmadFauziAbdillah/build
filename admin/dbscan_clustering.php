<?php
/**
 * DBSCAN Clustering Implementation
 * 
 * This script implements the DBSCAN (Density-Based Spatial Clustering of Applications with Noise)
 * algorithm for clustering diabetes data. It now incorporates population data alongside patient 
 * and mortality counts for more comprehensive clustering analysis.
 */

session_start();
require_once '../config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Initialize messages
$success_message = '';
$error_message = '';

// Fetch available years for the dropdown
$stmt = $pdo->query("SELECT DISTINCT tahun FROM diabetes_data ORDER BY tahun DESC");
$available_years = $stmt->fetchAll(PDO::FETCH_COLUMN);

// Fetch clustering history for display
$history_stmt = $pdo->query("SELECT * FROM clustering_results ORDER BY date_generated DESC LIMIT 5");
$cluster_history = $history_stmt->fetchAll();

// Process DBSCAN when form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $eps = floatval($_POST['eps']);
    $min_samples = intval($_POST['min_samples']);
    $selected_year = $_POST['selected_year'];

    try {
        // Start execution timer
        $start_time = microtime(true);
        
        // Fetch data for the selected year
        $stmt = $pdo->prepare("SELECT id, wilayah, jumlah_penduduk, jumlah_penderita, jumlah_kematian 
                              FROM diabetes_data 
                              WHERE tahun = ?");
        $stmt->execute([$selected_year]);
        $raw_data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Validate data
        if (empty($raw_data)) {
            throw new Exception("No data available for the selected year $selected_year.");
        }
        
        // Count total data points
        $data_points_count = count($raw_data);
        
        // Find maximum values for normalization
        $max_penduduk = max(array_map(function($item) { 
            return isset($item['jumlah_penduduk']) ? $item['jumlah_penduduk'] : 0; 
        }, $raw_data));
        
        $max_penderita = max(array_column($raw_data, 'jumlah_penderita'));
        $max_kematian = max(array_column($raw_data, 'jumlah_kematian'));
        
        // Prevent division by zero
        $max_penduduk = $max_penduduk > 0 ? $max_penduduk : 1;
        $max_penderita = $max_penderita > 0 ? $max_penderita : 1;
        $max_kematian = $max_kematian > 0 ? $max_kematian : 1;
        
        // Prepare normalized data for DBSCAN
        $normalized_data = [];
        foreach ($raw_data as $row) {
            $normalized_data[] = [
                'id' => $row['id'],
                'wilayah' => $row['wilayah'],
                'jumlah_penduduk' => $row['jumlah_penduduk'] ?? 0,
                'jumlah_penderita' => $row['jumlah_penderita'],
                'jumlah_kematian' => $row['jumlah_kematian'],
                'normalized_penduduk' => isset($row['jumlah_penduduk']) ? $row['jumlah_penduduk'] / $max_penduduk : 0,
                'normalized_penderita' => $row['jumlah_penderita'] / $max_penderita,
                'normalized_kematian' => $row['jumlah_kematian'] / $max_kematian
            ];
        }
        
        // Run DBSCAN clustering
        $clusters = performDBSCAN($normalized_data, $eps, $min_samples);
        
        // Update cluster assignments in database
        $update_stmt = $pdo->prepare("UPDATE diabetes_data SET cluster = ? WHERE wilayah = ? AND tahun = ?");
        
        foreach ($clusters as $wilayah => $cluster) {
            $update_stmt->execute([$cluster, $wilayah, $selected_year]);
        }
        
        // Calculate statistics
        $cluster_count = count(array_filter(array_unique($clusters), function($cluster) { 
            return $cluster > 0; 
        }));
        
        // Count outliers (points with cluster = 0)
        $outliers_count = count(array_filter($clusters, function($cluster) { 
            return $cluster === 0; 
        }));
        
        // Calculate execution time
        $execution_time = round(microtime(true) - $start_time, 4);
        
        // Save clustering results to database
        $save_stmt = $pdo->prepare("INSERT INTO clustering_results 
            (cluster_count, epsilon, min_points, data_points, outliers, execution_time, date_generated) 
            VALUES (?, ?, ?, ?, ?, ?, NOW())");
            
        $save_stmt->execute([
            $cluster_count,
            $eps,
            $min_samples,
            $data_points_count,
            $outliers_count,
            $execution_time
        ]);
        
        // Create success message
        $success_message = "DBSCAN clustering completed successfully for year $selected_year! ".
                          "Number of clusters: $cluster_count, Outliers: $outliers_count, ".
                          "Execution time: {$execution_time}s";
                          
        // Refresh clustering history
        $history_stmt = $pdo->query("SELECT * FROM clustering_results ORDER BY date_generated DESC LIMIT 5");
        $cluster_history = $history_stmt->fetchAll();
        
    } catch (Exception $e) {
        $error_message = "Error: " . $e->getMessage();
    }
}

/**
 * DBSCAN Algorithm Implementation
 * 
 * Clusters data points based on density.
 * 
 * @param array $data The data to cluster
 * @param float $eps The maximum distance between two points to be considered neighbors
 * @param int $min_samples The minimum number of points required to form a dense region
 * @return array Mapping of point wilayah to cluster ID
 */
function performDBSCAN($data, $eps, $min_samples) {
    // Initialize clusters array
    $clusters = [];
    $cluster_id = 0;
    $processed = [];

    // Main DBSCAN loop
    foreach ($data as $index => $point) {
        $wilayah = $point['wilayah'];
        
        // Skip already processed points
        if (isset($processed[$wilayah])) continue;
        $processed[$wilayah] = true;

        // Find neighbors
        $neighbors = findNeighbors($data, $point, $eps);
        
        // Check if this point is a core point
        if (count($neighbors) < $min_samples) {
            $clusters[$wilayah] = 0; // Mark as noise point
        } else {
            // Start a new cluster
            $cluster_id++;
            $clusters[$wilayah] = $cluster_id;
            
            // Expand cluster
            expandCluster($data, $neighbors, $clusters, $processed, $cluster_id, $eps, $min_samples);
        }
    }

    // If all points are noise, assign them to a single cluster
    if (count(array_unique($clusters)) === 1 && reset($clusters) === 0) {
        foreach ($clusters as &$cluster) {
            $cluster = 1;
        }
    }

    return $clusters;
}

/**
 * Find neighboring points within epsilon distance
 * 
 * @param array $data All data points
 * @param array $point The reference point
 * @param float $eps Maximum distance to consider as neighbors
 * @return array List of neighbor points
 */
function findNeighbors($data, $point, $eps) {
    $neighbors = [];
    
    foreach ($data as $other_point) {
        // Skip self comparisons
        if ($point['wilayah'] === $other_point['wilayah']) continue;
        
        // Calculate Euclidean distance in 3D space (population, patients, deaths)
        $distance = sqrt(
            pow($point['normalized_penduduk'] - $other_point['normalized_penduduk'], 2) +
            pow($point['normalized_penderita'] - $other_point['normalized_penderita'], 2) +
            pow($point['normalized_kematian'] - $other_point['normalized_kematian'], 2)
        );
        
        // Add to neighbors if within epsilon distance
        if ($distance <= $eps) {
            $neighbors[] = $other_point;
        }
    }
    
    return $neighbors;
}

/**
 * Expand a cluster by adding all density-connected points
 * 
 * @param array $data All data points
 * @param array $neighbors Initial neighbors to process
 * @param array &$clusters Reference to clusters mapping
 * @param array &$processed Reference to processed points tracking
 * @param int $cluster_id Current cluster ID
 * @param float $eps Maximum distance to consider as neighbors
 * @param int $min_samples Minimum points required to form a dense region
 */
function expandCluster($data, $neighbors, &$clusters, &$processed, $cluster_id, $eps, $min_samples) {
    // Process all neighbors
    $i = 0;
    while ($i < count($neighbors)) {
        $neighbor = $neighbors[$i];
        $wilayah = $neighbor['wilayah'];
        
        // Check if point was already processed
        if (!isset($processed[$wilayah])) {
            $processed[$wilayah] = true;
            
            // Find neighbors of this point
            $new_neighbors = findNeighbors($data, $neighbor, $eps);
            
            // If this is a core point, add its neighbors to be processed
            if (count($new_neighbors) >= $min_samples) {
                foreach ($new_neighbors as $new_neighbor) {
                    if (!in_array($new_neighbor, $neighbors)) {
                        $neighbors[] = $new_neighbor;
                    }
                }
            }
        }
        
        // Assign to cluster if not already assigned
        if (!isset($clusters[$wilayah])) {
            $clusters[$wilayah] = $cluster_id;
        }
        
        $i++;
    }
}

include '../includes/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <?php include '../includes/sidebar.php'; ?>

        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">DBSCAN Clustering</h1>
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
                <div class="col-lg-6">
                    <!-- DBSCAN Parameters Form -->
                    <div class="card shadow-sm mb-4">
                        <div class="card-body">
                            <h5 class="card-title mb-4">
                                <i class="bi bi-sliders me-2"></i>
                                DBSCAN Parameters
                            </h5>
                            <form method="POST" class="needs-validation" novalidate>
                                <!-- Year Selection -->
                                <div class="mb-3">
                                    <label for="selected_year" class="form-label">Select Year</label>
                                    <select class="form-select" id="selected_year" name="selected_year" required>
                                        <?php foreach ($available_years as $year): ?>
                                            <option value="<?php echo htmlspecialchars($year); ?>">
                                                <?php echo htmlspecialchars($year); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <div class="form-text">Choose the year for which you want to cluster data.</div>
                                </div>
                                
                                <!-- Epsilon Parameter -->
                                <div class="mb-3">
                                    <label for="eps" class="form-label">Epsilon (eps)</label>
                                    <div class="input-group">
                                        <input type="number" step="0.01" min="0.01" max="2.0" class="form-control" 
                                               id="eps" name="eps" value="0.5" required>
                                        <span class="input-group-text"><i class="bi bi-arrows-expand"></i></span>
                                    </div>
                                    <div class="form-text">Distance threshold for neighbors (0.01-2.0). Higher values create larger clusters.</div>
                                </div>
                                
                                <!-- Minimum Samples Parameter -->
                                <div class="mb-3">
                                    <label for="min_samples" class="form-label">Minimum Samples</label>
                                    <div class="input-group">
                                        <input type="number" min="1" class="form-control" 
                                               id="min_samples" name="min_samples" value="2" required>
                                        <span class="input-group-text"><i class="bi bi-people"></i></span>
                                    </div>
                                    <div class="form-text">Minimum number of points to form a cluster. Higher values create fewer clusters.</div>
                                </div>
                                
                                <!-- Submit Button -->
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="bi bi-play-fill me-2"></i>Run DBSCAN Clustering
                                </button>
                            </form>
                        </div>
                    </div>
                    
                    <!-- DBSCAN Information Card -->
                    <div class="card shadow-sm">
                        <div class="card-body">
                            <h5 class="card-title mb-3">
                                <i class="bi bi-info-circle me-2"></i>
                                About DBSCAN Algorithm
                            </h5>
                            <p>DBSCAN (Density-Based Spatial Clustering of Applications with Noise) is a clustering algorithm that groups together points that are closely packed together, marking as outliers points that lie alone in low-density regions.</p>
                            
                            <h6 class="mt-3 mb-2">Key Parameters:</h6>
                            <ul>
                                <li><strong>Epsilon (eps)</strong>: The maximum distance between two points for them to be considered neighbors.</li>
                                <li><strong>Minimum Samples</strong>: The minimum number of points required to form a dense region (cluster).</li>
                            </ul>
                            
                            <h6 class="mt-3 mb-2">Data Used for Clustering:</h6>
                            <ul>
                                <li><strong>Population (Jumlah Penduduk)</strong>: Total population in the region</li>
                                <li><strong>Patient Count (Jumlah Penderita)</strong>: Number of diabetes patients</li>
                                <li><strong>Mortality (Jumlah Kematian)</strong>: Number of deaths due to diabetes</li>
                            </ul>
                            
                            <h6 class="mt-3 mb-2">Cluster Interpretation:</h6>
                            <div class="d-flex mt-2 align-items-center">
                                <span class="status-badge bg-success me-2">Cluster 0</span>
                                <span>Lower severity (outliers or noise points)</span>
                            </div>
                            <div class="d-flex mt-2 align-items-center">
                                <span class="status-badge bg-warning me-2">Cluster 1</span>
                                <span>Medium severity</span>
                            </div>
                            <div class="d-flex mt-2 align-items-center">
                                <span class="status-badge bg-danger me-2">Cluster 2</span>
                                <span>Higher severity</span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Clustering History -->
                <div class="col-lg-6">
                    <div class="card shadow-sm">
                        <div class="card-body">
                            <h5 class="card-title mb-4">
                                <i class="bi bi-clock-history me-2"></i>
                                Recent Clustering History
                            </h5>
                            
                            <?php if (count($cluster_history) > 0): ?>
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Date</th>
                                                <th>Clusters</th>
                                                <th>Epsilon</th>
                                                <th>Min Points</th>
                                                <th>Data Points</th>
                                                <th>Outliers</th>
                                                <th>Time (s)</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($cluster_history as $history): ?>
                                                <tr>
                                                    <td><?php echo date('Y-m-d H:i', strtotime($history['date_generated'])); ?></td>
                                                    <td>
                                                        <span class="badge bg-primary"><?php echo htmlspecialchars($history['cluster_count']); ?></span>
                                                    </td>
                                                    <td><?php echo htmlspecialchars($history['epsilon']); ?></td>
                                                    <td><?php echo htmlspecialchars($history['min_points']); ?></td>
                                                    <td><?php echo htmlspecialchars($history['data_points']); ?></td>
                                                    <td><?php echo htmlspecialchars($history['outliers']); ?></td>
                                                    <td><?php echo htmlspecialchars($history['execution_time']); ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php else: ?>
                                <div class="text-center py-4">
                                    <i class="bi bi-exclamation-circle text-muted display-6 d-block mb-3"></i>
                                    <p class="text-muted">No clustering history available. Run DBSCAN to see results.</p>
                                </div>
                            <?php endif; ?>
                            
                            <div class="mt-3">
                                <h6 class="mb-2">Visualization:</h6>
                                <p>After running DBSCAN clustering, you can view the results:</p>
                                <ul>
                                    <li>Go to <a href="dashboard.php">Dashboard</a> to see the cluster distribution</li>
                                    <li>Check <a href="delete_data.php">Data Management</a> to see individual cluster assignments</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<?php include '../includes/footer.php'; ?>