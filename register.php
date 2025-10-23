<?php
// register.php — validation + patient creation + QR
// -------------------------------------------------
require 'db.php';
require_once 'assets/qr/phpqrcode.php'; // needs GD extension

function b64url_encode_json(array $arr): string {
    $json = json_encode($arr, JSON_UNESCAPED_SLASHES);
    $b64  = base64_encode($json);
    return rtrim(strtr($b64, '+/', '-_'), '=');
}

function redirect_with_errors(array $errors, array $old): void {
    $err_b64 = b64url_encode_json($errors);
    $old_b64 = b64url_encode_json($old);
    header("Location: register.html?errors={$err_b64}&old={$old_b64}");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
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

    $old = [
        'full_name' => $full_name,
        'email'     => $email,
        'phone'     => $phone,
        'nic'       => $nic,
        'dob'       => $dob,
        'gender'    => $gender,
        'address'   => $address
    ];

    // Validate inputs
    $errors = [];
    if ($full_name === '')                               $errors['full_name'] = "Full name is required.";
    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors['email'] = "Valid email is required.";
    if ($phone === '')                                   $errors['phone'] = "Phone number is required.";
    if ($dob === '')                                     $errors['dob'] = "Date of birth is required.";
    if (!in_array($gender, ['Male','Female','Other'], true)) $errors['gender'] = "Please select a gender.";
    if ($password === '' || strlen($password) < 8)       $errors['password'] = "Password must be at least 8 characters.";
    if ($confirm === '' || $password !== $confirm)       $errors['confirm_password'] = "Passwords do not match.";
    if (!$terms)                                         $errors['terms'] = "You must agree to continue.";

    // Email uniqueness check
    if (!isset($errors['email'])) {
        $stmt = $pdo->prepare("SELECT id FROM patients WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) $errors['email'] = "This email is already registered.";
    }

    // Redirect back if errors found
    if (!empty($errors)) redirect_with_errors($errors, $old);

    try {
        // Hash password
        $hashed = password_hash($password, PASSWORD_DEFAULT);

        // Insert patient record
        $stmt = $pdo->prepare("
            INSERT INTO patients (full_name, email, phone, nic, dob, gender, address, password)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([$full_name, $email, $phone, $nic, $dob, $gender, $address, $hashed]);

        // Generate patient code
        $newId = $pdo->lastInsertId();
        $patient_code = 'P' . str_pad((string)$newId, 6, '0', STR_PAD_LEFT);

        // Update patient_id
        $pdo->prepare("UPDATE patients SET patient_id = ? WHERE id = ?")
            ->execute([$patient_code, $newId]);

        // Build QR data
        $qr_data = json_encode([
            'patient_id' => $patient_code,
            'name'       => $full_name,
            'dob'        => $dob,
            'email'      => $email,
            'phone'      => $phone,
            'nic'        => $nic,
            'gender'     => $gender,
            'address'    => $address
        ], JSON_UNESCAPED_SLASHES);

        // Prepare QR directory
        $qrDirFs  = __DIR__ . '/assets/qr';
        if (!is_dir($qrDirFs)) { @mkdir($qrDirFs, 0775, true); }
        if (!is_writable($qrDirFs)) {
            redirect_with_errors(['__global' => 'QR folder is not writable.'], $old);
        }

        // Generate QR code
        $qrRel = "assets/qr/{$patient_code}.png";
        $qrFs  = __DIR__ . "/{$qrRel}";
        QRcode::png($qr_data, $qrFs, QR_ECLEVEL_H, 4);

        // Add “Patient ID: ...” text below QR
        $qr_img = imagecreatefrompng($qrFs);
        if (!$qr_img) redirect_with_errors(['__global' => 'Failed to generate QR image.'], $old);

        $w = imagesx($qr_img);
        $h = imagesy($qr_img);
        $text_h = 30;
        $final  = imagecreatetruecolor($w, $h + $text_h);

        $white = imagecolorallocate($final, 255, 255, 255);
        imagefilledrectangle($final, 0, 0, $w, $h + $text_h, $white);
        imagecopy($final, $qr_img, 0, 0, 0, 0, $w, $h);

        $black = imagecolorallocate($final, 0, 0, 0);
        $font  = 5;
        $text  = "Patient ID: {$patient_code}";
        $x = (int)(($w - imagefontwidth($font) * strlen($text)) / 2);
        $y = $h + 7;
        imagestring($final, $font, $x, $y, $text, $black);

        imagepng($final, $qrFs);
        imagedestroy($qr_img);
        imagedestroy($final);

        // Save QR code path
        $pdo->prepare("UPDATE patients SET qr_code = ? WHERE id = ?")
            ->execute([$qrRel, $newId]);

        // Redirect to success page
        header("Location: registration_success.php?pid={$patient_code}");
        exit;

    } catch (Throwable $e) {
        // Optional: log for debugging
        // error_log($e->getMessage());
        redirect_with_errors(['__global' => 'An unexpected error occurred. Please try again.'], $old);
    }

} else {
    // If direct GET access, go back to form
    header("Location: register.html");
    exit;
}
?>
