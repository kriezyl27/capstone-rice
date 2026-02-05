<?php
session_start();
if(!isset($_SESSION['user_id'])){ header("Location: ../login.php"); exit; }
if(strtolower($_SESSION['role'] ?? '') !== 'cashier'){ header("Location: ../login.php"); exit; }

include '../config/db.php';

function h($v){ return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }

$sale_id = (int)($_GET['sale_id'] ?? 0);
if($sale_id <= 0){
  die("Invalid sale.");
}

/* =========================
   LOAD SALE + CUSTOMER
========================= */
$stmt = $conn->prepare("
  SELECT s.sale_id, s.sale_date, s.total_amount, s.status,
         c.customer_id, c.first_name, c.last_name, c.phone, c.address,
         u.username AS cashier_name
  FROM sales s
  LEFT JOIN customers c ON s.customer_id = c.customer_id
  LEFT JOIN users u ON s.user_id = u.user_id
  WHERE s.sale_id = ?
  LIMIT 1
");
$stmt->bind_param("i", $sale_id);
$stmt->execute();
$sale = $stmt->get_result()->fetch_assoc();
$stmt->close();

if(!$sale){
  die("Sale not found.");
}

/* =========================
   LOAD SALE ITEMS
========================= */
$stmt = $conn->prepare("
  SELECT si.qty_kg, si.unit_price, si.line_total,
         p.variety, p.grade, p.sku
  FROM sales_items si
  LEFT JOIN products p ON si.product_id = p.product_id
  WHERE si.sale_id = ?
  ORDER BY si.sales_item_id ASC
");
$stmt->bind_param("i", $sale_id);
$stmt->execute();
$resItems = $stmt->get_result();
$items = [];
while($r = $resItems->fetch_assoc()) $items[] = $r;
$stmt->close();

/* =========================
   IF UTANG → LOAD AR
========================= */
$ar = null;
if(strtolower($sale['status']) === 'unpaid'){
  $stmt = $conn->prepare("
    SELECT total_amount, amount_paid, balance, due_date, status
    FROM account_receivable
    WHERE sales_id = ?
    LIMIT 1
  ");
  $stmt->bind_param("i", $sale_id);
  $stmt->execute();
  $ar = $stmt->get_result()->fetch_assoc();
  $stmt->close();
}

$companyName = "DE ORO HIYS GENERAL MERCHANDISE";
$isUtang = strtolower($sale['status']) === 'unpaid';
$receiptTitle = $isUtang ? "UTANG RECEIPT / SALES INVOICE" : "OFFICIAL RECEIPT (CASH)";
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Receipt #<?= (int)$sale_id ?></title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

<style>
body{ background:#f4f6f9; }
.paper{
  max-width:420px;
  margin:20px auto;
  background:#fff;
  padding:18px;
  border-radius:10px;
  box-shadow:0 6px 16px rgba(0,0,0,.12);
}
.mono{
  font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas,
               "Liberation Mono", "Courier New", monospace;
}
@media print{
  body{ background:#fff; }
  .no-print{ display:none !important; }
  .paper{
    box-shadow:none;
    border-radius:0;
    margin:0;
    max-width:100%;
  }
}
</style>
</head>
<body>

<div class="paper mono">
  <div class="text-center">
    <div class="fw-bold"><?= h($companyName) ?></div>
    <div class="small text-muted"><?= h($receiptTitle) ?></div>
    <hr>
  </div>

  <div class="small">
    <div><b>Sale #:</b> <?= (int)$sale['sale_id'] ?></div>
    <div><b>Date:</b> <?= h(date("M d, Y h:i A", strtotime($sale['sale_date']))) ?></div>
    <div><b>Cashier:</b> <?= h($sale['cashier_name'] ?: 'Cashier') ?></div>
    <div><b>Customer:</b> <?= h(trim(($sale['first_name'] ?? '').' '.($sale['last_name'] ?? ''))) ?: '—' ?></div>
    <?php if(!empty($sale['phone'])): ?>
      <div><b>Phone:</b> <?= h($sale['phone']) ?></div>
    <?php endif; ?>
  </div>

  <hr>

  <table class="table table-sm mb-2">
    <thead>
      <tr>
        <th>Item</th>
        <th class="text-end">Qty</th>
        <th class="text-end">Price</th>
        <th class="text-end">Total</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach($items as $it): ?>
        <tr>
          <td>
            <div class="fw-semibold"><?= h($it['variety'].' - '.$it['grade']) ?></div>
            <?php if(!empty($it['sku'])): ?>
              <div class="small text-muted"><?= h($it['sku']) ?></div>
            <?php endif; ?>
          </td>
          <td class="text-end"><?= number_format((float)$it['qty_kg'],2) ?></td>
          <td class="text-end">₱<?= number_format((float)$it['unit_price'],2) ?></td>
          <td class="text-end">₱<?= number_format((float)$it['line_total'],2) ?></td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>

  <div class="d-flex justify-content-between fw-bold">
    <span>GRAND TOTAL</span>
    <span>₱<?= number_format((float)$sale['total_amount'],2) ?></span>
  </div>

  <hr>

  <?php if($isUtang): ?>
    <div class="alert alert-warning py-2 small">
      <div class="fw-bold">STATUS: UNPAID (UTANG)</div>
      <div>Balance: <b>₱<?= number_format((float)($ar['balance'] ?? $sale['total_amount']),2) ?></b></div>
      <div>Due Date:
        <b><?= !empty($ar['due_date']) ? h(date("M d, Y", strtotime($ar['due_date']))) : '—' ?></b>
      </div>
      <div class="text-muted mt-1">
        This receipt acknowledges items received with unpaid balance.
      </div>
    </div>
  <?php else: ?>
    <div class="alert alert-success py-2 small">
      <div class="fw-bold">STATUS: PAID (CASH)</div>
      <div class="text-muted">Thank you for your purchase!</div>
    </div>
  <?php endif; ?>

  <div class="text-center small text-muted mt-2">
    --- END OF RECEIPT ---
  </div>

  <div class="no-print d-grid gap-2 mt-3">
    <button class="btn btn-dark" onclick="window.print()">Print Receipt</button>
    <a href="pos.php" class="btn btn-outline-secondary">Back to Sale</a>
  </div>
</div>

</body>
</html>
