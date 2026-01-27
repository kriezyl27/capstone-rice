<?php
session_start();
if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit;
}

$username = $_SESSION['username'] ?? 'User';
include '../config/db.php';

$error = "";
$success = "";

// Handle form submission
if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $product_id   = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
    $qty_kg       = isset($_POST['qty_kg']) ? (float)$_POST['qty_kg'] : 0;
    $reference_id = isset($_POST['reference_id']) ? (int)$_POST['reference_id'] : 0; // purchase_id (or 0 if none)
    $note         = trim($_POST['note'] ?? '');

    if($product_id <= 0 || $qty_kg <= 0){
        $error = "Please select a product and enter a valid quantity.";
    } else {

        // Use transaction so stock update + log will always match
        $conn->begin_transaction();

        try {
            // 1) Update stock in products table (Receiving -> Store)
            $stmt = $conn->prepare("UPDATE products SET stock_kg = stock_kg + ? WHERE product_id = ?");
            $stmt->bind_param("di", $qty_kg, $product_id);
            $stmt->execute();
            $stmt->close();

            // 2) Log inventory transaction (Stock movement log)
            $reference_type = "PURCHASE";
            $type = "IN";

            if($note === "") $note = "Stock received (Add Stock)";

            $stmt2 = $conn->prepare("INSERT INTO inventory_transactions
                (product_id, qty_kg, reference_id, reference_type, type, note, created_at)
                VALUES (?, ?, ?, ?, ?, ?, NOW())");
            $stmt2->bind_param("idisss", $product_id, $qty_kg, $reference_id, $reference_type, $type, $note);
            $stmt2->execute();
            $stmt2->close();

            $conn->commit();
            header("Location: inventory.php?success=stock_added");
            exit;

        } catch (Exception $e) {
            $conn->rollback();
            $error = "Failed to add stock. Please try again.";
        }
    }
}

// Fetch products
$products = $conn->query("SELECT product_id, variety, grade FROM products WHERE archived=0 ORDER BY variety ASC, grade ASC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Add Stock | DOHIVES</title>

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
        <li><a class="dropdown-item text-danger" href="logout.php">logout.php">Logout</a></li>
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
<a class="nav-link" href="../admin/dashboard.php">
<i class="fas fa-home me-2"></i>Dashboard
</a>
</li>

<li class="nav-item">
<a class="nav-link active" data-bs-toggle="collapse" href="#inventoryMenu">
<i class="fas fa-warehouse me-2"></i>Inventory
<i class="fas fa-chevron-down float-end"></i>
</a>
<div class="collapse show submenu" id="inventoryMenu">
<a href="../admin/products.php">Products</a>
<a href="../inventory/add_stock.php" class="fw-bold">Add Stock</a>
<a href="../inventory/adjust_stock.php">Adjust Stock</a>
<a href="../inventory/inventory.php">Inventory Logs</a>
</div>
</li>

<li class="nav-item">
<a class="nav-link" href="../admin/add_user.php"><i class="fas fa-users me-2"></i>User Management</a>
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
<a class="nav-link" href="archive_logs.php">
<i class="fas fa-archive me-2"></i>System Logs
</a>
</li>

</ul>
</div>
</nav>

<!-- Main Content -->
<main class="col-lg-10 ms-sm-auto px-4 main-content">

<h2 class="mb-3">Add Stock (Receiving)</h2>

<?php if($error): ?>
  <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<?php if(isset($_GET['success']) && $_GET['success'] === 'stock_added'): ?>
  <div class="alert alert-success">Stock added successfully!</div>
<?php endif; ?>

<div class="card modern-card mt-3">
  <div class="card-body">
    <form method="POST">
        <div class="mb-3">
            <label class="form-label">Product</label>
            <select name="product_id" class="form-select" required>
                <option value="">Select product</option>
                <?php while($row = $products->fetch_assoc()): ?>
                    <option value="<?= (int)$row['product_id'] ?>">
                      <?= htmlspecialchars($row['variety'] . " - " . $row['grade']) ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>

        <div class="mb-3">
            <label class="form-label">Quantity (kg)</label>
            <input type="number" step="0.01" min="0.01" name="qty_kg" class="form-control" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Reference ID (Purchase ID)</label>
            <input type="number" name="reference_id" class="form-control" placeholder="Optional: purchases_id (e.g. 12)">
            <div class="form-text">If this stock came from a purchase order, enter the <b>purchases_id</b>. Otherwise leave blank.</div>
        </div>

        <div class="mb-3">
            <label class="form-label">Note</label>
            <textarea name="note" class="form-control" rows="3" placeholder="Example: Delivered by supplier / received on date..."></textarea>
        </div>

        <button type="submit" class="btn btn-primary">
          <i class="fas fa-plus"></i> Add Stock
        </button>
    </form>
  </div>
</div>

</main>

</div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
