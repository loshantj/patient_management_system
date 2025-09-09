<?php
// get_available_doctors.php
require 'db.php';
header('Content-Type: application/json');

// 1) Validate date
if (empty($_GET['date'])) {
    echo json_encode([]);
    exit;
}
$date = $_GET['date'];

// 2) Day-of-week mapping: 1=Sunday…7=Saturday → 0=Sunday…6=Saturday
//    MySQL DAYOFWEEK() returns 1 for Sunday, so subtract 1.
$pdoDayExpr = "DAYOFWEEK(:date) - 1";

// 3) Fetch only doctors with availability AND at least one free slot
$sql = "
SELECT
  d.id,
  d.full_name
FROM doctors d
-- join only those doctors who work that weekday
JOIN doctor_availability a
  ON a.doctor_id = d.id
 AND a.day_of_week = ($pdoDayExpr)
-- left-join their appointments on that date within their working hours
LEFT JOIN appointments b
  ON b.doctor_id = d.id
 AND b.appointment_date = :date
 AND b.appointment_time >= a.start_time
 AND b.appointment_time < a.end_time
GROUP BY d.id, d.full_name
HAVING 
  -- total booked slots less than total possible slots
  COUNT(b.id) < SUM(
    FLOOR(
      TIMESTAMPDIFF(MINUTE, a.start_time, a.end_time)
      / a.slot_duration_min
    )
  )
ORDER BY d.full_name
";

$stmt = $pdo->prepare($sql);
$stmt->execute(['date' => $date]);
$doctors = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 4) Return JSON
echo json_encode($doctors);
