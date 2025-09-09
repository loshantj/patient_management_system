<?php
require 'db.php';
header('Content-Type: application/json; charset=utf-8');

try {
    if (empty($_GET['doctor_id']) || empty($_GET['date'])) {
        throw new Exception("Missing doctor_id or date");
    }
    $doctorId = (int)$_GET['doctor_id'];
    $date     = $_GET['date'];

    // 1) Inner: generate every possible slot_time for that doctor & weekday
    $innerSql = "
      SELECT 
        a.doctor_id,
        ADDTIME(a.start_time, INTERVAL seq.n * a.slot_duration_min MINUTE) AS slot_time
      FROM doctor_availability AS a
      CROSS JOIN (
        SELECT 0 AS n UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3
        UNION ALL SELECT 4 UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7
        UNION ALL SELECT 8 UNION ALL SELECT 9
        -- add more UNION ALL SELECTs if you need >10 slots per day
      ) AS seq
      WHERE a.doctor_id   = :doc
        AND a.day_of_week = (DAYOFWEEK(:date) - 1)
        AND ADDTIME(a.start_time, INTERVAL seq.n * a.slot_duration_min MINUTE) < a.end_time
    ";
    $innerStmt = $pdo->prepare($innerSql);
    $innerStmt->execute([
      'doc'  => $doctorId,
      'date' => $date
    ]);

    // 2) Fetch generated slots into PHP array
    $allSlots = $innerStmt->fetchAll(PDO::FETCH_ASSOC);

    // 3) Now filter out those that are booked
    //    Build a simple lookup of booked times
    $bookedStmt = $pdo->prepare("
      SELECT appointment_time 
      FROM appointments 
      WHERE doctor_id = :doc 
        AND appointment_date = :date
    ");
    $bookedStmt->execute([
      'doc'  => $doctorId,
      'date' => $date
    ]);
    $booked = array_column($bookedStmt->fetchAll(PDO::FETCH_ASSOC), 'appointment_time');
    $booked = array_flip($booked); // for quick isset()

    // 4) Prepare final list of free, formatted slots
    $freeSlots = [];
    foreach ($allSlots as $row) {
      $t = $row['slot_time'];
      if (!isset($booked[$t])) {
        // format HH:MM
        $freeSlots[] = date('H:i', strtotime($t));
      }
    }

    // 5) Return JSON
    echo json_encode($freeSlots);
}
catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
