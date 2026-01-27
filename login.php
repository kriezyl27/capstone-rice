<?php
session_start();
// If already logged in, redirect by role
if(isset($_SESSION['user_id'])){
    $role = strtolower($_SESSION['role'] ?? '');
    if($role === 'admin') { header("Location: admin/dashboard.php"); exit; }
    if($role === 'cashier') { header("Location: cashier/dashboard.php"); exit; }
    if($role === 'owner') { header("Location: owner/dashboard.php"); exit; }
}
$error = $_GET['error'] ?? '';
$success = $_GET['success'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Login | Rice Inventory Control System</title>

<!-- Bootstrap -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">

<style>
body {
    min-height: 100vh;
    margin: 0;
    font-family: 'Segoe UI', sans-serif;
    background: #ffffff;
}

/* Left login panel */
.login-panel {
    padding: 60px;
    background: rgba(255,255,255,0.78);
    backdrop-filter: blur(10px);
    border-radius: 18px;
    box-shadow: 0 10px 25px rgba(0,0,0,.10);
}

/* Input style */
.form-control {
    border-radius: 30px;
    padding: 12px 20px;
}

/* Login button */
.btn-login {
    border-radius: 30px;
    padding: 12px;
    font-weight: 600;
}

/* Right image panel */
.hero-image {
    position: relative;
    min-height: 100vh;
    background: url("assets/ricewarehouse.jpg") center / cover no-repeat;
}

/* LEFT fade overlay */
.hero-image::before {
    content: "";
    position: absolute;
    inset: 0;
    background: linear-gradient(
        to right,
        rgba(255, 255, 255, 0.90) 0%,
        rgba(255, 255, 255, 0.75) 28%,
        rgba(255, 255, 255, 0.40) 52%,
        rgba(255, 255, 255, 0.10) 65%,
        rgba(255, 255, 255, 0) 75%
    );
    z-index: 1;
}
.hero-image > * { position: relative; z-index: 2; }

/* Small helper text */
.hint-badge {
    display: inline-block;
    font-size: .75rem;
    padding: .35rem .6rem;
    border-radius: 999px;
    background: #f1f3f5;
    color: #495057;
}

/* Mobile behavior */
@media (max-width: 768px) {
    .hero-image { display: none; }
    .login-panel { padding: 40px 25px; border-radius: 16px; }
}
</style>
</head>

<body>

<div class="container-fluid">
<div class="row min-vh-100">

    <!-- LEFT: LOGIN FORM -->
    <div class="col-md-6 d-flex align-items-center">
        <div class="w-100 px-3 px-md-5">

            <div class="login-panel w-100">

                <img src="assets/logo.jpg" alt="Logo" style="height:42px;" class="mb-4">

                <h2 class="fw-bold mb-1">Welcome back!</h2>
                <p class="text-muted mb-3">Login to Rice Inventory Control System</p>

                <!-- Optional: Role hint -->
                <div class="mb-3">
                    <span class="hint-badge"><i class="fa-solid fa-user-shield me-1"></i> Admin</span>
                    <span class="hint-badge"><i class="fa-solid fa-cash-register me-1"></i> Cashier</span>
                    <span class="hint-badge"><i class="fa-solid fa-user-tie me-1"></i> Owner</span>
                </div>

                <!-- Alerts -->
                <?php if($success): ?>
                    <div class="alert alert-success py-2">
                        <?= htmlspecialchars($success) ?>
                    </div>
                <?php endif; ?>

                <?php if($error): ?>
                    <div class="alert alert-danger py-2">
                        <?= htmlspecialchars($error) ?>
                    </div>
                <?php endif; ?>

                <form action="auth.php" method="POST" class="needs-validation" novalidate onsubmit="disableBtn()">

                    <div class="mb-3">
                        <label class="form-label">Username</label>
                        <input type="text" name="username" id="username"
                               class="form-control" placeholder="Enter your username"
                               autocomplete="username" required>
                        <div class="invalid-feedback">Please enter your username.</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Password</label>
                        <div class="input-group">
                            <input type="password" name="password" id="password"
                                   class="form-control" placeholder="Enter your password"
                                   autocomplete="current-password" required>
                            <span class="input-group-text bg-white" onclick="togglePassword()" style="cursor:pointer;">
                                <i id="eyeIcon" class="fa fa-eye"></i>
                            </span>
                            <div class="invalid-feedback">Please enter your password.</div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="remember" id="remember">
                            <label class="form-check-label" for="remember">Remember me</label>
                        </div>
                        <!-- Placeholder link (optional) -->
                        <a href="#" class="small text-decoration-none">Forgot password?</a>
                    </div>

                    <button type="submit" id="loginBtn" class="btn btn-dark w-100 btn-login">
                        Login
                    </button>

                </form>

                <p class="text-muted mt-4 small mb-0">
                    🔒 Rice Inventory Control System
                </p>

            </div>
        </div>
    </div>

    <!-- RIGHT: IMAGE -->
    <div class="col-md-6 hero-image d-none d-md-block"></div>

</div>
</div>

<script>
function togglePassword() {
    const pwd = document.getElementById("password");
    const eye = document.getElementById("eyeIcon");
    const show = pwd.type === "password";
    pwd.type = show ? "text" : "password";
    eye.className = show ? "fa fa-eye-slash" : "fa fa-eye";
}

function disableBtn(){
    const btn = document.getElementById("loginBtn");
    btn.disabled = true;
    btn.innerText = "Logging in...";
}

// Bootstrap validation
(() => {
  'use strict'
  const forms = document.querySelectorAll('.needs-validation')
  Array.from(forms).forEach(form => {
    form.addEventListener('submit', event => {
      if (!form.checkValidity()) {
        event.preventDefault()
        event.stopPropagation()
      }
      form.classList.add('was-validated')
    }, false)
  })
})()

// Autofocus username
document.getElementById('username')?.focus();
</script>

</body>
</html>
