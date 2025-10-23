<?php
session_start();
require_once __DIR__ . '/error_handler.php';
require 'db.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!$email || !$password) {
        $error = "Please enter both email and password.";
    } else {
        $stmt = $pdo->prepare("SELECT * FROM patients WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            // ✅ Store patient info in session
            $_SESSION['patient_id']   = $user['id'];
            $_SESSION['patient_name'] = $user['full_name'];
            $_SESSION['role']         = 'patient';   // 👈 important for header.php

            // Redirect to Patient Dashboard
            header("Location: patient/dashboard.php");
            exit();
        } else {
            $error = "Invalid email or password!";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Patient Login</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body { background-color: #f5f9ff; display: flex; justify-content: center; align-items: center; height: 100vh; }
    .login-box { background: white; padding: 2rem; border-radius: 12px; box-shadow: 0 0 10px rgba(0,0,0,0.1); width: 350px; }
  </style>
</head>
<body>
  <div class="login-box">
    <h4 class="mb-3">Patient Login</h4>

    <?php if ($error): ?>
      <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST" action="">
      <div class="mb-3">
        <label class="form-label">Email address</label>
        <input type="email" name="email" class="form-control" required placeholder="Enter email" autocomplete="username">
      </div>
      <div class="mb-3">
        <label class="form-label">Password</label>
        <input type="password" name="password" class="form-control" required placeholder="Enter password" autocomplete="current-password">
      </div>
      <button type="submit" class="btn btn-primary w-100">Login</button>
    </form>

    <div class="mt-3 text-center">
      <a href="register.html">Don't have an account? Register</a>
    </div>
  </div>
</body>
</html>
