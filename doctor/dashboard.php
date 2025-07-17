<?php
session_start();
require '../db.php';

if (!isset($_SESSION['doctor_id'])) {
    header("Location: ../staff_login.html");
    exit();
}

$doctorId = $_SESSION['doctor_id'];
$doctorName = $_SESSION['doctor_name'];
$today = date('Y-m-d');

// Get current pending appointment
$stmt = $pdo->prepare("SELECT a.*, p.full_name, p.gender, p.dob, p.phone, p.address, p.email 
                       FROM appointments a 
                       JOIN patients p ON a.patient_id = p.id 
                       WHERE a.doctor_id = ? AND a.appointment_date = ? AND a.status = 'pending' 
                       ORDER BY a.appointment_time ASC 
                       LIMIT 1");
$stmt->execute([$doctorId, $today]);
$current = $stmt->fetch();

// Count total and completed appointments
$totalPatients = $pdo->prepare("SELECT COUNT(*) FROM appointments WHERE doctor_id = ? AND appointment_date = ?");
$totalPatients->execute([$doctorId, $today]);
$total = $totalPatients->fetchColumn();

$completedPatients = $pdo->prepare("SELECT COUNT(*) FROM appointments WHERE doctor_id = ? AND appointment_date = ? AND status = 'completed'");
$completedPatients->execute([$doctorId, $today]);
$completed = $completedPatients->fetchColumn();

// Next patient
$next = null;
if ($current) {
    $stmtNext = $pdo->prepare("SELECT a.*, p.full_name FROM appointments a 
                               JOIN patients p ON a.patient_id = p.id 
                               WHERE a.doctor_id = ? AND a.appointment_date = ? AND a.status = 'pending' AND a.id != ? 
                               ORDER BY a.appointment_time ASC LIMIT 1");
    $stmtNext->execute([$doctorId, $today, $current['id']]);
    $next = $stmtNext->fetch();
}

// Fetch previous notes (excluding current appointment)
$previousNotes = [];
if ($current) {
    $noteQuery = $pdo->prepare("SELECT appointment_date, notes 
                                FROM appointments 
                                WHERE patient_id = ? AND status = 'completed' AND appointment_date < ? 
                                ORDER BY appointment_date DESC");
    $noteQuery->execute([$current['patient_id'], $today]);
    $previousNotes = $noteQuery->fetchAll();
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['appointment_id'])) {
    $diagnosis = trim($_POST['diagnosis']);
    $prescription = trim($_POST['prescription']);
    $treatment = trim($_POST['treatment']);
    $notes = "Diagnosis: $diagnosis\nPrescription: $prescription\nTreatment: $treatment";

    $update = $pdo->prepare("UPDATE appointments SET notes = ?, status = 'completed' WHERE id = ?");
    $update->execute([$notes, $_POST['appointment_id']]);

    header("Location: dashboard.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Doctor Dashboard - Clinic PMS</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
  <style>
    body { background-color: #f5f9ff; }
    .sidebar { background-color: #fff; min-height: 100vh; box-shadow: 2px 0 10px rgba(0,0,0,0.05); }
    .card { border: none; border-radius: 10px; }
    .btn-checkup { background-color: #198754; color: white; }
    .btn-next { background-color: #0d6efd; color: white; }
  </style>
</head>
<body>

<div class="container-fluid">
  <div class="row">
    <!-- Sidebar -->
    <div class="col-md-2 sidebar py-4 px-3">
      <div class="text-center mb-4">
        <img src="../assets/img/doctor1.jpg" class="rounded-circle" width="0" />
        <h5 class="mt-2"><?= htmlspecialchars($doctorName) ?></h5>
        <p class="text-muted mb-0">Doctor</p>
      </div>
      <ul class="nav flex-column">
        <li class="nav-item mb-2"><a href="#" class="nav-link text-primary fw-semibold">🧑‍⚕️ My Patients</a></li>
        <li class="nav-item mb-2"><a href="#" class="nav-link">📅 Appointments Today</a></li>
        <li class="nav-item mb-2"><a href="#" class="nav-link">📁 Medical History</a></li>
        <li class="nav-item mt-3"><a href="../logout.php" class="btn btn-outline-danger w-100">Logout</a></li>
      </ul>
    </div>

    <!-- Main Content -->
    <div class="col-md-10 p-4">
      <div class="row mb-4 text-center">
        <div class="col-md-3">
          <div class="card p-3">
            <h5 class="card-title text-muted">Current Queue</h5>
            <h4><?= $current ? "#{$current['id']}<br><small class='text-secondary'>{$current['full_name']}</small>" : "No patient" ?></h4>
          </div>
        </div>
        <div class="col-md-3">
          <div class="card p-3">
            <h5 class="card-title text-muted">Total Patients Today</h5>
            <h4><?= $total ?></h4>
          </div>
        </div>
        <div class="col-md-3">
          <div class="card p-3">
            <h5 class="card-title text-muted">Completed</h5>
            <h4><?= $completed ?></h4>
          </div>
        </div>
        <div class="col-md-3">
          <div class="card p-3">
            <h5 class="card-title text-muted">Next Patient</h5>
            <h4><?= $next ? "{$next['full_name']}<br><small class='text-secondary'>{$next['appointment_time']}</small>" : "N/A" ?></h4>
          </div>
        </div>
      </div>

      <?php if ($current): ?>
      <div class="row g-4">
        <!-- Patient Details -->
        <div class="col-md-5">
          <div class="card p-3">
            <h5 class="fw-bold"><?= htmlspecialchars($current['full_name']) ?></h5>
            <p class="mb-1"><?= $current['gender'] ?> | <?= date_diff(date_create($current['dob']), date_create('today'))->y ?> yrs</p>
            <p class="mb-1">Appointed: <?= $current['appointment_time'] ?></p>
            <p class="mb-1"><strong>📞</strong> <?= $current['phone'] ?></p>
            <p class="mb-1"><strong>📍</strong> <?= $current['address'] ?></p>
            <p class="mb-1"><strong>Reason:</strong> <?= htmlspecialchars($current['reason']) ?></p>
            <hr>
            <h6 class="fw-semibold">Previous Notes:</h6>
            <ul class="text-muted small">
              <?php if (count($previousNotes) > 0): ?>
                <?php foreach ($previousNotes as $note): ?>
                  <li><strong><?= $note['appointment_date'] ?>:</strong> <?= nl2br(htmlspecialchars($note['notes'])) ?></li>
                <?php endforeach; ?>
              <?php else: ?>
                <li>No previous notes found.</li>
              <?php endif; ?>
            </ul>
          </div>
        </div>

        <!-- Consultation Form -->
        <div class="col-md-7">
          <div class="card p-3">
            <h5 class="fw-bold mb-3">🩺 Consultation Notes</h5>
            <form method="POST">
              <input type="hidden" name="appointment_id" value="<?= $current['id'] ?>">
              <div class="mb-3">
                <label>Diagnosis</label>
                <input type="text" class="form-control" name="diagnosis" required />
              </div>
              <div class="mb-3">
                <label>Prescription</label>
                <input type="text" class="form-control" name="prescription" required />
              </div>
              <div class="mb-3">
                <label>Treatment Plan</label>
                <textarea class="form-control" name="treatment" rows="3" required></textarea>
              </div>
              <div class="d-flex justify-content-end gap-3">
                <button type="submit" class="btn btn-checkup">✓ Done Checkup</button>
                <a href="dashboard.php" class="btn btn-next">⏭️ Call Next Patient</a>
              </div>
            </form>
          </div>
        </div>
      </div>
      <?php else: ?>
        <div class="text-center mt-5">
          <h4>No patients in queue right now.</h4>
        </div>
      <?php endif; ?>
    </div>
  </div>
</div>

</body>
</html>
