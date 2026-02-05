<?php
session_start();
if(!isset($_SESSION['user_id'])){
  header("Location: ../login.php");
  exit;
}
if(strtolower($_SESSION['role'] ?? '') !== 'admin'){
  header("Location: ../login.php");
  exit;
}

$username = $_SESSION['username'] ?? 'Admin';
include '../config/db.php';

/* =========================================================
  SETTINGS (based on client interview)
  - lead time: usually 1-2 days (client said)
  - they want system to suggest how much to order next time
========================================================= */
$LEAD_TIME_DAYS   = 2;    // default supplier lead time (edit later if you add supplier module)
$SAFETY_STOCK_KG  = 10;   // buffer stock
$TARGET_DAYS_COVER= 7;    // target days of stock cover when ordering
$RESTOCK_LOOKBACK_DAYS = 30; // compute avg daily sales from last 30 days

/* =========================
   SALES PER PRODUCT (kg) - ALL TIME
========================= */
$salesPerProduct = [];
$sql = "
  SELECT 
    p.product_id,
    CONCAT(p.variety,' - ',p.grade) AS product_label,
    SUM(si.qty_kg) AS total_sold
  FROM sales_items si
  JOIN sales s ON si.sale_id = s.sale_id
  JOIN products p ON si.product_id = p.product_id
  WHERE p.archived=0
    AND (s.status IS NULL OR LOWER(s.status) <> 'cancelled')
  GROUP BY p.product_id
  ORDER BY total_sold DESC
";
$result = $conn->query($sql);
if($result){
  while($row = $result->fetch_assoc()){
    $salesPerProduct[] = $row;
  }
}

/* =========================
   SALES OVER TIME (MONTHLY) - ACTUAL
========================= */
$months = [];
$salesData = [];

$sql = "
  SELECT 
    DATE_FORMAT(s.sale_date,'%b %Y') AS month_label,
    YEAR(s.sale_date) AS y,
    MONTH(s.sale_date) AS m,
    COALESCE(SUM(si.qty_kg),0) AS total_kg
  FROM sales_items si
  JOIN sales s ON si.sale_id = s.sale_id
  WHERE (s.status IS NULL OR LOWER(s.status) <> 'cancelled')
  GROUP BY y,m,month_label
  ORDER BY y,m
";
$result = $conn->query($sql);
if($result){
  while($row = $result->fetch_assoc()){
    $months[] = $row['month_label'];
    $salesData[] = (float)$row['total_kg'];
  }
}

/* =========================
   KPI SUMMARY
========================= */
$topProduct = $salesPerProduct[0]['product_label'] ?? 'N/A';
$totalSoldKg = array_sum($salesData);
$totalMonths = count($salesData);

$growth = 0;
if($totalMonths >= 2 && (float)$salesData[$totalMonths-2] > 0){
  $growth = (($salesData[$totalMonths-1] - $salesData[$totalMonths-2]) / $salesData[$totalMonths-2]) * 100;
}

/* =========================
   Forecast (Next 3 months) - SMA
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
$window = 3;

if(count($salesData) >= 3){
  $series = $salesData;
  for($i=0;$i<3;$i++){
    $slice = array_slice($series, -$window);
    $avg = array_sum($slice) / max(1,count($slice));
    $forecastData[] = round($avg, 2);
    $series[] = $avg;
  }
} elseif(count($salesData) > 0){
  $baseline = (float)end($salesData);
  $forecastData = [round($baseline,2), round($baseline,2), round($baseline,2)];
} else {
  $forecastData = [120.00,130.00,125.00];
}

$combinedLabels = array_merge($months, $forecastLabels);
$actualPadded   = array_merge($salesData, array_fill(0, count($forecastLabels), null));
$forecastPadded = array_merge(array_fill(0, count($months), null), $forecastData);

$forecastTable = [];
for($i=0; $i<count($forecastLabels); $i++){
  $forecastTable[] = ['month'=>$forecastLabels[$i], 'pred'=>$forecastData[$i]];
}

/* =========================================================
   NEW (CLIENT-BASED): Sales by Day of Week
   - client said Saturday highest, holidays higher
========================================================= */
$daysOrder = ['Mon','Tue','Wed','Thu','Fri','Sat','Sun'];
$daySalesMap = array_fill_keys($daysOrder, 0.0);

// last 90 days for a good pattern snapshot
$sql = "
  SELECT
    DAYOFWEEK(s.sale_date) AS dow,
    SUM(si.qty_kg) AS total_kg
  FROM sales_items si
  JOIN sales s ON si.sale_id = s.sale_id
  WHERE (s.status IS NULL OR LOWER(s.status) <> 'cancelled')
    AND s.sale_date >= (CURDATE() - INTERVAL 90 DAY)
  GROUP BY dow
";
$res = $conn->query($sql);
if($res){
  while($r = $res->fetch_assoc()){
    $dow = (int)$r['dow']; // 1=Sun ... 7=Sat
    $kg  = (float)$r['total_kg'];
    // Map to Mon..Sun
    $label = 'Sun';
    if($dow===2) $label='Mon';
    if($dow===3) $label='Tue';
    if($dow===4) $label='Wed';
    if($dow===5) $label='Thu';
    if($dow===6) $label='Fri';
    if($dow===7) $label='Sat';
    if($dow===1) $label='Sun';
    $daySalesMap[$label] = $kg;
  }
}
$daySalesLabels = array_keys($daySalesMap);
$daySalesData   = array_values($daySalesMap);

// Peak day
$peakDay = 'N/A';
$peakKg  = 0;
foreach($daySalesMap as $d=>$kg){
  if($kg > $peakKg){ $peakKg=$kg; $peakDay=$d; }
}

/* =========================================================
   NEW (CLIENT-BASED): Restock Suggestions (last 30 days)
   - client wants system to suggest how much to order
   - uses current stock from inventory_transactions
========================================================= */
$restockRows = [];
$fastMovers = [];
$slowMovers = [];

// One query to get stock + last 30 days sold per product
$sql = "
SELECT
  p.product_id,
  CONCAT(p.variety,' - ',p.grade) AS product_label,
  p.sku,
  /* CURRENT STOCK */
  IFNULL(SUM(
    CASE
      WHEN LOWER(it.type)='in' THEN it.qty_kg
      WHEN LOWER(it.type)='out' THEN -it.qty_kg
      WHEN LOWER(it.type)='adjust' THEN it.qty_kg
      ELSE 0
    END
  ), 0) AS current_stock,

  /* SOLD LAST 30 DAYS */
  (
    SELECT IFNULL(SUM(si.qty_kg),0)
    FROM sales_items si
    JOIN sales s ON s.sale_id = si.sale_id
    WHERE si.product_id = p.product_id
      AND (s.status IS NULL OR LOWER(s.status) <> 'cancelled')
      AND s.sale_date >= (CURDATE() - INTERVAL {$RESTOCK_LOOKBACK_DAYS} DAY)
  ) AS sold_last_30

FROM products p
LEFT JOIN inventory_transactions it ON it.product_id = p.product_id
WHERE p.archived=0
GROUP BY p.product_id
ORDER BY sold_last_30 DESC
";
$res = $conn->query($sql);
$allProdForRestock = [];
if($res){
  while($r = $res->fetch_assoc()){
    $allProdForRestock[] = $r;
  }
}

// Build restock recommendations
foreach($allProdForRestock as $r){
  $current = (float)$r['current_stock'];
  $sold30  = (float)$r['sold_last_30'];

  $avgDaily = $sold30 / max(1, $RESTOCK_LOOKBACK_DAYS);
  $reorderPoint = ($avgDaily * $LEAD_TIME_DAYS) + $SAFETY_STOCK_KG;
  $targetStock  = ($avgDaily * $TARGET_DAYS_COVER);

  $suggested = $targetStock - $current;
  if($suggested < 0) $suggested = 0;

  $status = 'OK';
  if($current <= 0) $status = 'OUT';
  else if($current <= $reorderPoint) $status = 'REORDER';

  $r['_avgDaily']     = $avgDaily;
  $r['_reorderPoint'] = $reorderPoint;
  $r['_suggested']    = $suggested;
  $r['_status']       = $status;

  $allProdForRestock = $allProdForRestock; // no-op
}

// Top 5 restock priority (OUT/REORDER first, then by lowest coverage)
$restockCandidates = [];
foreach($allProdForRestock as $r){
  $avgDaily = (float)($r['_avgDaily'] ?? 0);
  $current  = (float)$r['current_stock'];
  $daysCover = ($avgDaily > 0) ? ($current / $avgDaily) : 9999;

  if(($r['_status'] ?? 'OK') !== 'OK'){
    $r['_daysCover'] = $daysCover;
    $restockCandidates[] = $r;
  }
}

usort($restockCandidates, function($a,$b){
  // OUT first, then REORDER, then by lower days cover
  $prio = ['OUT'=>0,'REORDER'=>1,'OK'=>2];
  $pa = $prio[$a['_status']] ?? 9;
  $pb = $prio[$b['_status']] ?? 9;
  if($pa !== $pb) return $pa <=> $pb;

  $da = (float)($a['_daysCover'] ?? 9999);
  $db = (float)($b['_daysCover'] ?? 9999);
  return $da <=> $db;
});

$restockRows = array_slice($restockCandidates, 0, 5);

// Fast movers = top 5 sold_last_30
$fastMovers = array_slice($allProdForRestock, 0, 5);

// Slow movers = bottom 5 sold_last_30 (but still active products)
$slowMovers = $allProdForRestock;
usort($slowMovers, fn($a,$b)=> (float)$a['sold_last_30'] <=> (float)$b['sold_last_30']);
$slowMovers = array_slice($slowMovers, 0, 5);

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Analytics & Forecasting | Admin</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<link href="../css/layout.css" rel="stylesheet">
</head>
<body>

<?php include '../includes/topnav.php'; ?>

<div class="container-fluid">
<div class="row">

<?php include '../includes/admin_sidebar.php'; ?>

<!-- MAIN -->
<main class="col-lg-10 ms-sm-auto px-4 main-content">
  <div class="py-4">

    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
      <div>
        <h3 class="fw-bold mb-1">Analytics & Forecasting</h3>
        <div class="text-muted">Trends + forecasting + restock suggestions</div>
      </div>
      <button class="btn btn-outline-dark" onclick="window.print()"><i class="fa-solid fa-print me-1"></i> Print</button>
    </div>

    <!-- KPI -->
    <div class="row g-3 mb-3">
      <div class="col-12 col-md-3">
        <div class="card card-soft">
          <div class="card-body">
            <div class="text-muted small">Total Sold (All Time)</div>
            <div class="h3 fw-bold mb-0"><?= number_format((float)$totalSoldKg,2) ?> kg</div>
          </div>
        </div>
      </div>
      <div class="col-12 col-md-3">
        <div class="card card-soft">
          <div class="card-body">
            <div class="text-muted small">Top Product (All Time)</div>
            <div class="h6 fw-bold mb-0"><?= htmlspecialchars($topProduct) ?></div>
          </div>
        </div>
      </div>
      <div class="col-12 col-md-3">
        <div class="card card-soft">
          <div class="card-body">
            <div class="text-muted small">Monthly Growth</div>
            <div class="h3 fw-bold mb-0"><?= number_format((float)$growth,1) ?>%</div>
          </div>
        </div>
      </div>
      <div class="col-12 col-md-3">
        <div class="card card-soft">
          <div class="card-body">
            <div class="text-muted small">Next Month Forecast</div>
            <div class="h3 fw-bold mb-0"><?= number_format((float)$forecastData[0],2) ?> kg</div>
          </div>
        </div>
      </div>
    </div>

    <!-- NEW: Day of Week + Restock -->
    <div class="analytics-row mb-3">

      <!-- DAY OF WEEK -->
      <div class="analytics-box">
        <div class="d-flex justify-content-between align-items-start">
          <div>
            <h5 class="fw-bold mb-1">Sales by Day of Week (Last 90 Days)</h5>
            <div class="small-note"> Peak: <b><?= htmlspecialchars($peakDay) ?></b> (<?= number_format((float)$peakKg,2) ?> kg)</div>
          </div>
        </div>
        <canvas id="dowChart" height="180"></canvas>
      </div>

      <!-- RESTOCK SUGGESTIONS -->
      <div class="analytics-box">
        <h5 class="fw-bold mb-1">Suggested Restock (Top Priority)</h5>
        <div class="small-note mb-2">
          Uses Avg Daily Sales (last <?= (int)$RESTOCK_LOOKBACK_DAYS ?> days), Lead Time = <?= (int)$LEAD_TIME_DAYS ?> day(s), Safety Stock = <?= number_format($SAFETY_STOCK_KG,0) ?>kg.
        </div>

        <?php if(empty($restockRows)): ?>
          <div class="alert alert-success mb-0">
            No urgent restocks right now (based on current data).
          </div>
        <?php else: ?>
          <div class="table-responsive">
            <table class="table table-sm table-bordered align-middle mb-0">
              <thead class="table-light">
                <tr>
                  <th>Product</th>
                  <th>SKU</th>
                  <th class="text-end">Stock</th>
                  <th class="text-end">Avg/Day</th>
                  <th class="text-end">Reorder Point</th>
                  <th class="text-end">Suggested Order</th>
                  <th>Status</th>
                </tr>
              </thead>
              <tbody>
              <?php foreach($restockRows as $r): ?>
                <?php
                  $badge = "success";
                  if(($r['_status'] ?? 'OK') === 'REORDER') $badge = "warning";
                  if(($r['_status'] ?? 'OK') === 'OUT') $badge = "danger";
                ?>
                <tr>
                  <td class="fw-semibold"><?= htmlspecialchars($r['product_label']) ?></td>
                  <td><?= htmlspecialchars($r['sku']) ?></td>
                  <td class="text-end"><?= number_format((float)$r['current_stock'],2) ?></td>
                  <td class="text-end"><?= number_format((float)$r['_avgDaily'],2) ?></td>
                  <td class="text-end"><?= number_format((float)$r['_reorderPoint'],2) ?></td>
                  <td class="text-end fw-bold"><?= number_format((float)$r['_suggested'],2) ?></td>
                  <td><span class="badge bg-<?= $badge ?>"><?= htmlspecialchars($r['_status']) ?></span></td>
                </tr>
              <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php endif; ?>
      </div>

    </div>

    <!-- Existing: Sales per Product + Sales over time -->
    <div class="analytics-row">

      <!-- SALES PER PRODUCT -->
      <div class="analytics-box">
        <h5 class="fw-bold mb-3">Sales per Product (All Time, kg)</h5>

        <?php if(empty($salesPerProduct)): ?>
          <div class="alert alert-warning mb-0">
            No sales data yet. This section will populate once transactions are recorded.
          </div>
        <?php else: ?>
          <?php
            $maxSold = max(array_map(fn($r)=>(float)$r['total_sold'], $salesPerProduct));
            if($maxSold <= 0) $maxSold = 1;
          ?>
          <?php foreach(array_slice($salesPerProduct, 0, 10) as $row): ?>
            <?php $pct = ((float)$row['total_sold'] / $maxSold) * 100; ?>
            <div class="mb-3">
              <div class="d-flex justify-content-between">
                <span class="fw-semibold"><?= htmlspecialchars($row['product_label']) ?></span>
                <span class="text-muted small"><?= number_format((float)$row['total_sold'],2) ?> kg</span>
              </div>
              <div class="bar">
                <div style="width:<?= max(2, min(100, $pct)) ?>%"></div>
              </div>
            </div>
          <?php endforeach; ?>
          <div class="small-note">Showing top 10 products (all time).</div>
        <?php endif; ?>
      </div>

      <!-- SALES + FORECAST -->
      <div class="analytics-box">
        <h5 class="fw-bold mb-1">Sales Over Time + Forecast</h5>
        <div class="small-note mb-2">Forecast uses Simple Moving Average (SMA).</div>

        <canvas id="salesChart" height="180"></canvas>

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
                    <td><?= number_format((float)$f['pred'],2) ?></td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>

      </div>

    </div>

    <!-- NEW: Fast/Slow movers -->
    <div class="analytics-row mt-3">
      <div class="analytics-box">
        <h5 class="fw-bold mb-1">Fast Moving Products (Last <?= (int)$RESTOCK_LOOKBACK_DAYS ?> Days)</h5>
        <div class="table-responsive">
          <table class="table table-sm table-bordered align-middle mb-0">
            <thead class="table-light">
              <tr>
                <th>Product</th>
                <th>SKU</th>
                <th class="text-end">Sold (<?= (int)$RESTOCK_LOOKBACK_DAYS ?>d)</th>
                <th class="text-end">Current Stock</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach($fastMovers as $r): ?>
                <tr>
                  <td class="fw-semibold"><?= htmlspecialchars($r['product_label']) ?></td>
                  <td><?= htmlspecialchars($r['sku']) ?></td>
                  <td class="text-end"><?= number_format((float)$r['sold_last_30'],2) ?></td>
                  <td class="text-end"><?= number_format((float)$r['current_stock'],2) ?></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>

      <div class="analytics-box">
        <h5 class="fw-bold mb-1">Slow Moving Products (Last <?= (int)$RESTOCK_LOOKBACK_DAYS ?> Days)</h5>
        <div class="small-note mb-2">Useful to avoid overstock/spoilage</div>
        <div class="table-responsive">
          <table class="table table-sm table-bordered align-middle mb-0">
            <thead class="table-light">
              <tr>
                <th>Product</th>
                <th>SKU</th>
                <th class="text-end">Sold (<?= (int)$RESTOCK_LOOKBACK_DAYS ?>d)</th>
                <th class="text-end">Current Stock</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach($slowMovers as $r): ?>
                <tr>
                  <td class="fw-semibold"><?= htmlspecialchars($r['product_label']) ?></td>
                  <td><?= htmlspecialchars($r['sku']) ?></td>
                  <td class="text-end"><?= number_format((float)$r['sold_last_30'],2) ?></td>
                  <td class="text-end"><?= number_format((float)$r['current_stock'],2) ?></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>

  </div>
</main>

</div>
</div>

<script>
  window.ADMIN_ANALYTICS_DATA = <?= json_encode([
    'combinedLabels' => $combinedLabels,
    'actualPadded'   => $actualPadded,
    'forecastPadded' => $forecastPadded,
    'daySalesLabels' => $daySalesLabels,
    'daySalesData'   => $daySalesData,
  ], JSON_UNESCAPED_SLASHES) ?>;
</script>

<script src="../js/admin_analytics.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
