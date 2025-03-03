<?php
session_start();
require_once '../config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Get user settings
$stmt = $pdo->prepare("SELECT * FROM user_settings WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$settings = $stmt->fetch(PDO::FETCH_ASSOC);

// Default settings if none found
if (!$settings) {
    $settings = [
        'theme' => 'light',
        'show_chart_dashboard' => 1,
        'default_sort' => 'jumlah_penderita'
    ];
}

// Store settings in session for use across pages
$_SESSION['user_settings'] = $settings;

// Get the show_chart_dashboard setting
$show_chart_dashboard = isset($settings['show_chart_dashboard']) ? (bool)$settings['show_chart_dashboard'] : true;

// Get selected year (default to the latest year if not set)
$selected_year = isset($_GET['year']) ? $_GET['year'] : null;

// Fetch available years
$stmt = $pdo->query("SELECT DISTINCT tahun FROM diabetes_data ORDER BY tahun DESC");
$years = $stmt->fetchAll(PDO::FETCH_COLUMN);

// If no year is selected or if selected year is not in the list, use the latest year
if (!$selected_year || !in_array($selected_year, $years)) {
    $selected_year = $years[0] ?? date('Y');
}

// Fetch region data for charts and stats
$stmt = $pdo->prepare("SELECT * FROM diabetes_data WHERE tahun = ? ORDER BY {$settings['default_sort']} DESC");
$stmt->execute([$selected_year]);
$region_data = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Calculate summary statistics
$total_penduduk = array_sum(array_column($region_data, 'jumlah_penduduk'));
$total_penderita = array_sum(array_column($region_data, 'jumlah_penderita'));
$total_kematian = array_sum(array_column($region_data, 'jumlah_kematian'));

$avg_penduduk = count($region_data) > 0 ? $total_penduduk / count($region_data) : 0;
$avg_penderita = count($region_data) > 0 ? $total_penderita / count($region_data) : 0;
$avg_kematian = count($region_data) > 0 ? $total_kematian / count($region_data) : 0;

// Calculate mortality rate
$mortality_rate = $total_penderita > 0 ? ($total_kematian / $total_penderita) * 100 : 0;

// Get cluster statistics
$cluster_counts = ['0' => 0, '1' => 0, '2' => 0];
foreach ($region_data as $row) {
    if (isset($row['cluster'])) {
        $cluster = (string)$row['cluster'];
        $cluster_counts[$cluster] = ($cluster_counts[$cluster] ?? 0) + 1;
    }
}

// Prepare chart data for the top 10 regions
$chart_data = array_slice($region_data, 0, 10);

// Get recent activities (latest data additions and modifications)
$recent_stmt = $pdo->query("SELECT id, wilayah, tahun, jumlah_penderita, jumlah_kematian, created_at 
                            FROM diabetes_data 
                            ORDER BY created_at DESC 
                            LIMIT 5");
$recent_activities = $recent_stmt->fetchAll();

// Get clustering history
$cluster_stmt = $pdo->query("SELECT * FROM clustering_results ORDER BY date_generated DESC LIMIT 3");
$cluster_history = $cluster_stmt->fetchAll();

include '../includes/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <?php include '../includes/sidebar.php'; ?>

        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Dashboard - Year <?php echo htmlspecialchars($selected_year); ?></h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <select class="form-select year-select" onchange="window.location.href='?year='+this.value">
                        <?php foreach ($years as $year): ?>
                            <option value="<?php echo htmlspecialchars($year); ?>" <?php echo $year == $selected_year ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($year); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <!-- Statistics Cards -->
            <div class="row g-3 mb-4">
                <div class="col-md-6 col-lg-3">
                    <div class="card shadow-sm">
                        <div class="card-body">
                            <div class="stats-icon bg-primary bg-opacity-10">
                                <i class="bi bi-people text-primary"></i>
                            </div>
                            <div class="stats-label">Total Penduduk</div>
                            <div class="stats-value text-primary"><?php echo number_format($total_penduduk); ?></div>
                            <div class="small text-muted">
                                Avg: <?php echo number_format($avg_penduduk); ?> per region
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3">
                    <div class="card shadow-sm">
                        <div class="card-body">
                            <div class="stats-icon bg-info bg-opacity-10">
                                <i class="bi bi-heart-pulse text-info"></i>
                            </div>
                            <div class="stats-label">Total Penderita</div>
                            <div class="stats-value text-info"><?php echo number_format($total_penderita); ?></div>
                            <div class="small text-muted">
                                Avg: <?php echo number_format($avg_penderita); ?> per region
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3">
                    <div class="card shadow-sm">
                        <div class="card-body">
                            <div class="stats-icon bg-danger bg-opacity-10">
                                <i class="bi bi-heart-fill text-danger"></i>
                            </div>
                            <div class="stats-label">Total Kematian</div>
                            <div class="stats-value text-danger"><?php echo number_format($total_kematian); ?></div>
                            <div class="small text-muted">
                                Mortality: <?php echo number_format($mortality_rate, 1); ?>% of cases
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3">
                    <div class="card shadow-sm">
                        <div class="card-body">
                            <div class="stats-icon bg-success bg-opacity-10">
                                <i class="bi bi-diagram-3 text-success"></i>
                            </div>
                            <div class="stats-label">Cluster Distribution</div>
                            <div class="stats-value text-success"><?php echo array_sum($cluster_counts); ?> Total</div>
                            <div class="progress" style="height: 8px;">
                                <?php if (array_sum($cluster_counts) > 0): ?>
                                    <div class="progress-bar bg-success" style="width: <?php echo $cluster_counts['0'] / array_sum($cluster_counts) * 100; ?>%"></div>
                                    <div class="progress-bar bg-warning" style="width: <?php echo $cluster_counts['1'] / array_sum($cluster_counts) * 100; ?>%"></div>
                                    <div class="progress-bar bg-danger" style="width: <?php echo $cluster_counts['2'] / array_sum($cluster_counts) * 100; ?>%"></div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Charts and Tables Section -->
            <div class="row">
                <?php if ($show_chart_dashboard): ?>
                    <div class="col-lg-8">
                        <div class="card shadow-sm mb-4">
                            <div class="card-body">
                                <h5 class="card-title mb-3">
                                    <i class="bi bi-bar-chart me-2"></i>
                                    Top 10 Regions by Patient Count
                                </h5>
                                <div style="height: 350px;">
                                    <canvas id="regionsChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                
                    <div class="col-lg-4">
                        <div class="card shadow-sm mb-4">
                            <div class="card-body">
                                <h5 class="card-title mb-3">
                                    <i class="bi bi-pie-chart me-2"></i>
                                    Cluster Distribution
                                </h5>
                                
                                <div class="mb-3">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span class="status-badge bg-success">Low Risk (Cluster 0)</span>
                                        <span class="fw-bold"><?php echo $cluster_counts['0']; ?></span>
                                    </div>
                                    <div class="progress mb-3">
                                        <div class="progress-bar bg-success" style="width: <?php echo array_sum($cluster_counts) > 0 ? ($cluster_counts['0'] / array_sum($cluster_counts) * 100) : 0; ?>%"></div>
                                    </div>
                                    
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span class="status-badge bg-warning">Medium Risk (Cluster 1)</span>
                                        <span class="fw-bold"><?php echo $cluster_counts['1']; ?></span>
                                    </div>
                                    <div class="progress mb-3">
                                        <div class="progress-bar bg-warning" style="width: <?php echo array_sum($cluster_counts) > 0 ? ($cluster_counts['1'] / array_sum($cluster_counts) * 100) : 0; ?>%"></div>
                                    </div>
                                    
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span class="status-badge bg-danger">High Risk (Cluster 2)</span>
                                        <span class="fw-bold"><?php echo $cluster_counts['2']; ?></span>
                                    </div>
                                    <div class="progress">
                                        <div class="progress-bar bg-danger" style="width: <?php echo array_sum($cluster_counts) > 0 ? ($cluster_counts['2'] / array_sum($cluster_counts) * 100) : 0; ?>%"></div>
                                    </div>
                                </div>
                                
                                <div class="d-grid gap-2 mt-4">
                                    <a href="dbscan_clustering.php" class="btn btn-outline-primary">
                                        <i class="bi bi-gear me-1"></i> Run Clustering
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <!-- Message when charts are disabled -->
                    <div class="col-12">
                        <div class="alert alert-info mb-4">
                            <i class="bi bi-info-circle me-2"></i>
                            Charts are currently disabled. You can enable them in the <a href="settings.php" class="alert-link">Settings</a> page.
                        </div>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Recent Activities and Data Tables -->
            <div class="row">
                <div class="col-lg-6">
                    <div class="card shadow-sm mb-4">
                        <div class="card-body">
                            <h5 class="card-title d-flex justify-content-between align-items-center mb-3">
                                <span><i class="bi bi-clock-history me-2"></i>Recent Activities</span>
                                <a href="delete_data.php" class="btn btn-sm btn-outline-secondary">View All</a>
                            </h5>
                            
                            <?php if (count($recent_activities) > 0): ?>
                                <div class="list-group">
                                    <?php foreach ($recent_activities as $activity): ?>
                                        <div class="list-group-item list-group-item-action d-flex gap-3 py-3">
                                            <div class="d-flex gap-2 w-100 justify-content-between">
                                                <div>
                                                    <h6 class="mb-0"><?php echo htmlspecialchars($activity['wilayah']); ?> (<?php echo htmlspecialchars($activity['tahun']); ?>)</h6>
                                                    <p class="mb-0 text-muted">
                                                        Penderita: <?php echo number_format($activity['jumlah_penderita']); ?>,
                                                        Kematian: <?php echo number_format($activity['jumlah_kematian']); ?>
                                                    </p>
                                                </div>
                                                <small class="text-muted">
                                                    <?php echo date('d M Y', strtotime($activity['created_at'])); ?>
                                                </small>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <div class="text-center py-4">
                                    <p class="text-muted">No recent activities found.</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-6">
                    <div class="card shadow-sm mb-4">
                        <div class="card-body">
                            <h5 class="card-title d-flex justify-content-between align-items-center mb-3">
                                <span><i class="bi bi-tools me-2"></i>Recent Clustering</span>
                                <a href="dbscan_clustering.php" class="btn btn-sm btn-outline-secondary">View All</a>
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
                                                <th>Time (s)</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($cluster_history as $history): ?>
                                                <tr>
                                                    <td><?php echo date('d M Y', strtotime($history['date_generated'])); ?></td>
                                                    <td>
                                                        <span class="badge bg-primary"><?php echo htmlspecialchars($history['cluster_count']); ?></span>
                                                    </td>
                                                    <td><?php echo htmlspecialchars($history['epsilon']); ?></td>
                                                    <td><?php echo htmlspecialchars($history['min_points']); ?></td>
                                                    <td><?php echo htmlspecialchars($history['execution_time']); ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php else: ?>
                                <div class="text-center py-4">
                                    <p class="text-muted">No clustering history found.</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Data Table Section -->
            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <h5 class="card-title d-flex justify-content-between align-items-center mb-3">
                        <span><i class="bi bi-table me-2"></i>Data Overview</span>
                        <a href="delete_data.php" class="btn btn-sm btn-outline-secondary">Manage Data</a>
                    </h5>
                    
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Wilayah</th>
                                    <th>Penduduk</th>
                                    <th>Penderita</th>
                                    <th>Kematian</th>
                                    <th>Cluster</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach (array_slice($region_data, 0, 5) as $row): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($row['wilayah']); ?></td>
                                        <td><?php echo number_format($row['jumlah_penduduk']); ?></td>
                                        <td><?php echo number_format($row['jumlah_penderita']); ?></td>
                                        <td><?php echo number_format($row['jumlah_kematian']); ?></td>
                                        <td>
                                            <span class="badge bg-<?php 
                                                switch ((int)$row['cluster']) {
                                                    case 0: echo 'success'; break;
                                                    case 1: echo 'warning'; break;
                                                    case 2: echo 'danger'; break;
                                                    default: echo 'secondary';
                                                }
                                            ?>">
                                                <?php echo isset($row['cluster']) ? htmlspecialchars($row['cluster']) : 'N/A'; ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                                
                                <?php if (count($region_data) > 5): ?>
                                    <tr>
                                        <td colspan="5" class="text-center">
                                            <a href="delete_data.php" class="btn btn-sm btn-link">View all <?php echo count($region_data); ?> records</a>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Settings reminder if charts are disabled -->
            <?php if (!$show_chart_dashboard): ?>
            <div class="card border-info mb-4">
                <div class="card-body">
                    <div class="d-flex">
                        <div class="flex-shrink-0">
                            <i class="bi bi-lightbulb text-info fs-4"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h5 class="card-title">Charts are disabled</h5>
                            <p class="card-text">You have disabled charts on this dashboard. Charts can help visualize your data more effectively.</p>
                            <a href="settings.php" class="btn btn-sm btn-outline-info">
                                <i class="bi bi-gear me-1"></i> Change Settings
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </main>
    </div>
</div>

<?php if ($show_chart_dashboard): ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('regionsChart').getContext('2d');
    
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: <?php echo json_encode(array_column($chart_data, 'wilayah')); ?>,
            datasets: [{
                label: 'Jumlah Penderita',
                data: <?php echo json_encode(array_column($chart_data, 'jumlah_penderita')); ?>,
                backgroundColor: 'rgba(13, 110, 253, 0.7)',
                borderColor: 'rgba(13, 110, 253, 1)',
                borderWidth: 1,
                borderRadius: 4,
                barThickness: 'flex',
                maxBarThickness: 35
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return 'Penderita: ' + new Intl.NumberFormat().format(context.raw);
                        },
                        afterLabel: function(context) {
                            const dataIndex = context.dataIndex;
                            const deaths = <?php echo json_encode(array_column($chart_data, 'jumlah_kematian')); ?>[dataIndex];
                            return 'Kematian: ' + new Intl.NumberFormat().format(deaths);
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        color: 'rgba(0, 0, 0, 0.05)'
                    },
                    ticks: {
                        callback: function(value) {
                            return new Intl.NumberFormat().format(value);
                        }
                    }
                },
                x: {
                    grid: {
                        display: false
                    },
                    ticks: {
                        maxRotation: 45,
                        minRotation: 45
                    }
                }
            }
        }
    });
});
</script>
<?php endif; ?>

<?php include '../includes/footer.php'; ?>