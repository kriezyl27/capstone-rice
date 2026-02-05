<?php
session_start();
if(!isset($_SESSION['user_id'])){ header("Location: ../login.php"); exit; }
if(strtolower($_SESSION['role'] ?? '') !== 'owner'){ header("Location: ../login.php"); exit; }

$username = $_SESSION['username'] ?? 'Owner';
include '../config/db.php';

/* =========================================================
  OWNER ANALYTICS (Interview-based)
  Adds:
  ✅ Sales by Day of Week (last 90 days)
  ✅ Suggested Restock (top priority, view-only)
  ✅ Current LOW/OUT counts (decision KPIs)
  Keeps:
  ✅ 12-month KG + Revenue trends
  ✅ 3-month forecast (MA + trend)
  ✅ 30-day product movement (fast/slow)
========================================================= */

/* -------------------------
  Settings (edit anytime)
------------------------- */
$LOW_STOCK_THRESHOLD   = 10;   // kg
$LEAD_TIME_DAYS        = 2;    // supplier lead time (client said usually 1-2 days)
$SAFETY_STOCK_KG       = 10;   // buffer
$TARGET_DAYS_COVER     = 7;    // target stock cover
$RESTOCK_LOOKBACK_DAYS = 30;   // use last 30 days demand

/* =========================
   MONTHLY (Last 12 months)
========================= */
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

$totalKg12    = (float)($summary['total_kg'] ?? 0);
$totalRev12   = (float)($summary['total_rev'] ?? 0);
$totalSales12 = (int)($summary['total_sales'] ?? 0);

$avgPricePerKg = ($totalKg12 > 0) ? ($totalRev12 / $totalKg12) : 0;
$avgKgPerMonth  = (count($kgData) > 0) ? array_sum($kgData)/count($kgData) : 0;
$avgRevPerMonth = (count($revData) > 0) ? array_sum($revData)/count($revData) : 0;

$bestMonthLabel = '—';
$bestMonthRev   = 0;
if(count($revData) > 0){
  $bestIdx = array_keys($revData, max($revData))[0];
  $bestMonthLabel = $months[$bestIdx] ?? '—';
  $bestMonthRev = (float)$revData[$bestIdx];
}

/* =========================
   FORECASTING (MA + trend)
========================= */
function movingAverage(array $arr, int $window): float {
  $n = count($arr);
  if($n === 0) return 0;
  $slice = array_slice($arr, max(0, $n-$window), $window);
  return (count($slice) > 0) ? array_sum($slice)/count($slice) : 0;
}

function simpleTrendSlope(array $arr, int $points = 6): float {
  $n = count($arr);
  if($n < 2) return 0;
  $slice = array_slice($arr, max(0, $n-$points), $points);
  $m = count($slice);
  if($m < 2) return 0;
  $first = (float)$slice[0];
  $last  = (float)$slice[$m-1];
  return ($last - $first) / ($m - 1);
}

$forecastLabels = ["Next Month", "+2 Months", "+3 Months"];
$forecastKg  = [0,0,0];
$forecastRev = [0,0,0];

if(count($kgData) > 0){
  $maKg = movingAverage($kgData, 3);
  $slKg = simpleTrendSlope($kgData, 6);
  $forecastKg = [
    round(max(0, $maKg + $slKg*1), 2),
    round(max(0, $maKg + $slKg*2), 2),
    round(max(0, $maKg + $slKg*3), 2),
  ];
}
if(count($revData) > 0){
  $maRev = movingAverage($revData, 3);
  $slRev = simpleTrendSlope($revData, 6);
  $forecastRev = [
    round(max(0, $maRev + $slRev*1), 2),
    round(max(0, $maRev + $slRev*2), 2),
    round(max(0, $maRev + $slRev*3), 2),
  ];
}

/* =========================
   PRODUCT MOVEMENT (Last 30 days)
========================= */
$topProducts = $conn->query("
  SELECT 
    p.product_id,
    CONCAT(p.variety,' - ',p.grade) AS product_name,
    p.sku,
    COALESCE(SUM(si.qty_kg),0) AS kg_sold_30,
    COALESCE(SUM(si.line_total),0) AS rev_30
  FROM sales s
  JOIN sales_items si ON si.sale_id = s.sale_id
  JOIN products p ON p.product_id = si.product_id
  WHERE s.sale_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
    AND (s.status IS NULL OR LOWER(s.status) <> 'cancelled')
  GROUP BY p.product_id, product_name, p.sku
  ORDER BY kg_sold_30 DESC
  LIMIT 10
");

$prodRows = [];
$kgList = [];
if($topProducts){
  while($r = $topProducts->fetch_assoc()){
    $kg = (float)$r['kg_sold_30'];
    $prodRows[] = $r;
    $kgList[] = $kg;
  }
}

$fastThreshold = 0;
$slowThreshold = 0;
if(count($kgList) > 0){
  $sorted = $kgList;
  sort($sorted);
  $n = count($sorted);
  $slowIdx = (int)floor(($n-1) * 0.30);
  $fastIdx = (int)floor(($n-1) * 0.70);
  $slowThreshold = (float)$sorted[$slowIdx];
  $fastThreshold = (float)$sorted[$fastIdx];
}

$insight = "No sales data in the last 30 days yet.";
if(count($prodRows) > 0){
  $insight = "Movement is computed from the last 30 days sales. Fast-moving items may need earlier restocking; slow-moving items may indicate overstock risk if warehouse stock is high.";
}

/* =========================================================
   NEW: Current Stock per Product + LOW/OUT counts
   stock = SUM(in) - SUM(out) + SUM(adjust)
========================================================= */
$stockRows = [];
$outCount = 0;
$lowCount = 0;

$stockRes = $conn->query("
  SELECT
    p.product_id,
    CONCAT(p.variety,' - ',p.grade) AS product_label,
    p.sku,
    IFNULL(SUM(
      CASE
        WHEN LOWER(it.type)='in' THEN it.qty_kg
        WHEN LOWER(it.type)='out' THEN -it.qty_kg
        WHEN LOWER(it.type)='adjust' THEN it.qty_kg
        ELSE 0
      END
    ), 0) AS current_stock
  FROM products p
  LEFT JOIN inventory_transactions it ON it.product_id = p.product_id
  WHERE p.archived=0
  GROUP BY p.product_id
");
if($stockRes){
  while($r = $stockRes->fetch_assoc()){
    $st = (float)$r['current_stock'];
    $stockRows[] = $r;
    if($st <= 0) $outCount++;
    else if($st <= $LOW_STOCK_THRESHOLD) $lowCount++;
  }
}

/* =========================================================
   NEW: Sales by Day of Week (Last 90 days)
========================================================= */
$daysOrder = ['Mon','Tue','Wed','Thu','Fri','Sat','Sun'];
$daySalesMap = array_fill_keys($daysOrder, 0.0);

$dowRes = $conn->query("
  SELECT
    DAYOFWEEK(s.sale_date) AS dow,
    SUM(si.qty_kg) AS total_kg
  FROM sales_items si
  JOIN sales s ON si.sale_id = s.sale_id
  WHERE (s.status IS NULL OR LOWER(s.status) <> 'cancelled')
    AND s.sale_date >= (CURDATE() - INTERVAL 90 DAY)
  GROUP BY dow
");
if($dowRes){
  while($r = $dowRes->fetch_assoc()){
    $dow = (int)$r['dow']; // 1=Sun ... 7=Sat
    $kg  = (float)$r['total_kg'];
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

$peakDay = 'N/A'; $peakKg = 0;
foreach($daySalesMap as $d=>$kg){
  if($kg > $peakKg){ $peakKg = $kg; $peakDay = $d; }
}

/* =========================================================
   NEW: Suggested Restock (Top Priority, view-only)
   - last 30 days sold per product
   - reorder point using lead time + safety stock
========================================================= */
$restockRows = [];

$restockRes = $conn->query("
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
");

$calcRows = [];
if($restockRes){
  while($r = $restockRes->fetch_assoc()){
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

    $daysCover = ($avgDaily > 0) ? ($current / $avgDaily) : 9999;

    $r['_avgDaily']     = $avgDaily;
    $r['_reorderPoint'] = $reorderPoint;
    $r['_suggested']    = $suggested;
    $r['_status']       = $status;
    $r['_daysCover']    = $daysCover;

    if($status !== 'OK'){
      $calcRows[] = $r;
    }
  }
}

// Sort priority: OUT first, then REORDER, then least days cover
usort($calcRows, function($a,$b){
  $prio = ['OUT'=>0,'REORDER'=>1,'OK'=>2];
  $pa = $prio[$a['_status']] ?? 9;
  $pb = $prio[$b['_status']] ?? 9;
  if($pa !== $pb) return $pa <=> $pb;

  return (float)$a['_daysCover'] <=> (float)$b['_daysCover'];
});

$restockRows = array_slice($calcRows, 0, 5);

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

<link href="../css/layout.css" rel="stylesheet">
</head>
<body>

<!-- TOP NAVBAR -->
<nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm fixed-top">
  <div class="container-fluid">
    <button class="btn btn-outline-dark d-lg-none" data-bs-toggle="collapse" data-bs-target="#sidebarMenu">☰</button>
    <span class="navbar-brand fw-bold ms-2">DE ORO HIYS GENERAL MERCHANDISE</span>

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

<?php include '../includes/owner_sidebar.php'; ?>

<!-- MAIN -->
<main class="col-lg-10 ms-sm-auto px-4 main-content">
<div class="py-4">

  <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
    <div>
      <h3 class="fw-bold mb-1">Analytics & Forecasting</h3>
      <div class="text-muted">Trends + forecast + decision insights (read-only).</div>
    </div>
    <button class="btn btn-outline-dark" onclick="window.print()"><i class="fa-solid fa-print me-1"></i> Print</button>
  </div>

  <!-- KPI ROW -->
  <div class="row g-3 mb-3">
    <div class="col-12 col-md-3">
      <div class="card modern-card">
        <div class="card-body">
          <div class="text-muted">Total Transactions (12 months)</div>
          <div class="h3 fw-bold mb-0"><?= $totalSales12 ?></div>
          <div class="small text-muted">Avg/month: <?= number_format((float)($totalMonths = max(1,count($months))),0) ?> pts</div>
        </div>
      </div>
    </div>

    <div class="col-12 col-md-3">
      <div class="card modern-card">
        <div class="card-body">
          <div class="text-muted">Total Sold (kg) (12 months)</div>
          <div class="h3 fw-bold mb-0"><?= number_format($totalKg12,2) ?></div>
          <div class="small text-muted">Avg/month: <?= number_format($avgKgPerMonth,2) ?> kg</div>
        </div>
      </div>
    </div>

    <div class="col-12 col-md-3">
      <div class="card modern-card">
        <div class="card-body">
          <div class="text-muted">Total Revenue (12 months)</div>
          <div class="h3 fw-bold mb-0">₱<?= number_format($totalRev12,2) ?></div>
          <div class="small text-muted">Avg/kg: ₱<?= number_format($avgPricePerKg,2) ?></div>
        </div>
      </div>
    </div>

    <div class="col-12 col-md-3">
      <div class="card modern-card">
        <div class="card-body">
          <div class="text-muted">Best Month (Revenue)</div>
          <div class="h5 fw-bold mb-0"><?= htmlspecialchars($bestMonthLabel) ?></div>
          <div class="small text-muted">₱<?= number_format($bestMonthRev,2) ?></div>
        </div>
      </div>
    </div>
  </div>

  <!-- NEW: Low/Out quick decision KPIs -->
  <div class="row g-3 mb-3">
    <div class="col-12 col-md-4">
      <div class="card modern-card">
        <div class="card-body">
          <div class="text-muted">Currently OUT of Stock</div>
          <div class="h3 fw-bold mb-0"><?= (int)$outCount ?></div>
          <div class="small text-muted">Products with stock ≤ 0</div>
        </div>
      </div>
    </div>

    <div class="col-12 col-md-4">
      <div class="card modern-card">
        <div class="card-body">
          <div class="text-muted">Low Stock Items</div>
          <div class="h3 fw-bold mb-0"><?= (int)$lowCount ?></div>
          <div class="small text-muted">Stock ≤ <?= number_format($LOW_STOCK_THRESHOLD,0) ?>kg</div>
        </div>
      </div>
    </div>

    <div class="col-12 col-md-4">
      <div class="card modern-card">
        <div class="card-body">
          <div class="text-muted">Peak Day (Last 90 Days)</div>
          <div class="h3 fw-bold mb-0"><?= htmlspecialchars($peakDay) ?></div>
          <div class="small text-muted"><?= number_format((float)$peakKg,2) ?> kg sold</div>
        </div>
      </div>
    </div>
  </div>

  <!-- NEW: Day of week + Restock -->
  <div class="row g-4">
    <div class="col-12 col-xl-7">
      <div class="card modern-card">
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-center mb-2">
            <h5 class="fw-bold mb-0">Sales by Day of Week</h5>
            <span class="text-muted small">Last 90 days</span>
          </div>
          <canvas id="dowChart" height="120"></canvas>
          <div class="mt-2 text-muted small">
            <i class="fa-solid fa-circle-info me-1"></i>
            Helps plan stock preparation (client mentioned Saturday usually has higher demand).
          </div>
        </div>
      </div>
    </div>

    <div class="col-12 col-xl-5">
      <div class="card modern-card">
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-center mb-2">
            <h5 class="fw-bold mb-0">Suggested Restock (Top 5)</h5>
            <span class="badge badge-soft">Decision</span>
          </div>

          <div class="text-muted small mb-2">
            Avg/day from last <?= (int)$RESTOCK_LOOKBACK_DAYS ?> days · Lead time <?= (int)$LEAD_TIME_DAYS ?> day(s) · Safety <?= number_format($SAFETY_STOCK_KG,0) ?>kg · Target <?= (int)$TARGET_DAYS_COVER ?> days cover
          </div>

          <?php if(empty($restockRows)): ?>
            <div class="alert alert-success mb-0">No urgent restocks based on current data.</div>
          <?php else: ?>
            <div class="table-responsive">
              <table class="table table-sm table-striped align-middle mb-0">
                <thead class="table-dark">
                  <tr>
                    <th>Product</th>
                    <th class="text-end">Stock</th>
                    <th class="text-end">Suggested</th>
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
                      <td class="fw-semibold">
                        <?= htmlspecialchars($r['product_label']) ?><br>
                        <small class="text-muted"><?= htmlspecialchars($r['sku']) ?></small>
                      </td>
                      <td class="text-end"><?= number_format((float)$r['current_stock'],2) ?></td>
                      <td class="text-end fw-bold"><?= number_format((float)$r['_suggested'],2) ?></td>
                      <td><span class="badge bg-<?= $badge ?>"><?= htmlspecialchars($r['_status']) ?></span></td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          <?php endif; ?>

          <div class="mt-2 text-muted small">
            <i class="fa-solid fa-lightbulb me-1"></i>
            Suggested order quantity is computed from average daily demand and current stock.
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- CHARTS: 12-month kg + revenue + forecast -->
  <div class="row g-4 mt-1">
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
            Monthly totals from sales_items (kg sold).
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
            <h5 class="fw-bold mb-0">Forecast (Next 3 Months)</h5>
            <span class="badge badge-soft">Time-series</span>
          </div>
          <canvas id="forecastChart" height="140"></canvas>
          <div class="mt-3 text-muted small">
            <i class="fa-solid fa-flask me-1"></i>
            Forecast uses a 3-month moving average with a simple trend adjustment from the last 6 months.
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- PRODUCT MOVEMENT -->
  <div class="row g-4 mt-1">
    <div class="col-12">
      <div class="card modern-card">
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-center mb-2">
            <h5 class="fw-bold mb-0">Product Movement (Last 30 Days)</h5>
            <span class="text-muted small">Fast / Medium / Slow</span>
          </div>

          <div class="alert alert-info mb-3">
            <i class="fa-solid fa-lightbulb me-1"></i>
            <?= htmlspecialchars($insight) ?>
          </div>

          <div class="table-responsive">
            <table class="table table-striped align-middle mb-0">
              <thead class="table-dark">
                <tr>
                  <th>Product</th>
                  <th>SKU</th>
                  <th class="text-end">Kg Sold (30d)</th>
                  <th class="text-end">Revenue (30d)</th>
                  <th>Movement</th>
                  <th>Recommendation</th>
                </tr>
              </thead>
              <tbody>
              <?php if(count($prodRows) > 0): ?>
                <?php foreach($prodRows as $r): ?>
                  <?php
                    $kg = (float)($r['kg_sold_30'] ?? 0);
                    $mv = 'MEDIUM';
                    $badge = 'badge-medium';
                    $rec = 'Monitor stock weekly.';
                    if($kg >= $fastThreshold && $fastThreshold > 0) { $mv = 'FAST'; $badge = 'badge-fast'; $rec='Restock earlier; prepare for peak days.'; }
                    if($kg <= $slowThreshold) { $mv = 'SLOW'; $badge = 'badge-slow'; $rec='Avoid over-ordering; check overstock risk.'; }
                  ?>
                  <tr>
                    <td class="fw-semibold"><?= htmlspecialchars($r['product_name'] ?? '') ?></td>
                    <td><?= htmlspecialchars($r['sku'] ?? '') ?></td>
                    <td class="text-end fw-bold"><?= number_format($kg,2) ?></td>
                    <td class="text-end">₱<?= number_format((float)($r['rev_30'] ?? 0),2) ?></td>
                    <td><span class="badge <?= $badge ?>"><?= $mv ?></span></td>
                    <td class="text-muted"><?= htmlspecialchars($rec) ?></td>
                  </tr>
                <?php endforeach; ?>
              <?php else: ?>
                <tr><td colspan="6" class="text-center text-muted">No product movement data in the last 30 days.</td></tr>
              <?php endif; ?>
              </tbody>
            </table>
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
  window.OWNER_ANALYTICS_DATA = <?= json_encode([
    'months'         => $months,
    'kgData'         => $kgData,
    'revData'        => $revData,
    'forecastLabels' => $forecastLabels,
    'forecastKg'     => $forecastKg,
    'forecastRev'    => $forecastRev,
    'daySalesLabels' => $daySalesLabels,
    'daySalesData'   => $daySalesData,
  ], JSON_UNESCAPED_SLASHES) ?>;
</script>

<script src="../js/owner_analytics.js"></script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
