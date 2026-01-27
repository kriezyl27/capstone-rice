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

include '../config/db.php';

$username = $_SESSION['username'] ?? 'Admin';

/* =========================
FILTERS (optional)
========================= */
$from = $_GET['from'] ?? date('Y-m-01');
$to = $_GET['to'] ?? date('Y-m-d');
$status = strtolower(trim($_GET['status'] ?? 'all'));

$allowedStatus = ['all','paid','unpaid','cancelled'];
if(!in_array($status, $allowedStatus)) $status = 'all';

// Validate dates
if(!preg_match('/^\d{4}-\d{2}-\d{2}$/', $from)) $from = date('Y-m-01');
if(!preg_match('/^\d{4}-\d{2}-\d{2}$/', $to)) $to = date('Y-m-d');

/* =========================
SUMMARY (CONNECTED TO POS)
POS saves: paid / unpaid
========================= */
$where = "WHERE DATE(s.sale_date) BETWEEN ? AND ? AND (s.status IS NULL OR LOWER(s.status) <> 'cancelled')";
$params = [$from, $to];
$types = "ss";

if($status !== 'all'){
// If status is cancelled, show only cancelled
if($status === 'cancelled'){
$where = "WHERE DATE(s.sale_date) BETWEEN ? AND ? AND LOWER(IFNULL(s.status,'')) = 'cancelled'";
} else {
$where .= " AND LOWER(IFNULL(s.status,'')) = ?";
$params[] = $status;
$types .= "s";
}
}

$stmt = $conn->prepare("
SELECT
COUNT(*) AS total_sales,
IFNULL(SUM(s.total_amount),0) AS total_revenue
FROM sales s
$where
");
$stmt->bind_param($types, ...$params);
$stmt->execute();
$summary = $stmt->get_result()->fetch_assoc();
$stmt->close();

/* =========================
SALES LIST (CONNECTED)
includes cashier + customer
========================= */
$stmt = $conn->prepare("
SELECT
s.sale_id, s.user_id, s.customer_id, s.sale_date, s.total_amount, s.status,
u.username AS cashier_username,
CONCAT(IFNULL(c.first_name,''),' ',IFNULL(c.last_name,'')) AS customer_name
FROM sales s
LEFT JOIN users u ON s.user_id = u.user_id
LEFT JOIN customers c ON s.customer_id = c.customer_id
$where
ORDER BY s.sale_date DESC
");
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();
$salesRows = [];
while($row = $result->fetch_assoc()){
$salesRows[] = $row;
}
$stmt->close();

/* =========================
Helper
========================= */
function h($v){ return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }

function statusBadge($status){
$st = strtolower(trim($status ?? ''));
if($st === 'paid') return 'bg-success';
if($st === 'unpaid') return 'bg-warning text-dark';
if($st === 'cancelled') return 'bg-danger';
if($st === 'completed') return 'bg-success';
return 'bg-secondary';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Sales Management | DOHIVES</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">

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

/* Cards */
.modern-card { border-radius:14px; box-shadow:0 6px 16px rgba(0,0,0,.12); transition:.3s; }
.modern-card:hover { transform:translateY(-4px); }

/* Navbar spacing */
.main-content { padding-top:85px; }

/* Gradients */
.bg-gradient-primary {background:linear-gradient(135deg,#1d2671,#c33764);}
.bg-gradient-success {background:linear-gradient(135deg,#11998e,#38ef7d);}
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
<?= h($username) ?>
</a>
<ul class="dropdown-menu dropdown-menu-end">
<li><a class="dropdown-item" href="profile.php">Profile</a></li>
<li><a class="dropdown-item text-danger" href="../logout.php">Logout</a></li>
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

<li class="nav-item">
<a class="nav-link" href="dashboard.php"><i class="fas fa-home me-2"></i>Dashboard</a>
</li>

<li class="nav-item">
<a class="nav-link" data-bs-toggle="collapse" href="#inventoryMenu">
<i class="fas fa-warehouse me-2"></i>Inventory
<i class="fas fa-chevron-down float-end"></i>
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

<li class="nav-item">
<a class="nav-link active" href="sales.php"><i class="fas fa-cash-register me-2"></i>Sales</a>
</li>

<li class="nav-item">
<a class="nav-link" href="analytics.php"><i class="fas fa-chart-line me-2"></i>Analytics & Forecasting</a>
</li>

<li class="nav-item">
<a class="nav-link" href="system_logs.php"><i class="fas fa-archive me-2"></i>System Logs</a>
</li>

</ul>
</div>
</nav>

<!-- Main Content -->
<main class="col-lg-10 ms-sm-auto px-4 main-content">

<div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
<div>
<h3 class="fw-bold mb-1">Sales Management (Admin)</h3>
<div class="text-muted">Shows all cashier sales. POS writes to sales + sales_items.</div>
</div>
</div>

<!-- FILTER -->
<div class="card modern-card mt-3">
<div class="card-body">
<form class="row g-2 align-items-end">
<div class="col-12 col-md-3">
<label class="form-label">From</label>
<input type="date" name="from" class="form-control" value="<?= h($from) ?>">
</div>
<div class="col-12 col-md-3">
<label class="form-label">To</label>
<input type="date" name="to" class="form-control" value="<?= h($to) ?>">
</div>
<div class="col-12 col-md-3">
<label class="form-label">Status</label>
<select name="status" class="form-select">
<option value="all" <?= $status==='all'?'selected':'' ?>>All</option>
<option value="paid" <?= $status==='paid'?'selected':'' ?>>Paid</option>
<option value="unpaid" <?= $status==='unpaid'?'selected':'' ?>>Unpaid (Utang)</option>
<option value="cancelled"<?= $status==='cancelled'?'selected':'' ?>>Cancelled</option>
</select>
</div>
<div class="col-12 col-md-3">
<button class="btn btn-dark w-100"><i class="fa-solid fa-filter me-1"></i> Apply</button>
</div>
</form>
</div>
</div>

<!-- SUMMARY -->
<div class="row g-4 mt-1">
<div class="col-12 col-md-6 col-lg-3">
<div class="card modern-card bg-gradient-primary text-white p-4">
<div class="d-flex justify-content-between align-items-center">
<div>
<h6 class="fw-light">Total Sales</h6>
<h2 class="fw-bold mb-0"><?= (int)($summary['total_sales'] ?? 0) ?></h2>
</div>
<i class="fas fa-receipt fa-3x opacity-75"></i>
</div>
</div>
</div>

<div class="col-12 col-md-6 col-lg-3">
<div class="card modern-card bg-gradient-success text-white p-4">
<div class="d-flex justify-content-between align-items-center">
<div>
<h6 class="fw-light">Total Revenue (₱)</h6>
<h2 class="fw-bold mb-0"><?= number_format((float)($summary['total_revenue'] ?? 0),2) ?></h2>
</div>
<i class="fas fa-coins fa-3x opacity-75"></i>
</div>
</div>
</div>
</div>

<!-- SALES TABLE -->
<div class="card modern-card mt-4">
<div class="card-body table-responsive">
<table class="table table-striped table-hover align-middle mb-0">
<thead class="table-dark">
<tr>
<th>Sale ID</th>
<th>Cashier</th>
<th>Customer</th>
<th>Date</th>
<th>Total (₱)</th>
<th>Status</th>
<th>Details</th>
</tr>
</thead>
<tbody>
<?php if(count($salesRows) > 0): ?>
<?php foreach($salesRows as $row): ?>
<?php $st = strtolower(trim($row['status'] ?? '')); ?>
<tr>
<td><?= (int)$row['sale_id'] ?></td>
<td><?= h($row['cashier_username'] ?? 'N/A') ?></td>
<td><?= h(trim($row['customer_name'] ?? '') ?: 'Walk-in') ?></td>
<td><?= $row['sale_date'] ? date('Y-m-d H:i', strtotime($row['sale_date'])) : '' ?></td>
<td><?= number_format((float)$row['total_amount'],2) ?></td>
<td>
<span class="badge <?= statusBadge($row['status']) ?>">
<?= h(ucfirst($st ?: 'unknown')) ?>
</span>
</td>
<td>
<button class="btn btn-sm btn-outline-dark"
data-bs-toggle="modal"
data-bs-target="#saleModal<?= (int)$row['sale_id'] ?>">
View
</button>
</td>
</tr>

<!-- DETAILS MODAL -->
<div class="modal fade" id="saleModal<?= (int)$row['sale_id'] ?>" tabindex="-1">
<div class="modal-dialog modal-lg modal-dialog-scrollable">
<div class="modal-content">
<div class="modal-header">
<h5 class="modal-title">Sale #<?= (int)$row['sale_id'] ?> Details</h5>
<button type="button" class="btn-close" data-bs-dismiss="modal"></button>
</div>
<div class="modal-body">
<?php
$saleId = (int)$row['sale_id'];
$itemsStmt = $conn->prepare("
SELECT si.qty_kg, si.unit_price, si.line_total,
p.variety, p.grade, p.sku
FROM sales_items si
LEFT JOIN products p ON si.product_id = p.product_id
WHERE si.sale_id = ?
");
$itemsStmt->bind_param("i", $saleId);
$itemsStmt->execute();
$itemsRes = $itemsStmt->get_result();
?>
<div class="row g-2 mb-3">
<div class="col-md-4"><b>Cashier:</b> <?= h($row['cashier_username'] ?? 'N/A') ?></div>
<div class="col-md-4"><b>Customer:</b> <?= h(trim($row['customer_name'] ?? '') ?: 'Walk-in') ?></div>
<div class="col-md-4"><b>Status:</b> <?= h(ucfirst($st ?: 'unknown')) ?></div>
<div class="col-md-4"><b>Date:</b> <?= $row['sale_date'] ? date('Y-m-d H:i', strtotime($row['sale_date'])) : '' ?></div>
<div class="col-md-4"><b>Total:</b> ₱<?= number_format((float)$row['total_amount'],2) ?></div>
</div>

<div class="table-responsive">
<table class="table table-bordered align-middle">
<thead class="table-dark">
<tr>
<th>Product</th>
<th>SKU</th>
<th>Qty (kg)</th>
<th>Unit Price</th>
<th>Line Total</th>
</tr>
</thead>
<tbody>
<?php if($itemsRes && $itemsRes->num_rows > 0): ?>
<?php while($it = $itemsRes->fetch_assoc()): ?>
<tr>
<td><?= h(($it['variety'] ?? 'N/A') . ' - ' . ($it['grade'] ?? '')) ?></td>
<td><?= h($it['sku'] ?? '') ?></td>
<td><?= number_format((float)$it['qty_kg'],2) ?></td>
<td>₱<?= number_format((float)$it['unit_price'],2) ?></td>
<td>₱<?= number_format((float)$it['line_total'],2) ?></td>
</tr>
<?php endwhile; ?>
<?php else: ?>
<tr><td colspan="5" class="text-center text-muted">No items found for this sale.</td></tr>
<?php endif; ?>
</tbody>
</table>
</div>
<?php $itemsStmt->close(); ?>
</div>
<div class="modal-footer">
<button class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
</div>
</div>
</div>
</div>

<?php endforeach; ?>
<?php else: ?>
<tr><td colspan="7" class="text-center text-muted">No sales found for your filters.</td></tr>
<?php endif; ?>
</tbody>
</table>
</div>
</div>

</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>