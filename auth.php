<?php
session_start();
include 'config/db.php';

if($_SERVER['REQUEST_METHOD'] !== 'POST'){
    header("Location: login.php");
    exit;
}

$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';
$remember = isset($_POST['remember']);

if($username === '' || $password === ''){
    header("Location: login.php?error=" . urlencode("Please enter username and password."));
    exit;
}

// Get user by username (active only)
$stmt = $conn->prepare("SELECT user_id, username, password, role, status FROM users WHERE username=? LIMIT 1");
$stmt->bind_param("s", $username);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

if(!$user){
    header("Location: login.php?error=" . urlencode("Invalid username or password."));
    exit;
}

if(isset($user['status']) && strtolower($user['status']) !== 'active'){
    header("Location: login.php?error=" . urlencode("Account is not active. Please contact admin."));
    exit;
}

/*
  ✅ Password handling:
  - If passwords are hashed -> password_verify works
  - If old system saved plain text -> fallback (temporary)
  Recommendation: update all stored passwords to hashed later.
*/
$stored = $user['password'];
$isValid = false;

if(password_verify($password, $stored)){
    $isValid = true;
} else {
    // fallback for plain-text passwords (temporary)
    if(hash_equals((string)$stored, (string)$password)){
        $isValid = true;
    }
}

if(!$isValid){
    header("Location: login.php?error=" . urlencode("Invalid username or password."));
    exit;
}

// ✅ Set session
$_SESSION['user_id']  = (int)$user['user_id'];
$_SESSION['username'] = $user['username'];
$_SESSION['role']     = $user['role'];

// ✅ Remember me (simple cookie token)
if($remember){
    // NOTE: Better approach is storing tokens in DB. For capstone, this is okay.
    $token = bin2hex(random_bytes(16));
    setcookie("remember_token", $token, time() + (86400 * 30), "/"); // 30 days
    // Optional: store token in session so you can use it later
    $_SESSION['remember_token'] = $token;
} else {
    setcookie("remember_token", "", time() - 3600, "/");
}

// ✅ Log login to login_logs table
$device = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown Device';
$ip = $_SERVER['REMOTE_ADDR'] ?? 'Unknown IP';

$logStmt = $conn->prepare("INSERT INTO login_logs (user_id, login_time, device_info, ip_address)
                           VALUES (?, NOW(), ?, ?)");
$logStmt->bind_param("iss", $_SESSION['user_id'], $device, $ip);
$logStmt->execute();
$logStmt->close();

// ✅ Redirect based on role
$role = strtolower(trim((string)$user['role']));

if($role === 'admin'){
    header("Location: admin/dashboard.php");
    exit;
}
if($role === 'cashier'){
    // change this if your cashier dashboard path is different
    header("Location: cashier/dashboard.php");
    exit;
}
if($role === 'owner'){
    header("Location: owner/dashboard.php");
    exit;
}

// Default fallback
header("Location: login.php?error=" . urlencode("Role not recognized. Contact admin."));
exit;
