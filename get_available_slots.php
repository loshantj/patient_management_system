<?php
require 'db.php';

if (!isset($_GET['doctor_id']) || !isset($_GET['date'])) {
    http_response_code(400);
    echo json_encode(["error" => "Missing doctor_id or date"]);
    exit();
}

$doctorId = $_GET['doctor_id'];
$date = $_GET['date'];

// Step 1: Generate all 30-minute slots between 9 AM and 5 PM
$startTime = strtotime('09:00');
$endTime = strtotime('17:00');
$allSlots = [];

for ($time = $startTime; $time < $endTime; $time += 30 * 60) {
    $allSlots[] = date('H:i', $time);
}

// Step 2: Get booked slots for that doctor on that day
$stmt = $pdo->prepare("SELECT appointment_time FROM appointments 
                       WHERE doctor_id = ? AND appointment_date = ? AND status != 'cancelled'");
$stmt->execute([$doctorId, $date]);
$booked = $stmt->fetchAll(PDO::FETCH_COLUMN);

// Step 3: Filter out booked slots
$availableSlots = array_diff($allSlots, $booked);

// Return as JSON
header('Content-Type: application/json');
echo json_encode(array_values($availableSlots));
