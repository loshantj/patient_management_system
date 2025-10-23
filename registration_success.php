<?php
require_once __DIR__ . '/error_handler.php';
require 'db.php';

if (!isset($_GET['pid'])) {
    die("Missing patient ID.");
}
$patient_code = $_GET['pid'];

// Fetch patient by the custom patient_id
$stmt = $pdo->prepare("SELECT * FROM patients WHERE patient_id = ?");
$stmt->execute([$patient_code]);
$patient = $stmt->fetch();

if (!$patient) {
    die("Patient not found.");
}

$qr_path = $patient['qr_code'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Registration Successful</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f5f9ff; }
        .container { max-width: 420px; margin: 40px auto; padding: 2rem; background: #fff; border-radius: 14px; box-shadow: 0 2px 12px rgba(0,0,0,0.09);}
        .qr-code { display: block; margin: 16px auto; width: 180px; border: 1px solid #eee; border-radius: 10px; }
    </style>
</head>
<body>
<div class="container text-center">
    <h2 class="mb-2">Registration Successful!</h2>
    <h5 class="mb-4 text-success"><?= htmlspecialchars($patient['full_name']) ?></h5>
    <p class="fw-bold">Patient ID: <span class="text-primary"><?= htmlspecialchars($patient['patient_id']) ?></span></p>

    <img src="<?= htmlspecialchars($qr_path) ?>" alt="Patient QR" class="qr-code mb-2">

    <div class="d-grid gap-2 mb-3">
        <a href="<?= htmlspecialchars($qr_path) ?>" download class="btn btn-outline-primary">Download QR</a>
        <button onclick="window.print()" class="btn btn-outline-secondary">Print This Page</button>
    </div>
    <a href="patient_login.html" class="btn btn-primary mt-2">Go to Login</a>
</div>
</body>
</html>
