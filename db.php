<?php
// db.php — Secure database connection with error logging

$host = "localhost";
$dbname = "clinic_pms";
$user = "root";
$pass = "";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // Optional: $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // Log the error message into a file (for debugging)
    $errorMessage = "[" . date("Y-m-d H:i:s") . "] Database Error: " . $e->getMessage() . PHP_EOL;
    file_put_contents(__DIR__ . '/error_log.txt', $errorMessage, FILE_APPEND);

    // Show a friendly message to the user
    die("Sorry, we’re having technical issues. Please try again later.");
}
?>
