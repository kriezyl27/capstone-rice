<?php
session_start();
if(!isset($_SESSION['user_id'])){ header("Location: ../login.php"); exit; }
if(strtolower($_SESSION['role'] ?? '') !== 'owner'){ header("Location: ../login.php"); exit; }

$username = $_SESSION['username'] ?? 'Owner';
include '../config/db.php';

$search = trim($_GET['search'] ?? '');
$low = (float)($_GET['low'] ?? 50); // default low threshold (kg)

$searchSql = "";
if($search !== ''){
  $s = $conn->real_escape_string($search);
  $searchSql = " AND (p.variety LIKE '%$s%' OR p.grade LIKE '%$s%' OR p.sku LIKE '%$s%') ";
}

/* =========================
   QUERY: INVENTORY MONITORING
   stock = SUM(IN) - SUM(OUT) + SUM(ADJUST)
========================= */
$res = $conn->query("
  SELECT
    p.product_id, p.variety, p.grade, p.sku, p.unit_price, p.harvest_date,
    COALESCE(SUM(
      CASE 
        WHEN LOWER(it.type)='in' THEN it.qty_kg
        WHEN LOWER(it.type)='out' THEN -it.qty_kg
        WHEN LOWER(it.type)='adjust' THEN it.qty_kg
        ELSE 0
      END
    ),0) AS stock_kg
  FROM products p
  LEFT JOIN inventory_transactions it ON it.product_id = p.product_id
  WHERE p.archived=0
  $searchSql
  GROUP BY p.product_id
  ORDER BY stock_kg ASC, p.variety ASC
");

/* =========================
   PREP: STORE ROWS + LOW STOCK LIST
========================= */
$productsRows = [];
$lowItems = [];

if($res && $res->num_rows > 0){
  while($row = $res->fetch_assoc()){
    $productsRows[] = $row;

    $stock = (float)$row['stock_kg'];
    if($stock <= $low){
      $lowItems[] = [
        'product' => ($row['variety'].' - '.$row['grade']),
        'sku' => $row['sku'],
        'stock' => $stock
      ];
    }
  }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Inventory Monitoring | Owner</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">

<style>
body { background:#f4f6f9; }
.sidebar { min-height:100vh; background:#2c3e50; }
.sidebar .nav-link { color:#fff; padding:10px 16px; border-radius:8px; font-size:.95rem; }
.sidebar .nav-link:hover, .sidebar .nav-link.active { background:#34495e; }
.modern-card { border-radius:14px; box-shadow:0 6px 16px rgba(0,0,0,.12); }
.main-content { padding-top:85px; }
.table td, .table th { padding:0.55rem; vertical-align: middle; }
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
      <li class="nav-item"><a class="nav-link active" href="inventory_monitoring.php"><i class="fas fa-boxes-stacked me-2"></i>Inventory Monitoring</a></li>
      <li class="nav-item"><a class="nav-link" href="sales_report.php"><i class="fas fa-receipt me-2"></i>Sales Reports</a></li>
      <li class="nav-item"><a class="nav-link" href="returns_report.php"><i class="fas fa-rotate-left me-2"></i>Returns Report</a></li>
      <li class="nav-item"><a class="nav-link" href="analytics.php"><i class="fas fa-chart-line me-2"></i>Analytics & Forecasting</a></li>
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
      <h3 class="fw-bold mb-1">Inventory Monitoring</h3>
      <div class="text-muted">View current stock per product (computed from inventory transactions).</div>
    </div>
    <button class="btn btn-outline-dark" onclick="window.print()"><i class="fa-solid fa-print me-1"></i> Print</button>
  </div>

  <!-- LOW STOCK MODAL (AUTO POPUP) -->
  <?php if(count($lowItems) > 0): ?>
  <div class="modal fade" id="lowStockModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
      <div class="modal-content">

        <div class="modal-header">
          <h5 class="modal-title fw-bold">Low Stock Alert</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>

        <div class="modal-body">
          <div class="alert alert-warning mb-3">
            There are <?= count($lowItems) ?> product(s) at or below <?= number_format($low,2) ?> kg.
          </div>

          <div class="table-responsive">
            <table class="table table-sm table-striped align-middle mb-0">
              <thead class="table-dark">
                <tr>
                  <th>Product</th>
                  <th>SKU</th>
                  <th class="text-end">Remaining (kg)</th>
                  <th>Status</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach($lowItems as $li): ?>
                  <tr>
                    <td class="fw-semibold"><?= htmlspecialchars($li['product']) ?></td>
                    <td><?= htmlspecialchars($li['sku']) ?></td>
                    <td class="text-end fw-bold"><?= number_format((float)$li['stock'],2) ?></td>
                    <td>
                      <?php if((float)$li['stock'] <= 0): ?>
                        <span class="badge bg-danger">Out of stock</span>
                      <?php else: ?>
                        <span class="badge bg-warning text-dark">Low</span>
                      <?php endif; ?>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>

          <div class="text-muted small mt-3">
            You can adjust the threshold using the "Low stock threshold" field.
          </div>
        </div>

        <div class="modal-footer">
          <button class="btn btn-outline-dark" type="button" data-bs-dismiss="modal">Close</button>
          <a class="btn btn-dark" href="#stockTable">Go to table</a>
        </div>

      </div>
    </div>
  </div>
  <?php endif; ?>

  <div class="card modern-card mb-3">
    <div class="card-body">
      <form class="row g-2 align-items-end" method="get">
        <div class="col-12 col-md-6">
          <label class="form-label fw-semibold">Search (Variety / Grade / SKU)</label>
          <input class="form-control form-control-lg" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="e.g. Dinorado / B / SKU...">
        </div>
        <div class="col-12 col-md-3">
          <label class="form-label fw-semibold">Low stock threshold (kg)</label>
          <input class="form-control form-control-lg" type="number" step="0.01" name="low" value="<?= htmlspecialchars((string)$low) ?>">
        </div>
        <div class="col-12 col-md-3 d-grid">
          <button class="btn btn-dark btn-lg"><i class="fa-solid fa-magnifying-glass me-1"></i> Apply</button>
        </div>
      </form>
    </div>
  </div>

  <div class="card modern-card" id="stockTable">
    <div class="card-body">
      <div class="table-responsive">
        <table class="table table-striped align-middle mb-0">
          <thead class="table-dark">
            <tr>
              <th>Product</th>
              <th>SKU</th>
              <th class="text-end">Stock (kg)</th>
              <th class="text-end">Unit Price</th>
              <th>Harvest Date</th>
              <th>Status</th>
            </tr>
          </thead>
          <tbody>
          <?php if(count($productsRows) > 0): ?>
            <?php foreach($productsRows as $p):
              $stock = (float)$p['stock_kg'];
              $isLow = $stock <= $low;
            ?>
              <tr class="<?= $isLow ? 'table-warning' : '' ?>">
                <td class="fw-bold"><?= htmlspecialchars($p['variety'].' - '.$p['grade']) ?></td>
                <td><?= htmlspecialchars($p['sku']) ?></td>
                <td class="text-end fw-bold"><?= number_format($stock,2) ?></td>
                <td class="text-end">₱<?= number_format((float)$p['unit_price'],2) ?></td>
                <td><?= $p['harvest_date'] ? htmlspecialchars(date("M d, Y", strtotime($p['harvest_date']))) : '—' ?></td>
                <td>
                  <?php if($stock <= 0): ?>
                    <span class="badge bg-danger">Out of stock</span>
                  <?php elseif($isLow): ?>
                    <span class="badge bg-warning text-dark">Low</span>
                  <?php else: ?>
                    <span class="badge bg-success">OK</span>
                  <?php endif; ?>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php else: ?>
            <tr><td colspan="6" class="text-center text-muted">No products found.</td></tr>
          <?php endif; ?>
          </tbody>
Triggers modal show; also can show only once per session (optional).
      </div>

      <div class="text-muted small mt-2">
        Tip: Low stock threshold helps the owner quickly decide re-ordering.
      </div>
    </div>
  </div>

</div>
</main>

</div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<?php if(count($lowItems) > 0): ?>
<script>
document.addEventListener("DOMContentLoaded", function () {
  const modalEl = document.getElementById("lowStockModal");
  if(modalEl){
    const modal = new bootstrap.Modal(modalEl, { backdrop: 'static', keyboard: false });
    modal.show();
  }
});
</script>
<?php endif; ?>

</body>
</html>
