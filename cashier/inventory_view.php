<?php
session_start();
if(!isset($_SESSION['user_id'])){
header("Location: ../login.php");
exit;
}
if(strtolower($_SESSION['role'] ?? '') !== 'cashier'){
header("Location: ../login.php");
exit;
}

$username = $_SESSION['username'] ?? 'Cashier';
include '../config/db.php';

// Low stock threshold (can be adjusted)
$LOW_STOCK_KG = 50;

// Fetch products with computed stock
$sql = "
SELECT
p.product_id,
p.variety,
p.grade,
p.sku,
p.unit_price,
IFNULL(SUM(
CASE
WHEN LOWER(it.type)='in' THEN it.qty_kg
WHEN LOWER(it.type)='out' THEN -it.qty_kg
WHEN LOWER(it.type)='adjust' THEN it.qty_kg
ELSE 0
END
),0) AS stock_kg
FROM products p
LEFT JOIN inventory_transactions it
ON it.product_id = p.product_id
WHERE p.archived = 0
GROUP BY p.product_id
ORDER BY stock_kg ASC, p.variety ASC
";

$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Inventory View | Cashier</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">

<style>
body{ background:#f4f6f9; padding-top: 60px; }
.sidebar{ min-height:100vh; background:#2c3e50; padding-top: 0px; }
.sidebar .nav-link{ color:#fff; padding:10px 16px; border-radius:8px; font-size:.95rem; }
.sidebar .nav-link:hover,.sidebar .nav-link.active{ background:#34495e; }
.main-content{ padding-top:0px; }
.modern-card{ border-radius:14px; box-shadow:0 6px 16px rgba(0,0,0,.12); }
.table td,.table th{ vertical-align:middle; }
</style>
</head>

<body>

<!-- TOP NAVBAR -->
<nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm fixed-top">
<div class="container-fluid">
<button class="btn btn-outline-dark d-lg-none" data-bs-toggle="collapse" data-bs-target="#sidebarMenu">☰</button>
<span class="navbar-brand fw-bold ms-2">DE ORO HIYS GENERAL MERCHANDISE</span>

<div class="ms-auto dropdown">
<a class="nav-link dropdown-toggle" data-bs-toggle="dropdown">
<?= htmlspecialchars($username) ?> <small class="text-muted">(Cashier)</small>
</a>
<ul class="dropdown-menu dropdown-menu-end">
<li><a class="dropdown-item" href="cashier_profile.php"><i class="fa-solid fa-user me-2"></i>Profile</a></li>
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
<ul class="nav flex-column gap-1 px-2">
<li class="nav-item">
<a class="nav-link" href="dashboard.php"><i class="fas fa-home me-2"></i>Dashboard</a>
</li>
<li class="nav-item">
<a class="nav-link" href="pos.php"><i class="fas fa-cash-register me-2"></i>Sale</a>
</li>
<li class="nav-item">
<a class="nav-link" href="sales_history.php"><i class="fas fa-receipt me-2"></i>Sales History</a>
</li>
<li class="nav-item">
<a class="nav-link" href="payments.php"><i class="fas fa-hand-holding-dollar me-2"></i>Utang Payments</a>
</li>
<li class="nav-item">
<a class="nav-link" href="returns.php"><i class="fas fa-rotate-left me-2"></i>Returns</a>
</li>
<li class="nav-item">
    <a class="nav-link" href="customers.php"><i class="fas fa-users me-2"></i>Customers</a>
</li>
<li class="nav-item">
<a class="nav-link active" href="inventory_view.php">
<i class="fas fa-boxes-stacked me-2"></i>Inventory View
</a>
</li>
</ul>
</div>
</nav>

<!-- MAIN CONTENT -->
<main class="col-lg-10 ms-sm-auto px-4 main-content">
<div class="py-4">

<h3 class="fw-bold mb-3">Inventory View</h3>
<div class="text-muted mb-3">
Read-only stock monitoring for cashier reference.
</div>

<div class="card modern-card">
<div class="card-body">

<div class="table-responsive">
<table class="table table-striped align-middle mb-0">
<thead class="table-dark">
<tr>
<th>Product</th>
<th>SKU</th>
<th class="text-end">Stock (kg)</th>
<th class="text-end">Unit Price</th>
<th>Status</th>
</tr>
</thead>
<tbody>

<?php if($result && $result->num_rows > 0): ?>
<?php while($row = $result->fetch_assoc()):
$stock = (float)$row['stock_kg'];
$isLow = $stock <= $LOW_STOCK_KG;
?>
<tr class="<?= $isLow ? 'table-warning' : '' ?>">
<td class="fw-semibold">
<?= htmlspecialchars($row['variety'].' - '.$row['grade']) ?>
</td>
<td><?= htmlspecialchars($row['sku']) ?></td>
<td class="text-end fw-bold"><?= number_format($stock,2) ?></td>
<td class="text-end">₱<?= number_format((float)$row['unit_price'],2) ?></td>
<td>
<?php if($stock <= 0): ?>
<span class="badge bg-danger">Out of Stock</span>
<?php elseif($isLow): ?>
<span class="badge bg-warning text-dark">Low</span>
<?php else: ?>
<span class="badge bg-success">Available</span>
<?php endif; ?>
</td>
</tr>
<?php endwhile; ?>
<?php else: ?>
<tr><td colspan="5" class="text-center text-muted">No products found.</td></tr>
<?php endif; ?>

</tbody>
</table>
</div>

<div class="text-muted small mt-2">
Low stock threshold: <?= number_format($LOW_STOCK_KG,2) ?> kg
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