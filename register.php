<?php
require 'db.php';
require_once 'assets/qr/phpqrcode.php'; // Ensure GD extension is enabled

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Sanitize inputs
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

    // 5. Insert patient into database
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

    // 6. Get inserted patient ID
    $patient_id = $pdo->lastInsertId();

    // 7. Create JSON for QR code (excluding medical history)
    $qr_data = [
        'id' => $patient_id,
        'name' => $full_name,
        'dob' => $dob,
        'email' => $email,
        'phone' => $phone,
        'nic' => $nic,
        'gender' => $gender,
        'address' => $address
    ];
    $qr_content = json_encode($qr_data);

    // 8. Generate QR code image
    $qr_path = "assets/qr/patient_$patient_id.png";
    QRcode::png($qr_content, $qr_path, QR_ECLEVEL_H, 4);

    // 9. Save QR path in the database
    $stmt = $pdo->prepare("UPDATE patients SET qr_code = ? WHERE id = ?");
    $stmt->execute([$qr_path, $patient_id]);

    // 10. Redirect to success page
    header("Location: registration_success.php?id=$patient_id");
    exit();
}
?>
