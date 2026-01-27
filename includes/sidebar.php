<?php
$role = $_SESSION['role'] ?? '';

$isAdmin   = ($role === 'admin');
$isOwner   = ($role === 'owner');
$isCashier = ($role === 'cashier');
?>

<nav id="sidebar">
    <div class="text-center mb-4 text-white">
        <h4>DOHIVES</h4>
        <small class="text-uppercase"><?= htmlspecialchars($role) ?></small>
    </div>

    <ul class="nav flex-column">

        <!-- Dashboard -->
        <li class="nav-item mb-2">
            <a class="nav-link" href="<?= $isCashier ? 'cashier_dashboard.php' : 'dashboard.php' ?>">
                <i class="fas fa-home"></i> Dashboard
            </a>
        </li>

        <!-- ================= ADMIN ================= -->
        <?php if($isAdmin): ?>
        <li class="nav-item mb-2">
            <a class="nav-link" data-bs-toggle="collapse" href="#inventoryMenu">
                <i class="fas fa-boxes"></i> Inventory
                <i class="fas fa-chevron-down float-end"></i>
            </a>
            <div class="collapse" id="inventoryMenu">
                <ul class="nav flex-column ms-3">
                    <li><a class="nav-link text-white" href="products.php">Products</a></li>
                    <li><a class="nav-link text-white" href="../inventory/add_stock.php">Stock In</a></li>
                    <li><a class="nav-link text-white" href="../inventory/deduct_stock.php">Stock Out</a></li>
                    <li><a class="nav-link text-white" href="../inventory/adjust_stock.php">Adjust Stock</a></li>
                </ul>
            </div>
        </li>

        <li class="nav-item mb-2">
            <a class="nav-link" href="sales.php">
                <i class="fas fa-receipt"></i> Sales
            </a>
        </li>

        <li class="nav-item mb-2">
            <a class="nav-link" href="add_user.php">
                <i class="fas fa-users"></i> Users
            </a>
        </li>
        <?php endif; ?>

        <!-- ================= CASHIER ================= -->
        <?php if($isCashier): ?>
        <li class="nav-item mb-2">
            <a class="nav-link" href="add_sale.php">
                <i class="fas fa-cart-plus"></i> Add Sale
            </a>
        </li>

        <li class="nav-item mb-2">
            <a class="nav-link" href="cashier_sales.php">
                <i class="fas fa-receipt"></i> My Sales
            </a>
        </li>
        <?php endif; ?>

        <!-- ================= OWNER ================= -->
        <?php if($isOwner): ?>
        <li class="nav-item mb-2">
            <a class="nav-link" href="analytics.php">
                <i class="fas fa-chart-line"></i> Analytics
            </a>
        </li>

        <li class="nav-item mb-2">
            <a class="nav-link" href="forecasting.php">
                <i class="fas fa-chart-area"></i> Forecasting
            </a>
        </li>

        <li class="nav-item mb-2">
            <a class="nav-link" href="accounts.php">
                <i class="fas fa-wallet"></i> AP / AR
            </a>
        </li>
        <?php endif; ?>

        <!-- ================= COMMON ================= -->
        <li class="nav-item mt-auto mb-2">
            <a class="nav-link" href="profile.php">
                <i class="fas fa-user"></i> Profile
            </a>
        </li>

        <li class="nav-item mb-2">
            <a class="nav-link text-danger" href="logout.php">
                <i class="fas fa-right-from-bracket"></i> Logout
            </a>
        </li>

    </ul>
</nav>