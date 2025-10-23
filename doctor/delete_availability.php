<?php
// doctor/delete_availability.php
session_start();
require_once __DIR__ . '/error_handler.php';
require_once __DIR__ . '/../db.php';

// 1. Ensure only logged-in doctors can delete
if (!isset($_SESSION['doctor_id'])) {
    header("Location: ../staff_login.html");
    exit();
}
$docId = $_SESSION['doctor_id'];

// 2. Get the rule ID from the query string
if (!isset($_GET['id']) || !ctype_digit($_GET['id'])) {
    die("Invalid availability ID.");
}
$ruleId = (int)$_GET['id'];

// 3. Delete only if this rule belongs to the logged-in doctor
$stmt = $pdo->prepare("
    DELETE FROM doctor_availability
    WHERE id = ? AND doctor_id = ?
");
$stmt->execute([$ruleId, $docId]);

// 4. Redirect back to the availability management page
header("Location: availability.php");
exit();
?>
