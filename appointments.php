<?php
require_once __DIR__ . '/error_handler.php';
require 'db.php';
session_start();
header('Content-Type: application/json; charset=utf-8');

try {
    $patientCode     = trim($_POST['patient_id'] ?? '');
    $appointmentDate = $_POST['appointment_date'] ?? '';
    $doctorId        = $_POST['doctor_id'] ?? '';
    $appointmentTime = $_POST['appointment_time'] ?? '';
    $reason          = trim($_POST['reason'] ?? '');

    if (!$patientCode || !$appointmentDate || !$appointmentTime || !$reason) {
        throw new Exception("Please fill out all required fields.");
    }

    $stmt = $pdo->prepare("SELECT id FROM patients WHERE patient_id = ?");
    $stmt->execute([$patientCode]);
    $patientId = $stmt->fetchColumn();
    if (!$patientId) throw new Exception("Invalid Patient ID.");

    if ($doctorId === 'auto' || !$doctorId) {
        $stmt = $pdo->prepare("
            SELECT d.id
            FROM doctors d
            WHERE NOT EXISTS (
                SELECT 1 FROM appointments a
                WHERE a.doctor_id = d.id
                  AND a.appointment_date = ?
                  AND a.appointment_time = ?
            )
            LIMIT 1
        ");
        $stmt->execute([$appointmentDate, $appointmentTime]);
        $doctorId = $stmt->fetchColumn();
        if (!$doctorId) throw new Exception("No doctors available at that time.");
    }

    $stmt = $pdo->prepare("
        SELECT COUNT(*) FROM appointments
        WHERE doctor_id = ? AND appointment_date = ? AND appointment_time = ?
    ");
    $stmt->execute([$doctorId, $appointmentDate, $appointmentTime]);
    if ($stmt->fetchColumn() > 0) throw new Exception("That time slot is already booked.");

    $stmt = $pdo->prepare("
        INSERT INTO appointments (patient_id, doctor_id, appointment_date, appointment_time, reason, status)
        VALUES (?, ?, ?, ?, ?, 'pending')
    ");
    $stmt->execute([$patientId, $doctorId, $appointmentDate, $appointmentTime, $reason]);

    echo json_encode([
        'status' => 'success',
        'message' => 'Appointment booked successfully!',
        'redirect' => 'patient/dashboard.php'
    ]);
} catch (Exception $e) {
    error_log("[APPOINTMENT ERROR] " . $e->getMessage() . "\n", 3, __DIR__ . '/error_log.txt');
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
