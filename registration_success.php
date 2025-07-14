<?php
require 'db.php';

if (!isset($_GET['id'])) {
    die("Invalid request.");
}

$patient_id = $_GET['id'];

// Fetch patient info including QR code path
$stmt = $pdo->prepare("SELECT full_name, qr_code FROM patients WHERE id = ?");
$stmt->execute([$patient_id]);
$patient = $stmt->fetch();

if (!$patient) {
    die("Patient not found.");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Registration Success</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
  <div class="container mt-5 text-center">
    <div class="bg-white p-4 rounded shadow" style="max-width: 500px; margin: auto;">
      <h3 class="mb-3 text-success">🎉 Registration Successful</h3>
      <p class="lead">Welcome, <strong><?= htmlspecialchars($patient['full_name']) ?></strong></p>
      <p>Your patient QR code is below. You can print or download it for your future visits.</p>

      <!-- Display QR Code -->
      <img src="<?= htmlspecialchars($patient['qr_code']) ?>" alt="QR Code" width="200" id="qrImage" class="my-3">

      <!-- Download and Print Buttons -->
      <div class="d-flex justify-content-center gap-3">
        <a href="<?= htmlspecialchars($patient['qr_code']) ?>" download class="btn btn-primary">
          ⬇️ Download
        </a>
        <button class="btn btn-outline-secondary" onclick="printQRCode()">🖨️ Print</button>
      </div>

      <a href="patient_login.html" class="btn btn-link mt-4">Go to Login</a>
    </div>
  </div>

  <script>
    function printQRCode() {
      const win = window.open();
      win.document.write('<img src="<?= htmlspecialchars($patient['qr_code']) ?>" width="300">');
      win.print();
      win.close();
    }
  </script>
</body>
</html>
