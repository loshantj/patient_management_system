<?php
require_once __DIR__ . '/error_handler.php';
require 'db.php';
require_once 'assets/qr/phpqrcode.php'; // QR code library

header('Content-Type: application/json'); // Return JSON responses

// Global error handler (for unexpected PHP errors)
set_exception_handler(function ($e) {
    error_log("[ERROR] " . $e->getMessage() . "\n" . $e->getTraceAsString(), 3, __DIR__ . '/error_log.txt');
    echo json_encode(['status' => 'error', 'errors' => ['__global' => 'Internal server error. Please try again later.']]);
    exit;
});

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo json_encode(['status' => 'error', 'errors' => ['__global' => 'Invalid request method.']]);
    exit;
}

// Collect & sanitize input
$full_name = trim($_POST["full_name"] ?? '');
$email     = trim($_POST["email"] ?? '');
$phone     = trim($_POST["phone"] ?? '');
$nic       = trim($_POST["nic"] ?? '');
$dob       = $_POST["dob"] ?? '';
$gender    = $_POST["gender"] ?? '';
$address   = trim($_POST["address"] ?? '');
$password  = $_POST["password"] ?? '';
$confirm   = $_POST["confirm_password"] ?? '';
$terms     = isset($_POST["terms"]);

$errors = [];

// --- VALIDATION ---
if ($full_name === '') $errors['full_name'] = "Full name is required.";
if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors['email'] = "Valid email is required.";
if ($phone === '') $errors['phone'] = "Phone number is required.";
if ($dob === '') $errors['dob'] = "Date of birth is required.";
if (!in_array($gender, ['Male', 'Female', 'Other'], true)) $errors['gender'] = "Please select a gender.";
if ($password === '' || strlen($password) < 8) $errors['password'] = "Password must be at least 8 characters.";
if ($confirm === '' || $password !== $confirm) $errors['confirm_password'] = "Passwords do not match.";
if (!$terms) $errors['terms'] = "You must agree to continue.";

// --- CHECK EMAIL UNIQUE ---
if (!isset($errors['email'])) {
    $stmt = $pdo->prepare("SELECT id FROM patients WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) $errors['email'] = "This email is already registered.";
}

// Return validation errors immediately
if (!empty($errors)) {
    echo json_encode(['status' => 'error', 'errors' => $errors]);
    exit;
}

try {
    // --- Create new patient ---
    $hashed = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("
        INSERT INTO patients (full_name, email, phone, nic, dob, gender, address, password)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->execute([$full_name, $email, $phone, $nic, $dob, $gender, $address, $hashed]);

    $newId = $pdo->lastInsertId();
    $patient_code = 'P' . str_pad((string)$newId, 6, '0', STR_PAD_LEFT);

    // Update patient_id
    $pdo->prepare("UPDATE patients SET patient_id = ? WHERE id = ?")
        ->execute([$patient_code, $newId]);

    // --- Generate QR Code ---
    $qrDir = __DIR__ . '/assets/qr';
    if (!is_dir($qrDir)) {
        mkdir($qrDir, 0775, true);
    }

    $qrRel = "assets/qr/{$patient_code}.png";
    $qrFile = __DIR__ . "/{$qrRel}";

    $qr_data = json_encode([
        'patient_id' => $patient_code,
        'name' => $full_name,
        'dob' => $dob,
        'email' => $email,
        'phone' => $phone,
        'nic' => $nic,
        'gender' => $gender,
        'address' => $address
    ], JSON_UNESCAPED_SLASHES);

    QRcode::png($qr_data, $qrFile, QR_ECLEVEL_H, 4);

    // Add "Patient ID" text below the QR image
    $qr_img = imagecreatefrompng($qrFile);
    $w = imagesx($qr_img);
    $h = imagesy($qr_img);
    $text_h = 30;
    $final_img = imagecreatetruecolor($w, $h + $text_h);

    $white = imagecolorallocate($final_img, 255, 255, 255);
    imagefilledrectangle($final_img, 0, 0, $w, $h + $text_h, $white);
    imagecopy($final_img, $qr_img, 0, 0, 0, 0, $w, $h);

    $black = imagecolorallocate($final_img, 0, 0, 0);
    $font = 5;
    $text = "Patient ID: {$patient_code}";
    $x = ($w - imagefontwidth($font) * strlen($text)) / 2;
    $y = $h + 7;
    imagestring($final_img, $font, $x, $y, $text, $black);

    imagepng($final_img, $qrFile);
    imagedestroy($qr_img);
    imagedestroy($final_img);

    // Save QR path
    $pdo->prepare("UPDATE patients SET qr_code = ? WHERE id = ?")
        ->execute([$qrRel, $newId]);

    // ✅ Return success
    echo json_encode(['status' => 'success', 'patient_id' => $patient_code]);
    exit;

} catch (Throwable $e) {
    error_log("[REGISTER ERROR] " . $e->getMessage() . "\n", 3, __DIR__ . '/error_log.txt');
    echo json_encode(['status' => 'error', 'errors' => ['__global' => 'An unexpected error occurred. Please try again.']]);
    exit;
}
