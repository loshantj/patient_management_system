<?php
require_once __DIR__ . '/error_handler.php';
require 'db.php';
session_start();

// 1. Collect form data
$patientCode     = trim($_POST['patient_id'] ?? '');
$appointmentDate = $_POST['appointment_date'] ?? '';
$doctorId        = $_POST['doctor_id'] ?? '';
$appointmentTime = $_POST['appointment_time'] ?? '';
$reason          = trim($_POST['reason'] ?? '');

// 2. Basic validation
if (!$patientCode || !$appointmentDate || !$appointmentTime) {
    die("Please fill out Patient ID, date, and time.");
}

// 3. Confirm patient exists by custom patient_id
$stmt = $pdo->prepare("SELECT id FROM patients WHERE patient_id = ?");
$stmt->execute([$patientCode]);
$patientId = $stmt->fetchColumn();
if (!$patientId) {
    die("Invalid Patient ID.");
}

// 4. Auto‐assign doctor if needed
if ($doctorId === 'auto' || !$doctorId) {
    $stmt = $pdo->prepare("
        SELECT d.id
        FROM doctors d
        WHERE NOT EXISTS (
            SELECT 1
            FROM appointments a
            WHERE a.doctor_id = d.id
              AND a.appointment_date = ?
              AND a.appointment_time = ?
        )
        LIMIT 1
    ");
    $stmt->execute([$appointmentDate, $appointmentTime]);
    $doctorId = $stmt->fetchColumn();

    if (!$doctorId) {
        die("No doctors available at the selected time.");
    }
}

// 5. Insert the appointment (status defaults to 'pending')
$stmt = $pdo->prepare("
    INSERT INTO appointments
      (patient_id, doctor_id, appointment_date, appointment_time, reason, status)
    VALUES (?, ?, ?, ?, ?, 'pending')
");
$stmt->execute([
    $patientId,
    $doctorId,
    $appointmentDate,
    $appointmentTime,
    $reason
]);

// 6. Redirect to patient dashboard
header("Location: patient/dashboard.php");
exit();
?>
