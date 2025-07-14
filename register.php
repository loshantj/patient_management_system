<?php
require 'db.php';
require_once 'assets/qr/phpqrcode.php'; // Make sure this file exists and GD is enabled

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Collect and sanitize input
    $full_name = trim($_POST["full_name"]);
    $email = trim($_POST["email"]);
    $phone = trim($_POST["phone"]);
    $nic = trim($_POST["nic"]);
    $dob = $_POST["dob"];
    $gender = $_POST["gender"];
    $address = trim($_POST["address"]);
    $password = $_POST["password"];
    $confirm_password = $_POST["confirm_password"];

    // 1. Validate required fields
    if (empty($full_name) || empty($email) || empty($phone) || empty($dob) || empty($gender) || empty($password)) {
        die("Please fill in all required fields.");
    }

    // 2. Confirm password match
    if ($password !== $confirm_password) {
        die("Passwords do not match.");
    }

    // 3. Check for duplicate email
    $stmt = $pdo->prepare("SELECT id FROM patients WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        die("An account with this email already exists.");
    }

    // 4. Hash the password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // 5. Insert into patients table
    $stmt = $pdo->prepare("INSERT INTO patients (full_name, email, phone, nic, dob, gender, address, password)
                           VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([
        $full_name,
        $email,
        $phone,
        $nic,
        $dob,
        $gender,
        $address,
        $hashed_password
    ]);

    // 6. Get inserted ID
    $patient_id = $pdo->lastInsertId();

    // 7. Generate QR code
    $qr_content = "PMSID:$patient_id"; // you can include email or name here if needed
    $qr_path = "assets/qr/patient_$patient_id.png";
    QRcode::png($qr_content, $qr_path, QR_ECLEVEL_H, 4);

    // 8. Save QR path to DB
    $stmt = $pdo->prepare("UPDATE patients SET qr_code = ? WHERE id = ?");
    $stmt->execute([$qr_path, $patient_id]);

    // 9. Redirect to success page with download/print
    header("Location: registration_success.php?id=$patient_id");
    exit();
}
?>
