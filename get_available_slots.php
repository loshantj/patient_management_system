<?php
require_once __DIR__ . '/error_handler.php';
require 'db.php';
header('Content-Type: application/json; charset=utf-8');

try {
    if (empty($_GET['doctor_id']) || empty($_GET['date'])) {
        echo json_encode([]);
        exit;
    }

    $doctorId = (int)$_GET['doctor_id'];
    $date     = $_GET['date'];

    // 1️⃣ Generate all slot times based on availability
    $sql = "
        SELECT 
            TIME_FORMAT(
                TIMESTAMPADD(MINUTE, seq.n * a.slot_duration_min, a.start_time),
                '%H:%i:%s'
            ) AS slot_time
        FROM doctor_availability a
        JOIN (
            SELECT 0 AS n UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3
            UNION ALL SELECT 4 UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7
            UNION ALL SELECT 8 UNION ALL SELECT 9 UNION ALL SELECT 10 UNION ALL SELECT 11
            UNION ALL SELECT 12 UNION ALL SELECT 13 UNION ALL SELECT 14 UNION ALL SELECT 15
            UNION ALL SELECT 16 UNION ALL SELECT 17 UNION ALL SELECT 18 UNION ALL SELECT 19
            UNION ALL SELECT 20 UNION ALL SELECT 21 UNION ALL SELECT 22 UNION ALL SELECT 23
            UNION ALL SELECT 24 UNION ALL SELECT 25 UNION ALL SELECT 26 UNION ALL SELECT 27
            UNION ALL SELECT 28 UNION ALL SELECT 29 UNION ALL SELECT 30 UNION ALL SELECT 31
        ) seq
        WHERE a.doctor_id = :doc
          AND a.day_of_week = (DAYOFWEEK(:date) - 1)
          AND TIMESTAMPADD(MINUTE, seq.n * a.slot_duration_min, a.start_time) < a.end_time
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute(['doc' => $doctorId, 'date' => $date]);
    $allSlots = $stmt->fetchAll(PDO::FETCH_COLUMN);

    if (!$allSlots) {
        echo json_encode([]);
        exit;
    }

    // 2️⃣ Fetch booked times
    $bookedStmt = $pdo->prepare("
        SELECT appointment_time 
        FROM appointments 
        WHERE doctor_id = :doc AND appointment_date = :date
    ");
    $bookedStmt->execute(['doc' => $doctorId, 'date' => $date]);
    $bookedSlots = $bookedStmt->fetchAll(PDO::FETCH_COLUMN);

    // Normalize all booked times to HH:MM:SS format
    $bookedNormalized = array_map(function($t) {
        return date('H:i:s', strtotime($t));
    }, $bookedSlots);
    $bookedSet = array_flip($bookedNormalized);

    // 3️⃣ Filter only unbooked + future slots
    $freeSlots = [];
    $now = new DateTime('now', new DateTimeZone('Asia/Colombo'));
    $today = $now->format('Y-m-d');

    foreach ($allSlots as $slot) {
        // skip booked
        if (isset($bookedSet[$slot])) continue;

        // skip past times for today
        if ($date === $today && $slot <= $now->format('H:i:s')) continue;

        $freeSlots[] = date('H:i', strtotime($slot)); // output clean HH:MM
    }

    echo json_encode(array_values($freeSlots));

} catch (Throwable $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>
