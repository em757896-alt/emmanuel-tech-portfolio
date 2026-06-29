<?php
require_once '../../config/config.php';
require_once '../../config/database.php';

$pageTitle = 'Public Dashboard - PBO Kenya';
$currentPage = 'dashboard';

$db = Database::getInstance()->getConnection();

$stmt = $db->query("SELECT COUNT(*) FROM monitoring_reports WHERE status='verified'");
$verifiedReports = (int)$stmt->fetchColumn();

$stmt = $db->query("SELECT COUNT(DISTINCT county) FROM monitoring_reports WHERE county IS NOT NULL AND county != ''");
$countiesCovered = (int)$stmt->fetchColumn();

$stmt = $db->query("SELECT COUNT(*) FROM incident_reports");
$totalIncidents = (int)$stmt->fetchColumn();

$stmt = $db->query("SELECT COUNT(*) FROM knowledge_articles WHERE is_published=1");
$publishedArticles = (int)$stmt->fetchColumn();

$stmt = $db->query("
    SELECT county, COUNT(*) as count
    FROM monitoring_reports
    WHERE county IS NOT NULL AND county != ''
    GROUP BY county
    ORDER BY count DESC
    LIMIT 10
");
$topCounties = $stmt->fetchAll();

$stmt = $db->query("
    SELECT report_type, COUNT(*) as count
    FROM monitoring_reports
    WHERE status='verified'
    GROUP BY report_type
");
$byType = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

$stmt = $db->query("
    SELECT DATE_FORMAT(created_at, '%b %Y') as label, COUNT(*) as count
    FROM monitoring_reports
    WHERE created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
    GROUP BY DATE_FORMAT(created_at, '%Y-%m')
    ORDER BY MIN(created_at) ASC
");
$monthlyTrend = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <link href="../../assets/css/style.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
</head>
<body>
<?php include '../../includes/navbar.php'; ?>

<section class="page-hero py-5 bg-primary text-white">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-8" data-aos="fade-right">
                <span class="section-badge bg-white text-primary">Dashboard</span>
                <h1 class="fw-bold display-5">Civic Space Data Dashboard</h1>
                <p class="lead mb-0">Aggregated data from civic space monitoring reports across Kenya.</p>
            </div>
        </div>
    </div>
</section>

<section class="py-5">
    <div class="container">
        <div class="row g-4 mb-5">
            <div class="col-md-3" data-aos="fade-up">
                <div class="card border-0 shadow-sm text-center p-4">
                    <div class="display-6 text-primary mb-2"><?php echo number_format($verifiedReports); ?></div>
                    <p class="text-muted mb-0">Verified Reports</p>
                </div>
            </div>
            <div class="col-md-3" data-aos="fade-up" data-aos-delay="100">
                <div class="card border-0 shadow-sm text-center p-4">
                    <div class="display-6 text-success mb-2"><?php echo $countiesCovered; ?></div>
                    <p class="text-muted mb-0">Counties Covered</p>
                </div>
            </div>
            <div class="col-md-3" data-aos="fade-up" data-aos-delay="200">
                <div class="card border-0 shadow-sm text-center p-4">
                    <div class="display-6 text-danger mb-2"><?php echo number_format($totalIncidents); ?></div>
                    <p class="text-muted mb-0">Incidents Reported</p>
                </div>
            </div>
            <div class="col-md-3" data-aos="fade-up" data-aos-delay="300">
                <div class="card border-0 shadow-sm text-center p-4">
                    <div class="display-6 text-info mb-2"><?php echo $publishedArticles; ?></div>
                    <p class="text-muted mb-0">Knowledge Articles</p>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-lg-8" data-aos="fade-up">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-0 pt-4 px-4">
                        <h5 class="fw-bold mb-0"><i class="fas fa-chart-line me-2 text-primary"></i>Reports Trend (6 Months)</h5>
                    </div>
                    <div class="card-body px-4 pb-4">
                        <canvas id="trendChart" height="260"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-lg-4" data-aos="fade-up" data-aos-delay="100">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-0 pt-4 px-4">
                        <h5 class="fw-bold mb-0"><i class="fas fa-chart-pie me-2 text-success"></i>By Type</h5>
                    </div>
                    <div class="card-body px-4 pb-4">
                        <canvas id="typeChart" height="220"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4 mt-4">
            <div class="col-lg-6" data-aos="fade-up">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-0 pt-4 px-4">
                        <h5 class="fw-bold mb-0"><i class="fas fa-map-marker-alt me-2 text-danger"></i>Top Counties</h5>
                    </div>
                    <div class="card-body px-4 pb-4">
                        <canvas id="countyChart" height="220"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-lg-6" data-aos="fade-up" data-aos-delay="100">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-0 pt-4 px-4">
                        <h5 class="fw-bold mb-0"><i class="fas fa-file-alt me-2 text-warning"></i>Recent Reports</h5>
                    </div>
                    <div class="card-body px-4 pb-4">
                        <div class="text-center py-5 text-muted">
                            <i class="fas fa-chart-simple fa-3x mb-3"></i>
                            <p>View detailed analytics in the <a href="/admin/" class="text-primary fw-bold">Admin Portal</a>.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include '../../includes/footer.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
<script>
AOS.init({duration:700,once:true});
Chart.defaults.font.family = 'Inter, sans-serif';
Chart.defaults.color = '#6b7280';

new Chart(document.getElementById('trendChart'), {
    type: 'line',
    data: {
        labels: <?php echo json_encode(array_column($monthlyTrend,'label')); ?>,
        datasets: [{
            label: 'Reports',
            data: <?php echo json_encode(array_map('intval',array_column($monthlyTrend,'count'))); ?>,
            borderColor: '#3b82f6',
            backgroundColor: 'rgba(59,130,246,0.08)',
            fill: true, tension: 0.4, borderWidth: 2.5,
            pointRadius: 4, pointBackgroundColor: '#3b82f6',
        }]
    },
    options: {
        responsive: true, maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: {
            x: { grid: { display: false } },
            y: { beginAtZero: true, grid: { color: '#f3f4f6' } }
        }
    }
});

new Chart(document.getElementById('typeChart'), {
    type: 'doughnut',
    data: {
        labels: <?php echo json_encode(array_keys($byType)); ?>,
        datasets: [{
            data: <?php echo json_encode(array_values($byType)); ?>,
            backgroundColor: ['#3b82f6','#f59e0b','#ef4444','#10b981'],
            borderWidth: 3, borderColor: '#fff'
        }]
    },
    options: { cutout: '65%', plugins: { legend: { position: 'bottom' } } }
});

new Chart(document.getElementById('countyChart'), {
    type: 'bar',
    data: {
        labels: <?php echo json_encode(array_column($topCounties,'county')); ?>,
        datasets: [{
            label: 'Reports',
            data: <?php echo json_encode(array_map('intval',array_column($topCounties,'count'))); ?>,
            backgroundColor: 'rgba(239,68,68,0.12)',
            borderColor: '#ef4444',
            borderWidth: 2,
            borderRadius: 6,
        }]
    },
    options: {
        indexAxis: 'y', responsive: true, maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: {
            x: { beginAtZero: true, grid: { color: '#f3f4f6' } },
            y: { grid: { display: false } }
        }
    }
});
</script>
</body>
</html>
