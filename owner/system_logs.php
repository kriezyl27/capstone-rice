<?php
session_start();
if(!isset($_SESSION['user_id'])){ header("Location: ../login.php"); exit; }
if(strtolower($_SESSION['role'] ?? '') !== 'owner'){ header("Location: ../login.php"); exit; }

$username = $_SESSION['username'] ?? 'Owner';
include '../config/db.php';

$days = (int)($_GET['days'] ?? 30);
if(!in_array($days, [7,30,90])) $days = 30;

// Activity Logs
$activity = $conn->query("
  SELECT al.activity_id, al.created_at, al.activity_type, al.description,
         CONCAT(u.first_name,' ',u.last_name) AS user_name, u.role
  FROM activity_logs al
  LEFT JOIN users u ON u.user_id = al.user_id
  WHERE al.created_at >= DATE_SUB(NOW(), INTERVAL $days DAY)
  ORDER BY al.created_at DESC
  LIMIT 200
");

// Login Logs
$login = $conn->query("
  SELECT ll.log_id, ll.login_time, ll.device_info, ll.ip_address,
         CONCAT(u.first_name,' ',u.last_name) AS user_name, u.role
  FROM login_logs ll
  LEFT JOIN users u ON u.user_id = ll.user_id
  WHERE ll.login_time >= DATE_SUB(NOW(), INTERVAL $days DAY)
  ORDER BY ll.login_time DESC
  LIMIT 200
");

// Payment Logs (payments + payment_request + push_notif_logs)
$payments = $conn->query("
  SELECT p.payment_id, p.sale_id, p.amount, p.method, p.status, p.paid_at, p.external_ref
  FROM payments p
  WHERE p.paid_at >= DATE_SUB(NOW(), INTERVAL $days DAY)
  ORDER BY p.paid_at DESC
  LIMIT 200
");

$payreq = $conn->query("
  SELECT pr.pay_req_id, pr.sale_id, pr.phone, pr.requested_at, pr.status
  FROM payment_request pr
  WHERE pr.requested_at >= DATE_SUB(NOW(), INTERVAL $days DAY)
  ORDER BY pr.requested_at DESC
  LIMIT 200
");

$push = $conn->query("
  SELECT pn.push_notif_id, pn.payment_id, pn.customer_id, pn.message, pn.sent_at, pn.status
  FROM push_notif_logs pn
  WHERE pn.sent_at >= DATE_SUB(NOW(), INTERVAL $days DAY)
  ORDER BY pn.sent_at DESC
  LIMIT 200
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>System Logs | Owner</title>

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
      <li class="nav-item"><a class="nav-link" href="inventory_monitoring.php"><i class="fas fa-boxes-stacked me-2"></i>Inventory Monitoring</a></li>
      <li class="nav-item"><a class="nav-link" href="sales_report.php"><i class="fas fa-receipt me-2"></i>Sales Reports</a></li>
      <li class="nav-item"><a class="nav-link" href="returns_report.php"><i class="fas fa-rotate-left me-2"></i>Returns Report</a></li>
      <li class="nav-item"><a class="nav-link" href="analytics.php"><i class="fas fa-chart-line me-2"></i>Analytics & Forecasting</a></li>
      <li class="nav-item"><a class="nav-link active" href="system_logs.php"><i class="fas fa-file-shield me-2"></i>System Logs</a></li>
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
      <h3 class="fw-bold mb-1">System Logs</h3>
      <div class="text-muted">Track activity and access history (read-only).</div>
    </div>

    <div class="d-flex gap-2 align-items-center">
      <form method="get" class="d-flex gap-2 align-items-center">
        <select class="form-select" name="days" onchange="this.form.submit()">
          <option value="7"  <?= $days===7?'selected':'' ?>>Last 7 days</option>
          <option value="30" <?= $days===30?'selected':'' ?>>Last 30 days</option>
          <option value="90" <?= $days===90?'selected':'' ?>>Last 90 days</option>
        </select>
      </form>
      <button class="btn btn-outline-dark" onclick="window.print()"><i class="fa-solid fa-print me-1"></i> Print</button>
    </div>
  </div>

  <div class="card modern-card">
    <div class="card-body">
      <ul class="nav nav-pills mb-3" id="logsTab" role="tablist">
        <li class="nav-item" role="presentation">
          <button class="nav-link active" data-bs-toggle="pill" data-bs-target="#tab-activity" type="button">
            <i class="fa-solid fa-clipboard-list me-1"></i> Activity Logs
          </button>
        </li>
        <li class="nav-item" role="presentation">
          <button class="nav-link" data-bs-toggle="pill" data-bs-target="#tab-login" type="button">
            <i class="fa-solid fa-right-to-bracket me-1"></i> Login Logs
          </button>
        </li>
        <li class="nav-item" role="presentation">
          <button class="nav-link" data-bs-toggle="pill" data-bs-target="#tab-pay" type="button">
            <i class="fa-solid fa-bell me-1"></i> Payment / Notification Logs
          </button>
        </li>
      </ul>

      <div class="tab-content">

        <!-- Activity Logs -->
        <div class="tab-pane fade show active" id="tab-activity">
          <div class="table-responsive">
            <table class="table table-striped align-middle mb-0">
              <thead class="table-dark">
                <tr>
                  <th>Date</th>
                  <th>User</th>
                  <th>Role</th>
                  <th>Type</th>
                  <th>Description</th>
                </tr>
              </thead>
              <tbody>
              <?php if($activity && $activity->num_rows>0): ?>
                <?php while($r=$activity->fetch_assoc()): ?>
                  <tr>
                    <td><?= htmlspecialchars(date("M d, Y h:i A", strtotime($r['created_at']))) ?></td>
                    <td><?= htmlspecialchars($r['user_name'] ?: '—') ?></td>
                    <td><?= htmlspecialchars($r['role'] ?: '—') ?></td>
                    <td class="fw-semibold"><?= htmlspecialchars($r['activity_type'] ?: '—') ?></td>
                    <td><?= htmlspecialchars($r['description'] ?: '') ?></td>
                  </tr>
                <?php endwhile; ?>
              <?php else: ?>
                <tr><td colspan="5" class="text-center text-muted">No activity logs found.</td></tr>
              <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Login Logs -->
        <div class="tab-pane fade" id="tab-login">
          <div class="table-responsive">
            <table class="table table-striped align-middle mb-0">
              <thead class="table-dark">
                <tr>
                  <th>Login Time</th>
                  <th>User</th>
                  <th>Role</th>
                  <th>Device</th>
                  <th>IP Address</th>
                </tr>
              </thead>
              <tbody>
              <?php if($login && $login->num_rows>0): ?>
                <?php while($r=$login->fetch_assoc()): ?>
                  <tr>
                    <td><?= htmlspecialchars(date("M d, Y h:i A", strtotime($r['login_time']))) ?></td>
                    <td><?= htmlspecialchars($r['user_name'] ?: '—') ?></td>
                    <td><?= htmlspecialchars($r['role'] ?: '—') ?></td>
                    <td><?= htmlspecialchars($r['device_info'] ?: '—') ?></td>
                    <td><?= htmlspecialchars($r['ip_address'] ?: '—') ?></td>
                  </tr>
                <?php endwhile; ?>
              <?php else: ?>
                <tr><td colspan="5" class="text-center text-muted">No login logs found.</td></tr>
              <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Payment / Notification Logs -->
        <div class="tab-pane fade" id="tab-pay">
          <div class="row g-3">

            <div class="col-12">
              <h6 class="fw-bold mb-2"><i class="fa-solid fa-credit-card me-1"></i> Payments</h6>
              <div class="table-responsive">
                <table class="table table-striped align-middle mb-0">
                  <thead class="table-dark">
                    <tr>
                      <th>Paid At</th>
                      <th>Payment ID</th>
                      <th>Sale ID</th>
                      <th class="text-end">Amount</th>
                      <th>Method</th>
                      <th>Status</th>
                      <th>External Ref</th>
                    </tr>
                  </thead>
                  <tbody>
                  <?php if($payments && $payments->num_rows>0): ?>
                    <?php while($r=$payments->fetch_assoc()): ?>
                      <?php
                        $st = strtolower(trim($r['status'] ?? ''));
                        $badge = 'bg-secondary';
                        if($st==='paid' || $st==='success') $badge='bg-success';
                        elseif($st==='pending') $badge='bg-warning text-dark';
                        elseif($st==='failed') $badge='bg-danger';
                      ?>
                      <tr>
                        <td><?= $r['paid_at'] ? htmlspecialchars(date("M d, Y h:i A", strtotime($r['paid_at']))) : '—' ?></td>
                        <td class="fw-semibold">#<?= (int)$r['payment_id'] ?></td>
                        <td>#<?= (int)$r['sale_id'] ?></td>
                        <td class="text-end fw-bold">₱<?= number_format((float)$r['amount'],2) ?></td>
                        <td><?= htmlspecialchars($r['method'] ?: '—') ?></td>
                        <td><span class="badge <?= $badge ?>"><?= htmlspecialchars(strtoupper($st ?: 'N/A')) ?></span></td>
                        <td><?= htmlspecialchars($r['external_ref'] ?: '—') ?></td>
                      </tr>
                    <?php endwhile; ?>
                  <?php else: ?>
                    <tr><td colspan="7" class="text-center text-muted">No payment logs found.</td></tr>
                  <?php endif; ?>
                  </tbody>
                </table>
              </div>
            </div>

            <div class="col-12 col-xl-6">
              <h6 class="fw-bold mb-2 mt-3"><i class="fa-solid fa-paper-plane me-1"></i> Payment Requests</h6>
              <div class="table-responsive">
                <table class="table table-striped align-middle mb-0">
                  <thead class="table-dark">
                    <tr>
                      <th>Requested At</th>
                      <th>Request ID</th>
                      <th>Sale ID</th>
                      <th>Phone</th>
                      <th>Status</th>
                    </tr>
                  </thead>
                  <tbody>
                  <?php if($payreq && $payreq->num_rows>0): ?>
                    <?php while($r=$payreq->fetch_assoc()): ?>
                      <?php
                        $st = strtolower(trim($r['status'] ?? ''));
                        $badge = 'bg-secondary';
                        if($st==='sent') $badge='bg-info';
                        elseif($st==='pending') $badge='bg-warning text-dark';
                        elseif($st==='done' || $st==='completed') $badge='bg-success';
                        elseif($st==='failed') $badge='bg-danger';
                      ?>
                      <tr>
                        <td><?= htmlspecialchars(date("M d, Y h:i A", strtotime($r['requested_at']))) ?></td>
                        <td class="fw-semibold">#<?= (int)$r['pay_req_id'] ?></td>
                        <td>#<?= (int)$r['sale_id'] ?></td>
                        <td><?= htmlspecialchars($r['phone'] ?: '—') ?></td>
                        <td><span class="badge <?= $badge ?>"><?= htmlspecialchars(strtoupper($st ?: 'N/A')) ?></span></td>
                      </tr>
                    <?php endwhile; ?>
                  <?php else: ?>
                    <tr><td colspan="5" class="text-center text-muted">No payment request logs found.</td></tr>
                  <?php endif; ?>
                  </tbody>
                </table>
              </div>
            </div>

            <div class="col-12 col-xl-6">
              <h6 class="fw-bold mb-2 mt-3"><i class="fa-solid fa-bell me-1"></i> Push Notifications</h6>
              <div class="table-responsive">
                <table class="table table-striped align-middle mb-0">
                  <thead class="table-dark">
                    <tr>
                      <th>Sent At</th>
                      <th>Notif ID</th>
                      <th>Payment ID</th>
                      <th>Customer ID</th>
                      <th>Status</th>
                      <th>Message</th>
                    </tr>
                  </thead>
                  <tbody>
                  <?php if($push && $push->num_rows>0): ?>
                    <?php while($r=$push->fetch_assoc()): ?>
                      <?php
                        $st = strtolower(trim($r['status'] ?? ''));
                        $badge = 'bg-secondary';
                        if($st==='sent' || $st==='success') $badge='bg-success';
                        elseif($st==='pending') $badge='bg-warning text-dark';
                        elseif($st==='failed') $badge='bg-danger';
                      ?>
                      <tr>
                        <td><?= htmlspecialchars(date("M d, Y h:i A", strtotime($r['sent_at']))) ?></td>
                        <td class="fw-semibold">#<?= (int)$r['push_notif_id'] ?></td>
                        <td><?= $r['payment_id'] !== null ? '#'.(int)$r['payment_id'] : '—' ?></td>
                        <td><?= $r['customer_id'] !== null ? (int)$r['customer_id'] : '—' ?></td>
                        <td><span class="badge <?= $badge ?>"><?= htmlspecialchars(strtoupper($st ?: 'N/A')) ?></span></td>
                        <td><?= htmlspecialchars($r['message'] ?: '') ?></td>
                      </tr>
                    <?php endwhile; ?>
                  <?php else: ?>
                    <tr><td colspan="6" class="text-center text-muted">No push notification logs found.</td></tr>
                  <?php endif; ?>
                  </tbody>
                </table>
              </div>
            </div>

          </div>
        </div>

      </div><!-- tab-content -->
    </div>
  </div>

</div>
</main>

</div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
