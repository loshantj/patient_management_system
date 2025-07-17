<?php
require 'db.php';
session_start();

// 1) Ensure patient is logged in
if (!isset($_SESSION['patient_id'])) {
    header("Location: patient_login.html");
    exit();
}

$patient_id = $_SESSION['patient_id'];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $doctor_id        = $_POST['doctor_id'] ?? '';
    $appointment_date = $_POST['appointment_date'] ?? '';
    $appointment_time = $_POST['appointment_time'] ?? '';
    $reason           = trim($_POST['reason'] ?? '');

    // 2) Validate
    if (!$appointment_date || !$appointment_time) {
        die("Please select both date and time.");
    }

    // 3) Auto-assign doctor if "auto" selected
    if ($doctor_id === "auto" || $doctor_id === '') {
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
        $stmt->execute([$appointment_date, $appointment_time]);
        $doctor_id = $stmt->fetchColumn();
        if (!$doctor_id) {
            die("No doctors available at the selected time.");
        }
    }

    // 4) Insert WITHOUT specifying the `id` column
    $stmt = $pdo->prepare("
        INSERT INTO appointments 
          (patient_id, doctor_id, appointment_date, appointment_time, reason, status)
        VALUES 
          (?, ?, ?, ?, ?, 'pending')
    ");
    $stmt->execute([
        $patient_id,
        $doctor_id,
        $appointment_date,
        $appointment_time,
        $reason
    ]);

    // 5) Redirect to patient dashboard
    header("Location: patient/dashboard.php");
    exit();
}
?>
