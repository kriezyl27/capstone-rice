<?php
session_start();
if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit;
}
$role = $_SESSION['role'] ?? '';
$username = $_SESSION['username'] ?? 'User';
$user_id = (int)($_SESSION['user_id'] ?? 0);

include '../config/db.php';

if($role !== 'admin'){
    header("Location: dashboard.php");
    exit;
}

$message = "";

/* =========================
   HELPER: ACTIVITY LOG
========================= */
function logActivity($conn, $user_id, $type, $desc){
    $stmt = $conn->prepare("INSERT INTO activity_logs (user_id, activity_type, description, created_at)
                            VALUES (?, ?, ?, NOW())");
    $stmt->bind_param("iss", $user_id, $type, $desc);
    $stmt->execute();
    $stmt->close();
}

/* =========================
   HANDLE ADD PRODUCT
========================= */
if(isset($_POST['add_product'])){
    $variety = trim($_POST['variety']);
    $grade = trim($_POST['grade']);
    $sku = trim($_POST['sku']);
    $unit_weight_kg = (float)$_POST['unit_weight_kg'];
    $unit_price = (float)$_POST['unit_price'];
    $harvest_date = $_POST['harvest_date'];
    $stock_kg = 0; // NEW products start with 0 stock

    $stmt = $conn->prepare("INSERT INTO products
        (variety, grade, sku, unit_weight_kg, unit_price, harvest_date, stock_kg, created_at, archived)
        VALUES (?,?,?,?,?,?,?,NOW(),0)");
    $stmt->bind_param("sssddsd", $variety, $grade, $sku, $unit_weight_kg, $unit_price, $harvest_date, $stock_kg);
    $stmt->execute();
    $stmt->close();

    logActivity($conn, $user_id, "PRODUCT", "Added product: $variety - $grade (SKU: $sku)");

    header("Location: products.php?success=added");
    exit;
}

/* =========================
   HANDLE EDIT PRODUCT
   NOTE: DO NOT EDIT stock_kg here (stock changes via Add/Adjust/Sale/Return)
========================= */
if(isset($_POST['edit_product'])){
    $product_id = (int)$_POST['product_id'];
    $variety = trim($_POST['variety']);
    $grade = trim($_POST['grade']);
    $sku = trim($_POST['sku']);
    $unit_weight_kg = (float)$_POST['unit_weight_kg'];
    $unit_price = (float)$_POST['unit_price'];
    $harvest_date = $_POST['harvest_date'];

    $stmt = $conn->prepare("UPDATE products
        SET variety=?, grade=?, sku=?, unit_weight_kg=?, unit_price=?, harvest_date=?
        WHERE product_id=?");
    $stmt->bind_param("sssddsi", $variety, $grade, $sku, $unit_weight_kg, $unit_price, $harvest_date, $product_id);
    $stmt->execute();
    $stmt->close();

    logActivity($conn, $user_id, "PRODUCT", "Edited product #$product_id: $variety - $grade (SKU: $sku)");

    header("Location: products.php?success=updated");
    exit;
}

/* =========================
   HANDLE ARCHIVE
========================= */
if(isset($_GET['archive'])){
    $archive_id = (int)$_GET['archive'];

    $stmt = $conn->prepare("UPDATE products SET archived=1 WHERE product_id=?");
    $stmt->bind_param("i", $archive_id);
    $stmt->execute();
    $stmt->close();

    logActivity($conn, $user_id, "PRODUCT", "Archived product #$archive_id");

    header("Location: products.php?success=archived");
    exit;
}

/* =========================
   FETCH PRODUCTS
========================= */
$products = $conn->query("SELECT * FROM products WHERE archived=0 ORDER BY created_at DESC");
if(!$products){
    die("Query Error: " . $conn->error);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Products | DOHIVES</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">

<style>
body { background:#f4f6f9; }

/* Sidebar */
.sidebar {
    min-height:100vh;
    background:#2c3e50;
}
.sidebar .nav-link {
    color:#fff;
    padding:10px 16px;
    border-radius:8px;
    font-size:.95rem;
}
.sidebar .nav-link:hover,
.sidebar .nav-link.active { background:#34495e; }

/* Dropdown submenu */
.sidebar .submenu { padding-left:35px; }
.sidebar .submenu a {
    font-size:.9rem;
    padding:6px 0;
    display:block;
    color:#ecf0f1;
    text-decoration:none;
}
.sidebar .submenu a:hover { color:#fff; }

/* Cards */
.modern-card {
    border-radius:14px;
    box-shadow:0 6px 16px rgba(0,0,0,.12);
    transition:.3s;
}
.modern-card:hover { transform:translateY(-4px); }

/* Navbar spacing */
.main-content { padding-top:85px; }

/* Gradients */
.bg-gradient-primary {background:linear-gradient(135deg,#1d2671,#c33764);}
</style>

</head>
<body>

<!-- TOP NAVBAR -->
<nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm fixed-top">
  <div class="container-fluid">
    <button class="btn btn-outline-dark d-lg-none" data-bs-toggle="collapse" data-bs-target="#sidebarMenu">
      ☰
    </button>
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

<li class="nav-item">
<a class="nav-link" href="dashboard.php">
<i class="fas fa-home me-2"></i>Dashboard
</a>
</li>

<li class="nav-item">
<a class="nav-link active" data-bs-toggle="collapse" href="#inventoryMenu">
<i class="fas fa-warehouse me-2"></i>Inventory
<i class="fas fa-chevron-down float-end"></i>
</a>
<div class="collapse show submenu" id="inventoryMenu">
<a href="products.php" class="fw-bold">Products</a>
<a href="../inventory/add_stock.php">Add Stock</a>
<a href="../inventory/adjust_stock.php">Adjust Stock</a>
<a href="../inventory/inventory.php">Inventory Logs</a>
</div>
</li>


<li class="nav-item">
<a class="nav-link" href="users.php"><i class="fas fa-users me-2"></i>User Management</a>
</li>

<li class="nav-item">
<a class="nav-link" href="sales.php">
<i class="fas fa-cash-register me-2"></i>Sales
</a>
</li>

<li class="nav-item">
<a class="nav-link" href="analytics.php">
<i class="fas fa-chart-line me-2"></i>Analytics & Forecasting
</a>
</li>

<li class="nav-item">
<a class="nav-link" href="system_logs.php">
<i class="fas fa-archive me-2"></i>System Logs
</a>
</li>

</ul>
</div>
</nav>

<!-- Main Content -->
<main class="col-lg-10 ms-sm-auto px-4 main-content">

<div class="d-flex justify-content-between align-items-center">
  <h2 class="mb-0">Products</h2>
  <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addProductModal">
    <i class="fas fa-plus"></i> Add Product
  </button>
</div>

<?php if(isset($_GET['success'])): ?>
  <?php if($_GET['success']==='added'): ?>
    <div class="alert alert-success mt-3">Product added successfully!</div>
  <?php elseif($_GET['success']==='updated'): ?>
    <div class="alert alert-success mt-3">Product updated successfully!</div>
  <?php elseif($_GET['success']==='archived'): ?>
    <div class="alert alert-success mt-3">Product archived successfully!</div>
  <?php endif; ?>
<?php endif; ?>

<div class="table-responsive mt-3">
<table class="table table-striped table-bordered modern-card align-middle">
<thead class="table-dark">
<tr>
<th>ID</th>
<th>Variety</th>
<th>Grade</th>
<th>SKU</th>
<th>Unit Weight (kg)</th>
<th>Current Stock (kg)</th>
<th>Price</th>
<th>Harvest Date</th>
<th>Status</th>
<th>Actions</th>
</tr>
</thead>
<tbody>

<?php while($row = $products->fetch_assoc()): ?>
<?php
  $stock = (float)$row['stock_kg'];
  $lowBadge = ($stock <= 10) ? "<span class='badge bg-danger ms-2'>LOW</span>" : "<span class='badge bg-success ms-2'>OK</span>";
?>
<tr>
<td><?= (int)$row['product_id'] ?></td>
<td><?= htmlspecialchars($row['variety']) ?></td>
<td><?= htmlspecialchars($row['grade']) ?></td>
<td><?= htmlspecialchars($row['sku']) ?></td>
<td><?= number_format((float)$row['unit_weight_kg'],2) ?></td>
<td><?= number_format($stock,2) ?><?= $lowBadge ?></td>
<td><?= number_format((float)$row['unit_price'],2) ?></td>
<td><?= htmlspecialchars($row['harvest_date']) ?></td>
<td>
  <span class="badge bg-primary">Active</span>
</td>
<td class="text-nowrap">
    <button class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#editProductModal<?= (int)$row['product_id'] ?>">
      <i class="fas fa-edit"></i>
    </button>
    <a href="products.php?archive=<?= (int)$row['product_id'] ?>" class="btn btn-sm btn-danger"
       onclick="return confirm('Archive this product?')">
       <i class="fas fa-archive"></i>
    </a>
</td>
</tr>

<!-- Edit Product Modal -->
<div class="modal fade" id="editProductModal<?= (int)$row['product_id'] ?>" tabindex="-1">
<div class="modal-dialog">
<div class="modal-content">
<div class="modal-header">
<h5 class="modal-title">Edit Product</h5>
<button type="button" class="btn-close" data-bs-dismiss="modal"></button>
</div>

<form method="POST" action="">
<div class="modal-body">
<input type="hidden" name="product_id" value="<?= (int)$row['product_id'] ?>">

<div class="mb-2">
  <label>Variety</label>
  <input class="form-control" type="text" name="variety" value="<?= htmlspecialchars($row['variety']) ?>" required>
</div>

<div class="mb-2">
  <label>Grade</label>
  <input class="form-control" type="text" name="grade" value="<?= htmlspecialchars($row['grade']) ?>" required>
</div>

<div class="mb-2">
  <label>SKU</label>
  <input class="form-control" type="text" name="sku" value="<?= htmlspecialchars($row['sku']) ?>" required>
</div>

<div class="mb-2">
  <label>Unit Weight (kg)</label>
  <input class="form-control" type="number" step="0.01" min="0.01" name="unit_weight_kg"
         value="<?= htmlspecialchars($row['unit_weight_kg']) ?>" required>
</div>

<div class="mb-2">
  <label>Price</label>
  <input class="form-control" type="number" step="0.01" min="0" name="unit_price"
         value="<?= htmlspecialchars($row['unit_price']) ?>" required>
</div>

<div class="mb-2">
  <label>Harvest Date</label>
  <input class="form-control" type="date" name="harvest_date"
         value="<?= htmlspecialchars($row['harvest_date']) ?>" required>
</div>

<div class="alert alert-info mt-2 mb-0">
  <b>Note:</b> Stock cannot be edited here. Use <b>Add Stock</b>, <b>Adjust Stock</b>, Sales, or Returns.
</div>

</div>

<div class="modal-footer">
<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
<button type="submit" name="edit_product" class="btn btn-primary">Save Changes</button>
</div>
</form>

</div>
</div>
</div>

<?php endwhile; ?>

<?php if($products->num_rows===0): ?>
<tr><td colspan="10" class="text-center text-muted">No active products found.</td></tr>
<?php endif; ?>

</tbody>
</table>
</div>

<!-- Add Product Modal -->
<div class="modal fade" id="addProductModal" tabindex="-1">
<div class="modal-dialog">
<div class="modal-content">
<div class="modal-header">
<h5 class="modal-title">Add Product</h5>
<button type="button" class="btn-close" data-bs-dismiss="modal"></button>
</div>

<form method="POST" action="">
<div class="modal-body">

<div class="mb-2">
  <label>Variety</label>
  <input class="form-control" type="text" name="variety" required>
</div>

<div class="mb-2">
  <label>Grade</label>
  <input class="form-control" type="text" name="grade" required>
</div>

<div class="mb-2">
  <label>SKU</label>
  <input class="form-control" type="text" name="sku" required>
</div>

<div class="mb-2">
  <label>Unit Weight (kg)</label>
  <input class="form-control" type="number" step="0.01" min="0.01" name="unit_weight_kg" required>
</div>

<div class="mb-2">
  <label>Price</label>
  <input class="form-control" type="number" step="0.01" min="0" name="unit_price" required>
</div>

<div class="mb-2">
  <label>Harvest Date</label>
  <input class="form-control" type="date" name="harvest_date" required>
</div>

<div class="alert alert-info mt-2 mb-0">
  New products start with <b>0 stock</b>. Use <b>Add Stock</b> when inventory arrives.
</div>

</div>

<div class="modal-footer">
<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
<button type="submit" name="add_product" class="btn btn-primary">Add Product</button>
</div>
</form>

</div>
</div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</main>
</body>
</html>
