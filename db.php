<?php
$host = "localhost";      
$dbname = "clinic_pms";   
$user = "root";           
$pass = "";               

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // echo "Database connected successfully!";
} catch (PDOException $e) {
    echo "DB Connection failed: " . $e->getMessage();
}
?>
