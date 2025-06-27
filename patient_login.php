<?php
session_start();
require 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    $stmt = $pdo->prepare("SELECT * FROM patients WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['patient_id'] = $user['id'];
        $_SESSION['email'] = $user['email'];
        header("Location: patient/dashboard.html");
        exit();
    } else {
        $error = "Invalid email or password!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<!-- show the same login form again with error -->
<head>
  <meta charset="UTF-8">
  <title>Login Error</title>
  <link rel="stylesheet" href="assets/css/style.css" />
</head>
<body>
  <div class="login-box">
    <h4 style="color:red;">Login Failed</h4>
    <p><?= $error ?></p>
    <a href="patient_login.html">Try Again</a>
  </div>
</body>
</html>
