<?php
/**
 * Public Index Page
 * 
 * This is the main public-facing page of the Diabetes Clustering application.
 * It displays visualizations of diabetes data and clustering results.
 */
require_once 'config/database.php';

// Start session to access user settings if available
session_start();

// Get user settings from session if available (for admin users)
$show_chart_index = true; // Default value
if (isset($_SESSION['user_settings']) && isset($_SESSION['user_settings']['show_chart_index'])) {
    $show_chart_index = (bool)$_SESSION['user_settings']['show_chart_index'];
}

// Get the selected year (default to the latest year if not set)
$selected_year = isset($_GET['year']) ? $_GET['year'] : null;

// Fetch available years
$stmt = $pdo->query("SELECT DISTINCT tahun FROM diabetes_data ORDER BY tahun DESC");
$years = $stmt->fetchAll(PDO::FETCH_COLUMN);

// If no year is selected, use the latest year
if (!$selected_year && !empty($years)) {
    $selected_year = $years[0];
}

// Fetch region data for chart and table
$stmt = $pdo->prepare("SELECT * FROM diabetes_data WHERE tahun = ? ORDER BY jumlah_penderita DESC");
$stmt->execute([$selected_year]);
$region_data = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get top 10 regions for the chart
$chart_data = array_slice($region_data, 0, 10);

// Calculate statistics
$total_penduduk = array_sum(array_column($region_data, 'jumlah_penduduk'));
$total_penderita = array_sum(array_column($region_data, 'jumlah_penderita'));
$total_kematian = array_sum(array_column($region_data, 'jumlah_kematian'));

// Calculate averages
$region_count = count($region_data);
$avg_penduduk = $region_count > 0 ? $total_penduduk / $region_count : 0;
$avg_penderita = $region_count > 0 ? $total_penderita / $region_count : 0;
$avg_kematian = $region_count > 0 ? $total_kematian / $region_count : 0;

// Calculate mortality rate as percentage of patients
$mortality_rate = $total_penderita > 0 ? ($total_kematian / $total_penderita) * 100 : 0;

// Calculate level statistics with cluster categorization
$level_counts = ['Rendah' => 0, 'Sedang' => 0, 'Tinggi' => 0, 'Tidak Terdefinisi' => 0];
foreach ($region_data as $row) {
    $category = getCategory(isset($row['cluster']) ? $row['cluster'] : null)['category'];
    $level_counts[$category]++;
}

$show_map_index = true; // Default value
if (isset($_SESSION['user_settings']) && isset($_SESSION['user_settings']['show_map_index'])) {
    $show_map_index = (bool)$_SESSION['user_settings']['show_map_index'];
}

$map_default_zoom = isset($_SESSION['user_settings']['map_default_zoom']) ? $_SESSION['user_settings']['map_default_zoom'] : 5;
$map_default_center = isset($_SESSION['user_settings']['map_default_center']) ? $_SESSION['user_settings']['map_default_center'] : '-2.5, 118';
$map_default_color = isset($_SESSION['user_settings']['map_default_color']) ? $_SESSION['user_settings']['map_default_color'] : 'penderita';

// Fetch region coordinates
$stmt = $pdo->query("SELECT wilayah, latitude, longitude FROM region_coordinates");
$region_coordinates = [];
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $region_coordinates[$row['wilayah']] = [$row['latitude'], $row['longitude']];
}

/**
 * Maps cluster IDs to categories and colors
 *
 * @param int|null $cluster The cluster ID
 * @return array Array with category name and color class
 */
function getCategory($cluster) {
    $cluster = (int)$cluster;
    switch ($cluster) {
        case 0:
            return ['category' => 'Rendah', 'color' => 'bg-success'];
        case 1:
            return ['category' => 'Sedang', 'color' => 'bg-warning'];
        case 2:
            return ['category' => 'Tinggi', 'color' => 'bg-danger'];
        default:
            return ['category' => 'Tidak Terdefinisi', 'color' => 'bg-secondary'];
    }
}

include 'includes/header.php';
?>

<div class="container-fluid py-4">
    <!-- Header Section with List of Regions -->
    <div class="card shadow-sm mb-4 fade-in">
        <div class="card-body py-3">
            <div class="region-tabs d-flex overflow-auto pb-2">
                <?php foreach (array_slice($region_data, 0, 10) as $index => $region): ?>
                <div class="region-tab me-4 <?= $index === 0 ? 'active' : '' ?>" data-region="<?= htmlspecialchars($region['wilayah']) ?>">
                    <div class="fw-medium"><?= htmlspecialchars($region['wilayah']) ?></div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row fade-in">
        <!-- Total Population Card -->
        <div class="col-md-6 col-lg-3 mb-4">
            <div class="dashboard-card">
                <div class="d-flex align-items-center mb-2">
                    <div class="stat-icon bg-primary bg-opacity-10">
                        <i class="bi bi-people-fill text-primary"></i>
                    </div>
                    <div class="ms-3">
                        <div class="text-muted small">Total Penduduk</div>
                        <div class="stat-value text-primary"><?= number_format($total_penduduk) ?></div>
                    </div>
                </div>
                <div class="progress" style="height: 4px;">
                    <div class="progress-bar bg-primary" style="width: 100%"></div>
                </div>
            </div>
        </div>
        
        <!-- Total Patients Card -->
        <div class="col-md-6 col-lg-3 mb-4">
            <div class="dashboard-card">
                <div class="d-flex align-items-center mb-2">
                    <div class="stat-icon bg-info bg-opacity-10">
                        <i class="bi bi-heart-pulse-fill text-info"></i>
                    </div>
                    <div class="ms-3">
                        <div class="text-muted small">Total Penderita</div>
                        <div class="stat-value text-info"><?= number_format($total_penderita) ?></div>
                    </div>
                </div>
                <div class="small text-muted">Average: <?= number_format($avg_penderita, 1) ?> per region</div>
                <div class="progress" style="height: 4px;">
                    <div class="progress-bar bg-info" style="width: 100%"></div>
                </div>
            </div>
        </div>
        
        <!-- Mortality Card -->
        <div class="col-md-6 col-lg-3 mb-4">
            <div class="dashboard-card">
                <div class="d-flex align-items-center mb-2">
                    <div class="stat-icon bg-danger bg-opacity-10">
                        <i class="bi bi-heart-fill text-danger"></i>
                    </div>
                    <div class="ms-3">
                        <div class="text-muted small">Total Kematian</div>
                        <div class="stat-value text-danger"><?= number_format($total_kematian) ?></div>
                    </div>
                </div>
                <div class="small text-muted">Tingkat Kematian: <?= number_format($mortality_rate, 1) ?>%</div>
                <div class="progress" style="height: 4px;">
                    <div class="progress-bar bg-danger" style="width: <?= min(100, $mortality_rate * 5) ?>%"></div>
                </div>
            </div>
        </div>
        
        <!-- Cluster Distribution Card -->
        <div class="col-md-6 col-lg-3 mb-4">
            <div class="dashboard-card">
                <div class="d-flex align-items-center mb-2">
                    <div class="stat-icon bg-success bg-opacity-10">
                        <i class="bi bi-diagram-3 text-success"></i>
                    </div>
                    <div class="ms-3">
                        <div class="text-muted small">Cluster Distribution</div>
                        <div class="stat-value text-success"><?= array_sum($level_counts) ?> Total</div>
                    </div>
                </div>
                <div class="progress" style="height: 12px; border-radius: 6px;">
                    <?php 
                    $total = array_sum($level_counts);
                    if ($total > 0):
                        $low_pct = ($level_counts['Rendah'] / $total) * 100;
                        $med_pct = ($level_counts['Sedang'] / $total) * 100;
                        $high_pct = ($level_counts['Tinggi'] / $total) * 100;
                    ?>
                        <div class="progress-bar bg-success" style="width: <?= $low_pct ?>%" title="Rendah: <?= $level_counts['Rendah'] ?>"></div>
                        <div class="progress-bar bg-warning" style="width: <?= $med_pct ?>%" title="Sedang: <?= $level_counts['Sedang'] ?>"></div>
                        <div class="progress-bar bg-danger" style="width: <?= $high_pct ?>%" title="Tinggi: <?= $level_counts['Tinggi'] ?>"></div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

<?php if ($show_chart_index): ?>
<!-- Map Section -->
<div class="mb-4 fade-in">
    <div class="d-flex justify-content-between align-items-start mb-3">
        <div class="d-flex align-items-center">
            <i class="bi bi-geo-alt-fill fs-4 me-2"></i>
            <h5 class="mb-0">Peta Distribusi Diabetes per Wilayah</h5>
        </div>
        <div class="d-flex align-items-center">
            <div class="dropdown me-3">
                <button class="btn btn-outline-secondary btn-sm dropdown-toggle" type="button" id="filterDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                    Berdasarkan Jumlah Penderita
                </button>
                <ul class="dropdown-menu" aria-labelledby="filterDropdown">
                    <li><a class="dropdown-item" href="#" data-filter="penderita">Berdasarkan Jumlah Penderita</a></li>
                    <li><a class="dropdown-item" href="#" data-filter="kematian">Berdasarkan Jumlah Kematian</a></li>
                    <li><a class="dropdown-item" href="#" data-filter="cluster">Berdasarkan Cluster</a></li>
                </ul>
            </div>
            <div class="form-check form-switch">
                <input class="form-check-input" type="checkbox" id="boundaryLinesOnly" checked>
                <label class="form-check-label small" for="boundaryLinesOnly">
                    Tampilkan hanya garis batas
                </label>
            </div>
        </div>
    </div>
    
    <div id="regionMap" class="rounded" style="height: 500px;"></div>
    
    <div class="mt-3 d-flex justify-content-center">
        <div class="d-flex align-items-center me-4">
            <span class="legend-item" style="background-color: #ffffcc;"></span>
            <span class="small ms-1">Rendah</span>
        </div>
        <div class="d-flex align-items-center me-4">
            <span class="legend-item" style="background-color: #fd8d3c;"></span>
            <span class="small ms-1">Sedang</span>
        </div>
        <div class="d-flex align-items-center">
            <span class="legend-item" style="background-color: #bd0026;"></span>
            <span class="small ms-1">Tinggi</span>
        </div>
    </div>
</div>

<!-- Charts Section - Only shown if setting is enabled -->
<div class="row fade-in">
    <div class="col-lg-8">
        <div class="dashboard-card">
            <h5 class="mb-4">Distribusi Penderita per Wilayah</h5>
            <div class="chart-wrapper">
                <canvas id="regionChart" height="350"></canvas>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="dashboard-card">
            <h5 class="mb-4">Statistik Level</h5>
            <?php foreach ($level_counts as $level => $count): 
                if ($level === 'Tidak Terdefinisi' && $count === 0) continue;
                
                $percentage = ($count / array_sum($level_counts)) * 100;
                $color = '';
                switch($level) {
                    case 'Rendah':
                        $color = 'success';
                        break;
                    case 'Sedang':
                        $color = 'warning';
                        break;
                    case 'Tinggi':
                        $color = 'danger';
                        break;
                    default:
                        $color = 'secondary';
                }   
            ?>
                <div class="mb-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="status-badge bg-<?= $color ?> bg-opacity-10 text-<?= $color ?>">
                            <?= $level ?>
                        </span>
                        <span class="fw-bold"><?= $count ?></span>
                    </div>
                    <div class="progress">
                        <div class="progress-bar bg-<?= $color ?>" 
                             role="progressbar" 
                             style="width: <?= $percentage ?>%" 
                             aria-valuenow="<?= $percentage ?>" 
                             aria-valuemin="0" 
                             aria-valuemax="100"></div>
                    </div>
                </div>
            <?php endforeach; ?>
            
        </div>
    </div>
</div>
<?php else: ?>
<!-- Message when charts are disabled -->
<div class="alert alert-info fade-in">
    <i class="bi bi-info-circle me-2"></i>
    Charts are currently disabled. You can enable them in the <strong>Settings</strong> page.
</div>
<?php endif; ?>

    <!-- Data Table -->
    <div class="dashboard-card fade-in mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h5><i class="bi bi-table me-2"></i>Data Penderita Diabetes per Wilayah</h5>
        </div>
        
        <div class="table-responsive">
            <table class="table table-hover custom-table">
                <thead>
                    <tr>
                        <th>Wilayah</th>
                        <th>Jumlah Penduduk</th>
                        <th>Jumlah Penderita</th>
                        <th>Jumlah Kematian</th>
                        <th>Kategori</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($region_data as $row): 
                        $cat_info = getCategory(isset($row['cluster']) ? $row['cluster'] : null);
                    ?>
                    <tr>
                        <td class="fw-medium"><?= htmlspecialchars($row['wilayah']) ?></td>
                        <td><?= number_format($row['jumlah_penduduk'] ?? 0) ?></td>
                        <td><?= number_format($row['jumlah_penderita']) ?></td>
                        <td><?= number_format($row['jumlah_kematian']) ?></td>
                        <td>
                            <span class="status-badge <?= $cat_info['color'] ?>">
                                <?= $cat_info['category'] ?>
                            </span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<style>
/* Styling for region tabs */
.region-tabs {
    -ms-overflow-style: none;  /* Hide scrollbar in IE and Edge */
    scrollbar-width: none;  /* Hide scrollbar in Firefox */
}

.region-tabs::-webkit-scrollbar {
    display: none;  /* Hide scrollbar for Chrome, Safari, and Opera */
}

.region-tab {
    cursor: pointer;
    padding: 5px 15px;
    transition: all 0.3s ease;
    border-bottom: 2px solid transparent;
    white-space: nowrap;
}

.region-tab.active {
    border-bottom: 2px solid #0d6efd;
    font-weight: 500;
}

.region-tab:hover {
    background-color: rgba(13, 110, 253, 0.05);
}

/* Legend styling */
.legend-item {
    display: inline-block;
    width: 20px;
    height: 15px;
    border-radius: 2px;
}
</style>

<?php if ($show_chart_index): ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize map
    const mapElement = document.getElementById('regionMap');
    if (!mapElement) return;
    
    // Map center and zoom
    const mapCenter = [5.5, 95.3]; // Center on Aceh
    const mapZoom = 10;
    
    // Initialize map
    const map = L.map('regionMap').setView(mapCenter, mapZoom);
    
    // Add tile layer
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
        maxZoom: 18
    }).addTo(map);
    
    // Get regions data
    const regionsData = <?php echo json_encode($region_data); ?>;
    
    // Get coordinates
    const regionCoordinates = <?php echo json_encode($region_coordinates); ?>;
    
    // Coloring functions
    function getColor(value, max, type) {
        if (type === 'cluster') {
            switch(parseInt(value)) {
                case 0: return '#1a9850'; // Green
                case 1: return '#fd8d3c'; // Orange
                case 2: return '#bd0026'; // Red
                default: return '#cccccc'; // Gray
            }
        }
        
        // Color scale for continuous values
        const colors = ['#ffffcc', '#ffeda0', '#fed976', '#feb24c', '#fd8d3c', '#fc4e2a', '#e31a1c', '#bd0026'];
        const ratio = max > 0 ? value / max : 0;
        const index = Math.min(Math.floor(ratio * colors.length), colors.length - 1);
        return colors[index];
    }
    
    // Current filter
    let currentFilter = 'penderita';
    
    // Max values for normalization
    const maxPenderita = Math.max(...regionsData.map(item => item.jumlah_penderita));
    const maxKematian = Math.max(...regionsData.map(item => item.jumlah_kematian));
    
    // Generate popup content
    function createPopupContent(region) {
        return `<strong>${region.wilayah}</strong><br>
                <p>Populasi: ${parseInt(region.jumlah_penduduk).toLocaleString()}</p>
                <p>Penderita: ${parseInt(region.jumlah_penderita).toLocaleString()}</p>
                <p>Kematian: ${parseInt(region.jumlah_kematian).toLocaleString()}</p>
                ${region.cluster !== undefined ? 
                `<p>Cluster: <span class="badge bg-${
                    region.cluster == 0 ? 'success' : (region.cluster == 1 ? 'warning' : 'danger')
                }">${region.cluster}</span></p>` : ''}`;
    }
    
    // Store the boundary layers
    const boundaryLayers = {};
    
    // Function to draw the boundaries
    function drawBoundaries() {
        // Clear existing boundaries
        Object.values(boundaryLayers).forEach(layer => {
            if (map.hasLayer(layer)) {
                map.removeLayer(layer);
            }
        });
        
        // Get option status
        const showLinesOnly = document.getElementById('boundaryLinesOnly').checked;
        
        // Draw each region
        regionsData.forEach(region => {
            const regionName = region.wilayah;
            
            // Skip if no boundary data
            if (!boundaries[regionName]) return;
            
            // Determine color based on filter
            let fillColor;
            if (currentFilter === 'cluster') {
                fillColor = getColor(region.cluster, 0, 'cluster');
            } else if (currentFilter === 'kematian') {
                fillColor = getColor(region.jumlah_kematian, maxKematian);
            } else {
                fillColor = getColor(region.jumlah_penderita, maxPenderita);
            }
            
            // Create popup content
            const popupContent = createPopupContent(region);
            
            // Create the boundary
            if (showLinesOnly) {
                // Create polyline (just the boundary line)
                boundaryLayers[regionName] = L.polyline(boundaries[regionName], {
                    color: '#ff3b30',  // Red color
                    weight: 2,
                    opacity: 0.8,
                    dashArray: '5, 8',  // Dashed line
                    lineCap: 'round',
                    lineJoin: 'round'
                }).bindPopup(popupContent).addTo(map);
            } else {
                // Create polygon (filled area)
                boundaryLayers[regionName] = L.polygon(boundaries[regionName], {
                    color: '#ff3b30',  // Red border
                    weight: 2,
                    opacity: 0.8,
                    dashArray: '5, 8',  // Dashed border
                    fillColor: fillColor,
                    fillOpacity: 0.3
                }).bindPopup(popupContent).addTo(map);
            }
        });
    }
    
    // Initial drawing
    drawBoundaries();
    
    // Handle boundary toggle
    document.getElementById('boundaryLinesOnly').addEventListener('change', function() {
        drawBoundaries();
    });
    
    // Handle filter dropdown clicks
    document.querySelectorAll('.dropdown-item').forEach(item => {
        item.addEventListener('click', function(e) {
            e.preventDefault();
            currentFilter = this.dataset.filter;
            document.getElementById('filterDropdown').textContent = this.textContent;
            drawBoundaries();
        });
    });
    
    // Handle region tabs
    document.querySelectorAll('.region-tab').forEach(tab => {
        tab.addEventListener('click', function() {
            // Remove active class from all tabs
            document.querySelectorAll('.region-tab').forEach(t => {
                t.classList.remove('active');
            });
            
            // Add active class to clicked tab
            this.classList.add('active');
            
            // Get the region name
            const regionName = this.dataset.region;
            
            // Center map on the region
            if (boundaries[regionName]) {
                // Create a polygon to get the bounds
                const polygon = L.polygon(boundaries[regionName]);
                map.fitBounds(polygon.getBounds());
                
                // Open the popup for the region
                if (boundaryLayers[regionName]) {
                    boundaryLayers[regionName].openPopup();
                }
            } else if (regionCoordinates[regionName]) {
                // If we have coordinates but no boundary
                const coords = regionCoordinates[regionName];
                map.setView([coords[0], coords[1]], 13);
            }
        });
    });
});
</script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('regionChart').getContext('2d');
    
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: <?= json_encode(array_column($chart_data, 'wilayah')) ?>,
            datasets: [{
                label: 'Jumlah Penderita',
                data: <?= json_encode(array_column($chart_data, 'jumlah_penderita')) ?>,
                backgroundColor: [
                    'rgba(13, 110, 253, 0.6)',  // primary
                    'rgba(220, 53, 69, 0.6)',   // danger
                    'rgba(25, 135, 84, 0.6)',   // success
                    'rgba(255, 193, 7, 0.6)',   // warning
                    'rgba(13, 202, 240, 0.6)',  // info
                    'rgba(111, 66, 193, 0.6)',  // purple
                    'rgba(102, 16, 242, 0.6)',  // indigo
                    'rgba(253, 126, 20, 0.6)',  // orange
                    'rgba(32, 201, 151, 0.6)',  // teal
                    'rgba(214, 51, 132, 0.6)'   // pink
                ],
                borderColor: [
                    'rgb(13, 110, 253)',
                    'rgb(220, 53, 69)',
                    'rgb(25, 135, 84)',
                    'rgb(255, 193, 7)',
                    'rgb(13, 202, 240)',
                    'rgb(111, 66, 193)',
                    'rgb(102, 16, 242)',
                    'rgb(253, 126, 20)',
                    'rgb(32, 201, 151)',
                    'rgb(214, 51, 132)'
                ],
                borderWidth: 1,
                borderRadius: 5,
                barThickness: 30
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            layout: {
                padding: {
                    left: 10,
                    right: 10
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        drawBorder: false,
                        color: 'rgba(0, 0, 0, 0.05)'
                    },
                    ticks: {
                        callback: function(value) {
                            return new Intl.NumberFormat().format(value);
                        },
                        font: {
                            size: 12
                        }
                    }
                },
                x: {
                    grid: {
                        display: false
                    },
                    ticks: {
                        font: {
                            size: 12
                        },
                        maxRotation: 45,
                        minRotation: 45
                    }
                }
            },
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    backgroundColor: 'rgba(255, 255, 255, 0.9)',
                    titleColor: '#000',
                    titleFont: {
                        size: 14,
                        weight: 'bold'
                    },
                    bodyColor: '#000',
                    bodyFont: {
                        size: 13
                    },
                    borderColor: '#ddd',
                    borderWidth: 1,
                    padding: 12,
                    displayColors: false,
                    callbacks: {
                        label: function(context) {
                            const dataIndex = context.dataIndex;
                            const penduduk = <?= json_encode(array_column($chart_data, 'jumlah_penduduk')) ?>[dataIndex] || 0;
                            const penderita = context.raw;
                            const kematian = <?= json_encode(array_column($chart_data, 'jumlah_kematian')) ?>[dataIndex];
                            
                            return [
                                'Jumlah Penduduk: ' + new Intl.NumberFormat().format(penduduk),
                                'Jumlah Penderita: ' + new Intl.NumberFormat().format(penderita),
                                'Jumlah Kematian: ' + new Intl.NumberFormat().format(kematian)
                            ];
                        }
                    }
                }
            }
        }
    });
});
</script>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>