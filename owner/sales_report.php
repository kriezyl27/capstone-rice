<?php
session_start();
if(!isset($_SESSION['user_id'])){ header("Location: ../login.php"); exit; }
if(strtolower($_SESSION['role'] ?? '') !== 'owner'){ header("Location: ../login.php"); exit; }

$username = $_SESSION['username'] ?? 'Owner';
include '../config/db.php';

$from = $_GET['from'] ?? date('Y-m-01');
$to   = $_GET['to'] ?? date('Y-m-d');

$fromEsc = $conn->real_escape_string($from);
$toEsc   = $conn->real_escape_string($to);

$summary = $conn->query("
  SELECT 
    COALESCE(SUM(si.qty_kg),0) AS total_kg,
    COALESCE(SUM(si.line_total),0) AS total_rev,
    COUNT(DISTINCT s.sale_id) AS sales_count
  FROM sales s
  JOIN sales_items si ON si.sale_id=s.sale_id
  WHERE DATE(s.sale_date) BETWEEN '$fromEsc' AND '$toEsc'
    AND (s.status IS NULL OR LOWER(s.status) <> 'cancelled')
")->fetch_assoc();

$res = $conn->query("
  SELECT s.sale_id, s.sale_date, s.status,
         CONCAT(c.first_name,' ',c.last_name) AS customer,
         COALESCE(SUM(si.qty_kg),0) AS kg,
         COALESCE(SUM(si.line_total),0) AS amount
  FROM sales s
  LEFT JOIN customers c ON c.customer_id=s.customer_id
  LEFT JOIN sales_items si ON si.sale_id=s.sale_id
  WHERE DATE(s.sale_date) BETWEEN '$fromEsc' AND '$toEsc'
    AND (s.status IS NULL OR LOWER(s.status) <> 'cancelled')
  GROUP BY s.sale_id
  ORDER BY s.sale_date DESC
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Sales Reports | Owner</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
<style>
body { background:#f4f6f9; }
.sidebar { min-height:100vh; background:#2c3e50; }
.sidebar .nav-link { color:#fff; padding:10px 16px; border-radius:8px; font-size:.95rem; }
.sidebar .nav-link:hover, .sidebar .nav-link.active { background:#34495e; }
.modern-card { border-radius:14px; box-shadow:0 6px 16px rgba(0,0,0,.12); }
.main-content { padding-top:85px; }
</style>
</head>
<body>

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

<nav id="sidebarMenu" class="col-lg-2 d-lg-block sidebar collapse">
  <div class="pt-4">
    <ul class="nav flex-column gap-1">
      <li class="nav-item"><a class="nav-link" href="dashboard.php"><i class="fas fa-gauge-high me-2"></i>Owner Dashboard</a></li>
      <li class="nav-item"><a class="nav-link" href="inventory_monitoring.php"><i class="fas fa-boxes-stacked me-2"></i>Inventory Monitoring</a></li>
      <li class="nav-item"><a class="nav-link active" href="sales_report.php"><i class="fas fa-receipt me-2"></i>Sales Reports</a></li>
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

<main class="col-lg-10 ms-sm-auto px-4 main-content">
<div class="py-4">

  <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
    <div>
      <h3 class="fw-bold mb-1">Sales Reports</h3>
      <div class="text-muted">Filter by date range and view totals.</div>
    </div>
    <button class="btn btn-outline-dark" onclick="window.print()"><i class="fa-solid fa-print me-1"></i> Print</button>
  </div>

  <div class="card modern-card mb-3">
    <div class="card-body">
      <form class="row g-2 align-items-end" method="get">
        <div class="col-12 col-md-4">
          <label class="form-label fw-semibold">From</label>
          <input type="date" class="form-control form-control-lg" name="from" value="<?= htmlspecialchars($from) ?>">
        </div>
        <div class="col-12 col-md-4">
          <label class="form-label fw-semibold">To</label>
          <input type="date" class="form-control form-control-lg" name="to" value="<?= htmlspecialchars($to) ?>">
        </div>
        <div class="col-12 col-md-4 d-grid">
          <button class="btn btn-dark btn-lg"><i class="fa-solid fa-filter me-1"></i> Apply Filter</button>
        </div>
      </form>
    </div>
  </div>

  <div class="row g-3 mb-3">
    <div class="col-12 col-md-4">
      <div class="card modern-card">
        <div class="card-body">
          <div class="text-muted">Total Sales (transactions)</div>
          <div class="h3 fw-bold mb-0"><?= (int)($summary['sales_count'] ?? 0) ?></div>
        </div>
      </div>
    </div>
    <div class="col-12 col-md-4">
      <div class="card modern-card">
        <div class="card-body">
          <div class="text-muted">Total Sold (kg)</div>
          <div class="h3 fw-bold mb-0"><?= number_format((float)($summary['total_kg'] ?? 0),2) ?></div>
        </div>
      </div>
    </div>
    <div class="col-12 col-md-4">
      <div class="card modern-card">
        <div class="card-body">
          <div class="text-muted">Total Revenue</div>
          <div class="h3 fw-bold mb-0">₱<?= number_format((float)($summary['total_rev'] ?? 0),2) ?></div>
        </div>
      </div>
    </div>
  </div>

  <div class="card modern-card">
    <div class="card-body">
      <div class="table-responsive">
        <table class="table table-striped align-middle mb-0">
          <thead class="table-dark">
            <tr>
              <th>Date</th>
              <th>Sale ID</th>
              <th>Customer</th>
              <th class="text-end">Total kg</th>
              <th class="text-end">Amount</th>
              <th>Status</th>
            </tr>
          </thead>
          <tbody>
          <?php if($res && $res->num_rows>0): ?>
            <?php while($r=$res->fetch_assoc()): ?>
              <tr>
                <td><?= htmlspecialchars(date("M d, Y", strtotime($r['sale_date']))) ?></td>
                <td class="fw-semibold">#<?= (int)$r['sale_id'] ?></td>
                <td><?= htmlspecialchars($r['customer'] ?: 'Walk-in') ?></td>
                <td class="text-end fw-bold"><?= number_format((float)$r['kg'],2) ?></td>
                <td class="text-end">₱<?= number_format((float)$r['amount'],2) ?></td>
                <td>
                  <?php
                    $st = strtolower(trim($r['status'] ?? 'completed'));
                    if($st === 'cancelled') echo '<span class="badge bg-danger">Cancelled</span>';
                    else echo '<span class="badge bg-success">Completed</span>';
                  ?>
                </td>
              </tr>
            <?php endwhile; ?>
          <?php else: ?>
            <tr><td colspan="6" class="text-center text-muted">No sales for selected range.</td></tr>
          <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

</div>
</main>

</div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
