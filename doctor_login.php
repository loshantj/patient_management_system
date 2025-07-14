<?php
// doctor_login.php

require 'db.php'; // Make sure this file sets up $pdo properly
session_start();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    // Fetch doctor record by email
    $stmt = $pdo->prepare("SELECT * FROM doctors WHERE email = ?");
    $stmt->execute([$email]);
    $doctor = $stmt->fetch();

    if ($doctor && password_verify($password, $doctor['password'])) {
        // Login successful
        $_SESSION['doctor_id'] = $doctor['id'];
        $_SESSION['doctor_name'] = $doctor['full_name'];
        $_SESSION['role'] = 'doctor';

        header("Location: doctor/dashboard.php");
        exit();
    } else {
        // Login failed
        echo "<script>alert('Invalid email or password.'); window.location.href = 'staff_login.html';</script>";
        exit();
    }
}
?>
