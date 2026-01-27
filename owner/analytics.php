<?php
session_start();
if(!isset($_SESSION['user_id'])){ header("Location: ../login.php"); exit; }
if(strtolower($_SESSION['role'] ?? '') !== 'owner'){ header("Location: ../login.php"); exit; }

$username = $_SESSION['username'] ?? 'Owner';
include '../config/db.php';

/* =========================
   ANALYTICS (Last 12 months)
========================= */

// Monthly KG + Revenue (use sales_items line_total to avoid double-count)
$months = [];
$kgData = [];
$revData = [];

$monthly = $conn->query("
  SELECT 
    DATE_FORMAT(s.sale_date,'%b %Y') AS month_label,
    YEAR(s.sale_date) AS y,
    MONTH(s.sale_date) AS m,
    COALESCE(SUM(si.qty_kg),0) AS total_kg,
    COALESCE(SUM(si.line_total),0) AS total_rev
  FROM sales s
  JOIN sales_items si ON si.sale_id = s.sale_id
  WHERE s.sale_date >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
    AND (s.status IS NULL OR LOWER(s.status) <> 'cancelled')
  GROUP BY y, m, month_label
  ORDER BY y, m
  LIMIT 12
");

if($monthly){
  while($r = $monthly->fetch_assoc()){
    $months[] = $r['month_label'];
    $kgData[] = (float)$r['total_kg'];
    $revData[] = (float)$r['total_rev'];
  }
}

// Summary totals (same range)
$summary = $conn->query("
  SELECT 
    COALESCE(SUM(si.qty_kg),0) AS total_kg,
    COALESCE(SUM(si.line_total),0) AS total_rev,
    COUNT(DISTINCT s.sale_id) AS total_sales
  FROM sales s
  JOIN sales_items si ON si.sale_id = s.sale_id
  WHERE s.sale_date >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
    AND (s.status IS NULL OR LOWER(s.status) <> 'cancelled')
")->fetch_assoc();

$totalKg12 = (float)($summary['total_kg'] ?? 0);
$totalRev12 = (float)($summary['total_rev'] ?? 0);
$totalSales12 = (int)($summary['total_sales'] ?? 0);

// Forecast placeholder based on last month values
$forecastLabels = ["Next Month", "+2 Months", "+3 Months"];
$forecastKg = [0,0,0];
$forecastRev = [0,0,0];

if(count($kgData) > 0){
  $lastKg = (float)$kgData[count($kgData)-1];
  $forecastKg = [
    round($lastKg * 1.03, 2),
    round($lastKg * 1.05, 2),
    round($lastKg * 1.08, 2),
  ];
}
if(count($revData) > 0){
  $lastRev = (float)$revData[count($revData)-1];
  $forecastRev = [
    round($lastRev * 1.03, 2),
    round($lastRev * 1.05, 2),
    round($lastRev * 1.08, 2),
  ];
}

// Avg price per kg (rough KPI)
$avgPricePerKg = ($totalKg12 > 0) ? ($totalRev12 / $totalKg12) : 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Analytics & Forecasting | Owner</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<style>
body { background:#f4f6f9; }
.sidebar { min-height:100vh; background:#2c3e50; }
.sidebar .nav-link { color:#fff; padding:10px 16px; border-radius:8px; font-size:.95rem; }
.sidebar .nav-link:hover, .sidebar .nav-link.active { background:#34495e; }

.modern-card { border-radius:14px; box-shadow:0 6px 16px rgba(0,0,0,.12); }
.main-content { padding-top:85px; }

.badge-soft { background: rgba(25,135,84,.15); color:#198754; }
</style>
</head>
<body>

<!-- TOP NAVBAR -->
<nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm fixed-top">
  <div class="container-fluid">
    <button class="btn btn-outline-dark d-lg-none" data-bs-toggle="collapse" data-bs-target="#sidebarMenu">☰</button>
    <span class="navbar-brand fw-bold ms-2">DO HIVES GENERAL MERCHANDISE</span>

    <div class="ms-auto dropdown">
      <a class="nav-link dropdown-toggle" data-bs-toggle="dropdown">
        <?= htmlspecialchars($username) ?> <small class="text-muted">(Owner)</small>
      </a>
      <ul class="dropdown-menu dropdown-menu-end">
        <li><a class="dropdown-item text-danger" href="../logout.php"><i class="fa-solid fa-right-from-bracket me-2"></i>Logout</a></li>
      </ul>
    </div>
  </div>
</nav>

<div class="container-fluid">
<div class="row">

<!-- SIDEBAR -->
<nav id="sidebarMenu" class="col-lg-2 d-lg-block sidebar collapse">
  <div class="pt-4">
    <ul class="nav flex-column gap-1">
      <li class="nav-item"><a class="nav-link" href="dashboard.php"><i class="fas fa-gauge-high me-2"></i>Owner Dashboard</a></li>
      <li class="nav-item"><a class="nav-link" href="inventory_monitoring.php"><i class="fas fa-boxes-stacked me-2"></i>Inventory Monitoring</a></li>
      <li class="nav-item"><a class="nav-link" href="sales_report.php"><i class="fas fa-receipt me-2"></i>Sales Reports</a></li>
      <li class="nav-item"><a class="nav-link" href="returns_report.php"><i class="fas fa-rotate-left me-2"></i>Returns Report</a></li>
      <li class="nav-item"><a class="nav-link active" href="analytics.php"><i class="fas fa-chart-line me-2"></i>Analytics & Forecasting</a></li>
      <li class="nav-item"><a class="nav-link" href="system_logs.php"><i class="fas fa-file-shield me-2"></i>System Logs</a></li>
    </ul>

    <div class="px-3 mt-4">
      <div class="alert alert-light small mb-0">
        <i class="fa-solid fa-circle-info me-1"></i> Owner access is <b>view-only</b>.
      </div>
    </div>
  </div>
</nav>

<!-- MAIN -->
<main class="col-lg-10 ms-sm-auto px-4 main-content">
<div class="py-4">

  <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
    <div>
      <h3 class="fw-bold mb-1">Analytics & Forecasting</h3>
      <div class="text-muted">Summary and trends based on the last 12 months (read-only).</div>
    </div>
    <button class="btn btn-outline-dark" onclick="window.print()"><i class="fa-solid fa-print me-1"></i> Print</button>
  </div>

  <!-- KPI ROW -->
  <div class="row g-3 mb-3">
    <div class="col-12 col-md-4">
      <div class="card modern-card">
        <div class="card-body">
          <div class="text-muted">Total Transactions (12 months)</div>
          <div class="h3 fw-bold mb-0"><?= $totalSales12 ?></div>
        </div>
      </div>
    </div>
    <div class="col-12 col-md-4">
      <div class="card modern-card">
        <div class="card-body">
          <div class="text-muted">Total Sold (kg) (12 months)</div>
          <div class="h3 fw-bold mb-0"><?= number_format($totalKg12,2) ?></div>
        </div>
      </div>
    </div>
    <div class="col-12 col-md-4">
      <div class="card modern-card">
        <div class="card-body">
          <div class="text-muted">Total Revenue (12 months)</div>
          <div class="h3 fw-bold mb-0">₱<?= number_format($totalRev12,2) ?></div>
          <div class="small text-muted">Avg price/kg: ₱<?= number_format($avgPricePerKg,2) ?></div>
        </div>
      </div>
    </div>
  </div>

  <!-- CHARTS -->
  <div class="row g-4">
    <div class="col-12 col-xl-7">
      <div class="card modern-card">
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-center mb-2">
            <h5 class="fw-bold mb-0">Sales Trend (kg)</h5>
            <span class="text-muted small">Last 12 months</span>
          </div>
          <canvas id="kgChart" height="120"></canvas>
          <div class="mt-3 text-muted small">
            <i class="fa-solid fa-info-circle me-1"></i>
            Shows total kg sold per month from sales_items.
          </div>
        </div>
      </div>
    </div>

    <div class="col-12 col-xl-5">
      <div class="card modern-card mb-4">
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-center mb-2">
            <h5 class="fw-bold mb-0">Revenue Trend (₱)</h5>
            <span class="text-muted small">Last 12 months</span>
          </div>
          <canvas id="revChart" height="140"></canvas>
        </div>
      </div>

      <div class="card modern-card">
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-center mb-2">
            <h5 class="fw-bold mb-0">Forecast (Placeholder)</h5>
            <span class="badge badge-soft">Coming soon</span>
          </div>
          <canvas id="forecastChart" height="140"></canvas>
          <div class="mt-3 text-muted small">
            <i class="fa-solid fa-flask me-1"></i>
            This is a simple projection based on last month. You can replace it later with your real forecasting method.
          </div>
        </div>
      </div>
    </div>
  </div>

</div>
</main>

</div>
</div>

<script>
const months = <?= json_encode($months) ?>;
const kgData = <?= json_encode($kgData) ?>;
const revData = <?= json_encode($revData) ?>;

new Chart(document.getElementById('kgChart'), {
  type: 'line',
  data: { labels: months, datasets: [{ data: kgData, tension: 0.35, fill: false }] },
  options: { plugins:{ legend:{display:false} }, scales:{ y:{ beginAtZero:true } } }
});

new Chart(document.getElementById('revChart'), {
  type: 'bar',
  data: { labels: months, datasets: [{ data: revData }] },
  options: { plugins:{ legend:{display:false} }, scales:{ y:{ beginAtZero:true } } }
});

new Chart(document.getElementById('forecastChart'), {
  type: 'bar',
  data: {
    labels: <?= json_encode($forecastLabels) ?>,
    datasets: [
      { label: 'Forecast kg', data: <?= json_encode($forecastKg) ?> },
      { label: 'Forecast revenue', data: <?= json_encode($forecastRev) ?> }
    ]
  },
  options: {
    plugins:{ legend:{ display:true } },
    scales:{ y:{ beginAtZero:true } }
  }
});
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
