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

$activePage  = 'products';      // highlights Inventory section
$profileLink = 'profile.php';
$logoutLink  = '../logout.php';

$message = "";

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
   HANDLE ADD PRODUCT (NO UNIT WEIGHT)
========================= */
if(isset($_POST['add_product'])){
    $variety = trim($_POST['variety'] ?? '');
    $grade = trim($_POST['grade'] ?? '');
    $sku = trim($_POST['sku'] ?? '');
    $unit_price = (float)($_POST['unit_price'] ?? 0);
    $delivery_date = $_POST['delivery_date'] ?? null;

    if($variety === '' || $grade === '' || $sku === '' || !$delivery_date){
        header("Location: products.php?error=" . urlencode("Please complete all required fields."));
        exit;
    }

    $stmt = $conn->prepare("INSERT INTO products
        (variety, grade, sku, unit_price, delivery_date, created_at, archived)
        VALUES (?,?,?,?,?,NOW(),0)");
    $stmt->bind_param("sssds", $variety, $grade, $sku, $unit_price, $delivery_date);
    $stmt->execute();
    $stmt->close();

    logActivity($conn, $user_id, "PRODUCT", "Added product: $variety - $grade (SKU: $sku)");

    header("Location: products.php?success=added");
    exit;
}

/* =========================
   HANDLE EDIT PRODUCT (NO UNIT WEIGHT)
========================= */
if(isset($_POST['edit_product'])){
    $product_id = (int)($_POST['product_id'] ?? 0);
    $variety = trim($_POST['variety'] ?? '');
    $grade = trim($_POST['grade'] ?? '');
    $sku = trim($_POST['sku'] ?? '');
    $unit_price = (float)($_POST['unit_price'] ?? 0);
    $delivery_date = $_POST['delivery_date'] ?? null;

    if($product_id <= 0 || $variety === '' || $grade === '' || $sku === '' || !$delivery_date){
        header("Location: products.php?error=" . urlencode("Invalid edit request."));
        exit;
    }

    $stmt = $conn->prepare("UPDATE products
        SET variety=?, grade=?, sku=?, unit_price=?, delivery_date=?
        WHERE product_id=?");
    $stmt->bind_param("sssdsi", $variety, $grade, $sku, $unit_price, $delivery_date, $product_id);
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
    $archive_id = (int)($_GET['archive'] ?? 0);

    $stmt = $conn->prepare("UPDATE products SET archived=1 WHERE product_id=?");
    $stmt->bind_param("i", $archive_id);
    $stmt->execute();
    $stmt->close();

    logActivity($conn, $user_id, "PRODUCT", "Archived product #$archive_id");

    header("Location: products.php?success=archived");
    exit;
}

/* =========================
   FETCH PRODUCTS + COMPUTED STOCK
   stock = SUM(in) - SUM(out) + SUM(adjust)
========================= */
$sql = "
SELECT
  p.*,
  IFNULL(SUM(
    CASE
      WHEN LOWER(it.type)='in' THEN it.qty_kg
      WHEN LOWER(it.type)='out' THEN -it.qty_kg
      WHEN LOWER(it.type)='adjust' THEN it.qty_kg
      ELSE 0
    END
  ), 0) AS stock_kg
FROM products p
LEFT JOIN inventory_transactions it ON it.product_id = p.product_id
WHERE p.archived = 0
GROUP BY p.product_id
ORDER BY p.created_at DESC
";
$products = $conn->query($sql);
if(!$products){
    die("Query Error: " . $conn->error);
}

// Low stock threshold
$LOW_STOCK_THRESHOLD = 10; // kg

/* =========================
   ✅ ADDED: OVERSTOCK THRESHOLD (INTERFACE ONLY)
   No DB changes
========================= */
$OVERSTOCK_THRESHOLD = 1000; // kg (change if needed)

// ✅ ADDED: overstock items list for modal (computed from fetched rows)
$overItems = [];
if($products && $products->num_rows > 0){
    // we'll collect rows first so we can still loop later
    $allRows = [];
    while($r = $products->fetch_assoc()){
        $allRows[] = $r;
        $st = (float)$r['stock_kg'];
        if($st >= $OVERSTOCK_THRESHOLD){
            $overItems[] = [
                'product' => ($r['variety'].' - '.$r['grade']),
                'sku' => $r['sku'],
                'stock' => $st
            ];
        }
    }
    // ✅ restore rows for the table loop
    $products = $allRows;
} else {
    $products = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Products | DO HIYS</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">

<link href="../css/layout.css" rel="stylesheet">
</head>

<body>

<?php include '../includes/topnav.php'; ?>

<div class="container-fluid">
<div class="row">

<?php include '../includes/admin_sidebar.php'; ?>

<!-- Main Content -->
<main class="col-lg-10 ms-sm-auto px-4 main-content">

<div class="d-flex justify-content-between align-items-center">
  <h2 class="mb-0">Products</h2>
  <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addProductModal">
    <i class="fas fa-plus"></i> Add Product
  </button>
</div>

<?php if(isset($_GET['error'])): ?>
  <div class="alert alert-danger mt-3"><?= htmlspecialchars($_GET['error']) ?></div>
<?php endif; ?>

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
<th>Current Stock (kg)</th>
<th>Price</th>
<th>Delivery Date</th>
<th>Status</th>
<th>Actions</th>
</tr>
</thead>
<tbody>

<?php if(count($products) > 0): ?>
<?php foreach($products as $row): ?>
<?php
  $stock = (float)$row['stock_kg'];

  // ✅ ADDED: Status badges
  if($stock <= 0){
    $statusBadge = "<span class='badge bg-secondary ms-2'>OUT</span>";
  } elseif($stock >= $OVERSTOCK_THRESHOLD){
    $statusBadge = "<span class='badge bg-danger ms-2'>OVER</span>";
  } elseif($stock <= $LOW_STOCK_THRESHOLD){
    $statusBadge = "<span class='badge bg-danger ms-2'>LOW</span>";
  } else {
    $statusBadge = "<span class='badge bg-success ms-2'>OK</span>";
  }

  // ✅ ADDED: row highlight
  $rowClass = ($stock >= $OVERSTOCK_THRESHOLD) ? "table-danger" : (($stock <= $LOW_STOCK_THRESHOLD) ? "table-warning" : "");
?>
<tr class="<?= $rowClass ?>">
<td><?= (int)$row['product_id'] ?></td>
<td><?= htmlspecialchars($row['variety']) ?></td>
<td><?= htmlspecialchars($row['grade']) ?></td>
<td><?= htmlspecialchars($row['sku']) ?></td>
<td><?= number_format($stock,2) ?><?= $statusBadge ?></td>
<td><?= number_format((float)$row['unit_price'],2) ?></td>
<td><?= htmlspecialchars($row['delivery_date']) ?></td>
<td><span class="badge bg-primary">Active</span></td>
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
  <label>Price</label>
  <input class="form-control" type="number" step="0.01" min="0" name="unit_price"
         value="<?= htmlspecialchars($row['unit_price']) ?>" required>
</div>

<div class="mb-2">
  <label>Delivery Date</label>
  <input class="form-control" type="date" name="delivery_date"
         value="<?= htmlspecialchars($row['delivery_date']) ?>" required>
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
<?php endforeach; ?>
<?php else: ?>
<tr><td colspan="9" class="text-center text-muted">No active products found.</td></tr>
<?php endif; ?>

</tbody>
</table>
</div>

<!-- ✅ ADDED: OVERSTOCK MODAL (AUTO POPUP) -->
<?php if(count($overItems) > 0): ?>
<div class="modal fade" id="overStockModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">

      <div class="modal-header">
        <h5 class="modal-title fw-bold">Overstock Alert</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body">
        <div class="alert alert-danger mb-3">
          There are <?= count($overItems) ?> product(s) at or above <?= number_format($OVERSTOCK_THRESHOLD,2) ?> kg.
        </div>

        <div class="table-responsive">
          <table class="table table-sm table-striped align-middle mb-0">
            <thead class="table-dark">
              <tr>
                <th>Product</th>
                <th>SKU</th>
                <th class="text-end">Stock (kg)</th>
                <th>Status</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach($overItems as $oi): ?>
                <tr>
                  <td class="fw-semibold"><?= htmlspecialchars($oi['product']) ?></td>
                  <td><?= htmlspecialchars($oi['sku']) ?></td>
                  <td class="text-end fw-bold"><?= number_format((float)$oi['stock'],2) ?></td>
                  <td><span class="badge bg-danger">OVERSTOCK</span></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>

      <div class="modal-footer">
        <button class="btn btn-outline-dark" type="button" data-bs-dismiss="modal">Close</button>
      </div>

    </div>
  </div>
</div>
<?php endif; ?>

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
  <label>Price</label>
  <input class="form-control" type="number" step="0.01" min="0" name="unit_price" required>
</div>

<div class="mb-2">
  <label>Delivery Date</label>
  <input class="form-control" type="date" name="delivery_date" required>
</div>

<div class="alert alert-info mt-2 mb-0">
  New products start with <b>0 stock</b>. Use <b>Stock In (Receiving)</b> when inventory arrives.
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

<script>
document.addEventListener("DOMContentLoaded", function () {
  const overEl = document.getElementById("overStockModal");
  if(overEl){
    new bootstrap.Modal(overEl, { backdrop:'static', keyboard:false }).show();
  }
});
</script>

</main>
</div>
</div>
</body>
</html>
