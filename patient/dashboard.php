<?php
session_start();
require_once '../db.php'; // Adjust the path if needed

if (!isset($_SESSION['patient_id'])) {
    header("Location: ../patient_login.html");
    exit();
}

$patient_id = $_SESSION['patient_id'];
$patient_name = $_SESSION['patient_name'] ?? 'Patient';

try {
    $stmt = $pdo->prepare("
        SELECT a.appointment_date, a.appointment_time, a.reason, d.full_name AS doctor_name
        FROM appointments a
        JOIN doctors d ON a.doctor_id = d.id
        WHERE a.patient_id = ?
          AND a.appointment_date >= CURDATE()
        ORDER BY a.appointment_date, a.appointment_time
    ");
    $stmt->execute([$patient_id]);
    $appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error fetching appointments: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Patient Dashboard</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            background-color: #f5f9ff;
            font-family: 'Segoe UI', sans-serif;
        }
        .sidebar {
            height: calc(100vh - 56px);
            background-color: #ffffff;
            border-right: 1px solid #e0e0e0;
            padding-top: 30px;
            position: fixed;
            top: 56px;
            left: 0;
            width: 220px;
        }
        .main-content {
            margin-left: 220px;
            margin-top: 56px;
            padding: 30px;
        }
        .sidebar .nav-link {
            color: #333;
            font-weight: 500;
        }
        .sidebar .nav-link.active {
            background-color: #e6f0ff;
            border-radius: 8px;
        }
        .profile-img {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            object-fit: cover;
        }
        .appointment-card {
            background-color: #fff;
            border-radius: 12px;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05);
            padding: 20px;
        }
    </style>
</head>

<body>

<nav class="navbar navbar-light bg-white shadow-sm px-4 fixed-top">
    <a class="navbar-brand fw-bold" href="../index.html">Clinic PMS</a>
</nav>

<div class="container-fluid">
    <div class="row">
        <div class="col-md-3 col-lg-2 sidebar d-flex flex-column align-items-center">
            <img src="https://i.pravatar.cc/80" alt="profile" class="profile-img mb-3">
            <h6 class="fw-bold mb-0"><?= htmlspecialchars($patient_name) ?></h6>
            <small class="text-muted mb-4">Patient</small>
            <nav class="nav flex-column w-100 px-3">
                <a class="nav-link active" href="#"><i class="bi bi-person-fill me-2"></i> Profile</a>
                <a class="nav-link" href="#"><i class="bi bi-calendar-check me-2"></i> Appointments</a>
                <a class="nav-link" href="#"><i class="bi bi-journal-medical me-2"></i> Medical History</a>
            </nav>
        </div>

        <div class="col main-content">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h3>Welcome, <?= htmlspecialchars($patient_name) ?>!</h3>
                <a href="../appointments.html" class="btn btn-primary">
                    <i class="bi bi-plus-circle me-2"></i> Book New Appointment
                </a>
            </div>

            <div class="appointment-card">
                <h5 class="fw-semibold mb-3">Upcoming Appointments</h5>

                <?php if (count($appointments) === 0): ?>
                    <p class="text-muted">No upcoming appointments.</p>
                <?php else: ?>
                    <?php foreach ($appointments as $appt): ?>
                        <div class="d-flex justify-content-between border-bottom py-2">
                            <div>
                                <div><?= htmlspecialchars($appt['doctor_name']) ?></div>
                                <small class="text-muted">
                                    <?= date('D, j M Y', strtotime($appt['appointment_date'])) ?>,
                                    <?= date('g:i A', strtotime($appt['appointment_time'])) ?>
                                </small>
                            </div>
                            <span class="text-primary"><?= htmlspecialchars($appt['reason']) ?></span>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
