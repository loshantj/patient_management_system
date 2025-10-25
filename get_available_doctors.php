<?php
require_once __DIR__ . '/error_handler.php';
require 'db.php';
header('Content-Type: application/json');

if (empty($_GET['date'])) {
    echo json_encode([]);
    exit;
}

$date = $_GET['date'];

$sql = "
SELECT
  d.id,
  CONCAT(d.full_name, ' (', d.specialization, ')') AS full_name
FROM doctors d
JOIN doctor_availability a
  ON a.doctor_id = d.id
 AND a.day_of_week = (DAYOFWEEK(:date) - 1)
LEFT JOIN appointments b
  ON b.doctor_id = d.id
 AND b.appointment_date = :date
 AND b.appointment_time >= a.start_time
 AND b.appointment_time < a.end_time
GROUP BY d.id, d.full_name, d.specialization
HAVING 
  COALESCE(COUNT(b.id), 0) < COALESCE(SUM(
    FLOOR(TIMESTAMPDIFF(MINUTE, a.start_time, a.end_time) / a.slot_duration_min)
  ), 0)
ORDER BY d.full_name
";

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['date' => $date]);
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
} catch (Throwable $e) {
    error_log("[DOCTORS ERROR] " . $e->getMessage() . "\n", 3, __DIR__ . '/error_log.txt');
    echo json_encode([]);
}
?>
