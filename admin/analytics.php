<?php
session_start();
if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit;
}
$username = $_SESSION['username'] ?? 'User';
include '../config/db.php';

/* =========================
   SALES PER PRODUCT
========================= */
$salesPerProduct = [];
$sql = "
SELECT p.variety, SUM(si.qty_kg) AS total_sold
FROM sales_items si
JOIN sales s ON si.sale_id = s.sale_id
JOIN products p ON si.product_id = p.product_id
WHERE s.status != 'cancelled'
GROUP BY p.variety
ORDER BY total_sold DESC
";
$result = $conn->query($sql);
if($result){
  while($row = $result->fetch_assoc()){
      $salesPerProduct[] = $row;
  }
}

/* =========================
   SALES OVER TIME (MONTHLY)
========================= */
$months = [];
$salesData = [];
$sql = "
SELECT DATE_FORMAT(s.sale_date,'%b %Y') AS month,
       SUM(si.qty_kg) AS total
FROM sales_items si
JOIN sales s ON si.sale_id = s.sale_id
WHERE s.status != 'cancelled'
GROUP BY YEAR(s.sale_date), MONTH(s.sale_date)
ORDER BY YEAR(s.sale_date), MONTH(s.sale_date)
";
$result = $conn->query($sql);
if($result){
  while($row = $result->fetch_assoc()){
      $months[] = $row['month'];
      $salesData[] = (float)$row['total'];
  }
}

/* =========================
   SUMMARY DATA
========================= */
$topProduct = $salesPerProduct[0]['variety'] ?? 'N/A';
$totalSales = array_sum($salesData);

$growth = 0;
$count = count($salesData);
if($count >= 2 && $salesData[$count-2] > 0){
    $growth = (($salesData[$count-1] - $salesData[$count-2]) / $salesData[$count-2]) * 100;
}

/* =========================
   FORECAST PLACEHOLDER (NEXT 3 MONTHS)
   Method: Simple Moving Average (SMA)
   - If not enough data, use demo values
========================= */
function nextMonthsLabels($n = 3){
    $labels = [];
    $dt = new DateTime('first day of this month');
    for($i=1;$i<=$n;$i++){
        $dt->modify('+1 month');
        $labels[] = $dt->format('M Y');
    }
    return $labels;
}

$forecastLabels = nextMonthsLabels(3);
$forecastData = [];

// Simple Moving Average window (last 3 months)
$window = 3;

if(count($salesData) >= 3){
    $last = $salesData;
    for($i=0;$i<3;$i++){
        $slice = array_slice($last, -$window);
        $avg = array_sum($slice) / count($slice);
        $forecastData[] = round($avg, 2);
        $last[] = $avg; // extend series
    }
} elseif(count($salesData) > 0) {
    // Not enough history: use last known month as baseline
    $baseline = end($salesData);
    $forecastData = [round($baseline,2), round($baseline,2), round($baseline,2)];
} else {
    // No history at all: placeholder demo numbers
    $forecastData = [120.00, 130.00, 125.00];
}

// Combined labels + values for a single chart with forecast continuation
$combinedLabels = array_merge($months, $forecastLabels);
$combinedActual = $salesData;

// Pad actual with nulls so the actual line stops before forecast begins
$combinedActualPadded = array_merge($combinedActual, array_fill(0, count($forecastLabels), null));

// Forecast line: put nulls for past months, then forecast values
$combinedForecastPadded = array_merge(array_fill(0, count($months), null), $forecastData);

// Forecast table rows
$forecastTable = [];
for($i=0;$i<count($forecastLabels);$i++){
    $forecastTable[] = [
        'month' => $forecastLabels[$i],
        'pred' => $forecastData[$i]
    ];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Smart Analytics | DOHIVES</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<style>
body { background:#f4f6f9; }

/* Sidebar */
.sidebar { min-height:100vh; background:#2c3e50; }
.sidebar .nav-link { color:#fff; padding:10px 16px; border-radius:8px; font-size:.95rem; }
.sidebar .nav-link:hover, .sidebar .nav-link.active { background:#34495e; }

/* Dropdown submenu */
.sidebar .submenu { padding-left:35px; }
.sidebar .submenu a { font-size:.9rem; padding:6px 0; display:block; color:#ecf0f1; text-decoration:none; }
.sidebar .submenu a:hover { color:#fff; }

/* Main */
.main-content { padding-top:85px; }

/* Analytics */
.analytics-row { display:grid; grid-template-columns:1fr 1fr; gap:20px; }
.analytics-box {
    background:#fff;
    padding:20px;
    border-radius:14px;
    box-shadow:0 6px 16px rgba(0,0,0,.12);
}
.bar { background:#eaeaea; border-radius:6px; overflow:hidden; }
.bar div { background:#2f5bff; color:#fff; padding:4px 8px; font-size:.8rem; }

.analytics-summary { display:flex; gap:40px; margin-top:30px; flex-wrap:wrap; }
.positive { color:green; }

.small-note {
    font-size:.85rem;
    color:#6c757d;
}

@media (max-width: 992px){
  .analytics-row{ grid-template-columns:1fr; }
}
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
        <?= htmlspecialchars($username) ?>
      </a>
      <ul class="dropdown-menu dropdown-menu-end">
        <li><a class="dropdown-item" href="profile.php">Profile</a></li>
        <li><a class="dropdown-item text-danger" href="logout.php">Logout</a></li>
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
<li class="nav-item"><a class="nav-link" href="dashboard.php"><i class="fas fa-home me-2"></i>Dashboard</a></li>

<li class="nav-item">
<a class="nav-link" data-bs-toggle="collapse" href="#inventoryMenu">
<i class="fas fa-warehouse me-2"></i>Inventory <i class="fas fa-chevron-down float-end"></i>
</a>
<div class="collapse submenu" id="inventoryMenu">
<a href="products.php">Products</a>
<a href="../inventory/add_stock.php">Add Stock</a>
<a href="../inventory/adjust_stock.php">Adjust Stock</a>
<a href="../inventory/inventory.php">Inventory Logs</a>
</div>
</li>


<li class="nav-item">
<a class="nav-link" href="users.php"><i class="fas fa-users me-2"></i>User Management</a>
</li>

<li class="nav-item"><a class="nav-link" href="sales.php"><i class="fas fa-cash-register me-2"></i>Sales</a></li>
<li class="nav-item"><a class="nav-link active" href="analytics.php"><i class="fas fa-chart-line me-2"></i>Analytics & Forecasting</a></li>
<li class="nav-item"><a class="nav-link" href="../admin/system_logs.php"><i class="fas fa-archive me-2"></i>System Logs</a></li>
</ul>
</div>
</nav>

<!-- MAIN CONTENT -->
<main class="col-lg-10 ms-sm-auto px-4 main-content">

<h3 class="fw-bold mb-2">Smart Analytics</h3>
<p>Sales trends, forecasting, and reports</p>

<div class="analytics-row">

<!-- SALES PER PRODUCT -->
<div class="analytics-box">
  <h5 class="fw-bold mb-3">Sales per Product (kg)</h5>

  <?php if(empty($salesPerProduct)): ?>
    <div class="alert alert-warning mb-0">
      No sales data yet. This section will populate once transactions are recorded.
    </div>
  <?php else: ?>
    <?php
      $maxSold = max(array_map(fn($r)=>(float)$r['total_sold'], $salesPerProduct));
      if($maxSold <= 0) $maxSold = 1;
    ?>
    <?php foreach($salesPerProduct as $row): ?>
      <?php
        $pct = ((float)$row['total_sold'] / $maxSold) * 100;
      ?>
      <div class="mb-2">
        <span><?= htmlspecialchars($row['variety']) ?></span>
        <div class="bar">
          <div style="width:<?= max(5, min(100, $pct)) ?>%">
            <?= number_format((float)$row['total_sold'],2) ?> kg
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  <?php endif; ?>
</div>

<!-- SALES + FORECAST CHART -->
<div class="analytics-box">
  <div class="d-flex justify-content-between align-items-start">
    <div>
      <h5 class="fw-bold mb-1">Sales Over Time + Forecast</h5>
      <div class="small-note">
        Forecast is a placeholder (Simple Moving Average). Replace later with your forecasting model/data gathering.
      </div>
    </div>
  </div>
  <canvas id="salesChart" height="180"></canvas>

  <!-- Forecast Table -->
  <div class="mt-3">
    <h6 class="fw-bold mb-2">Forecast (Next 3 Months)</h6>
    <div class="table-responsive">
      <table class="table table-sm table-bordered mb-0">
        <thead class="table-light">
          <tr>
            <th>Month</th>
            <th>Predicted Demand (kg)</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach($forecastTable as $f): ?>
            <tr>
              <td><?= htmlspecialchars($f['month']) ?></td>
              <td><?= number_format((float)$f['pred'], 2) ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

</div>

<!-- SUMMARY -->
<div class="analytics-summary">
  <div>
    <small>Growth (Last vs Prev Month)</small>
    <h3 class="<?= $growth >= 0 ? 'positive' : 'text-danger' ?>">
      <?= number_format($growth,1) ?>%
    </h3>
  </div>
  <div>
    <small>Top Sales Product</small>
    <h3><?= htmlspecialchars($topProduct) ?></h3>
  </div>
  <div>
    <small>Total Sold</small>
    <h3><?= number_format((float)$totalSales,2) ?> kg</h3>
  </div>
  <div>
    <small>Next Month Forecast</small>
    <h3><?= number_format((float)$forecastData[0],2) ?> kg</h3>
  </div>
</div>

</main>
</div>
</div>

<script>
new Chart(document.getElementById('salesChart'), {
  type: 'line',
  data: {
    labels: <?= json_encode($combinedLabels) ?>,
    datasets: [
      {
        label: 'Actual',
        data: <?= json_encode($combinedActualPadded) ?>,
        borderColor: '#2f5bff',
        tension: 0.4,
        fill: false
      },
      {
        label: 'Forecast (Placeholder)',
        data: <?= json_encode($combinedForecastPadded) ?>,
        borderColor: '#fd7e14',
        borderDash: [6,6],
        tension: 0.4,
        fill: false
      }
    ]
  },
  options: {
    plugins: { legend: { display: true } },
    scales: { y: { beginAtZero: true } }
  }
});
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
