<?php
// doctor/save_availability.php
session_start();
require_once __DIR__ . '/../db.php';

// Only doctors may add rules
if (!isset($_SESSION['doctor_id'])) {
    header("Location: ../staff_login.html");
    exit();
}
$docId = $_SESSION['doctor_id'];

// Collect and validate
$day   = isset($_POST['day_of_week']) ? (int)$_POST['day_of_week'] : null;
$start = $_POST['start_time']  ?? '';
$end   = $_POST['end_time']    ?? '';
$dur   = isset($_POST['slot_duration_min']) ? (int)$_POST['slot_duration_min'] : 0;

if ($day === null || !$start || !$end || $dur <= 0) {
    die("Invalid input.");
}
if ($start >= $end) {
    die("Start time must be before end time.");
}

// Insert into doctor_availability
$stmt = $pdo->prepare("
    INSERT INTO doctor_availability
      (doctor_id, day_of_week, start_time, end_time, slot_duration_min)
    VALUES (?, ?, ?, ?, ?)
");
$stmt->execute([$docId, $day, $start, $end, $dur]);

// Redirect back to the availability page
header("Location: availability.php");
exit();
?>
