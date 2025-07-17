<?php
require 'db.php';
require_once 'assets/qr/phpqrcode.php'; // Ensure GD extension is enabled

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // 1. Collect & sanitize inputs
    $full_name = trim($_POST["full_name"]);
    $email     = trim($_POST["email"]);
    $phone     = trim($_POST["phone"]);
    $nic       = trim($_POST["nic"]);
    $dob       = $_POST["dob"];
    $gender    = $_POST["gender"];
    $address   = trim($_POST["address"]);
    $password  = $_POST["password"];
    $confirm   = $_POST["confirm_password"];

    // 2. Validate required fields
    if (!$full_name || !$email || !$phone || !$dob || !$gender || !$password || !$confirm) {
        die("All required fields must be filled.");
    }
    if ($password !== $confirm) {
        die("Passwords do not match.");
    }

    // 3. Check if email already exists
    $stmt = $pdo->prepare("SELECT id FROM patients WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        die("Email already registered.");
    }

    // 4. Hash password
    $hashed = password_hash($password, PASSWORD_DEFAULT);

    // 5. Insert patient (let MySQL assign auto-increment id)
    $stmt = $pdo->prepare("
        INSERT INTO patients 
          (full_name, email, phone, nic, dob, gender, address, password) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->execute([
        $full_name,
        $email,
        $phone,
        $nic,
        $dob,
        $gender,
        $address,
        $hashed
    ]);

    // 6. Get the new numeric ID and generate patient_id (e.g. P000001)
    $newId        = $pdo->lastInsertId();
    $patient_code = 'P' . str_pad($newId, 6, '0', STR_PAD_LEFT);

    // 7. Store patient_id in the patient row
    $stmt = $pdo->prepare("UPDATE patients SET patient_id = ? WHERE id = ?");
    $stmt->execute([$patient_code, $newId]);

    // 8. Generate QR code data with patient_id and other info
    $qr_data = json_encode([
        'patient_id' => $patient_code,
        'name'       => $full_name,
        'dob'        => $dob,
        'email'      => $email,
        'phone'      => $phone,
        'nic'        => $nic,
        'gender'     => $gender,
        'address'    => $address
    ]);
    $qr_path = "assets/qr/{$patient_code}.png";
    QRcode::png($qr_data, $qr_path, QR_ECLEVEL_H, 4);

    // 9. Overlay "Patient ID: XXXXXX" text under the QR code image
    $qr_img = imagecreatefrompng($qr_path);
    $width  = imagesx($qr_img);
    $height = imagesy($qr_img);

    // Extra height for text
    $text_height = 30;
    $final_img = imagecreatetruecolor($width, $height + $text_height);

    // Fill background white
    $white = imagecolorallocate($final_img, 255, 255, 255);
    imagefilledrectangle($final_img, 0, 0, $width, $height + $text_height, $white);

    // Copy QR on top
    imagecopy($final_img, $qr_img, 0, 0, 0, 0, $width, $height);

    // Add "Patient ID: P000001" text (centered)
    $black = imagecolorallocate($final_img, 0, 0, 0);
    $font_size = 5; // built-in font
    $text = "Patient ID: " . $patient_code;
    $text_width = imagefontwidth($font_size) * strlen($text);
    $x = ($width - $text_width) / 2;
    $y = $height + 7;
    imagestring($final_img, $font_size, $x, $y, $text, $black);

    // Save new QR code image with text
    imagepng($final_img, $qr_path);
    imagedestroy($qr_img);
    imagedestroy($final_img);

    // 10. Save QR path (optional—already set above, but ensures QR is linked)
    $stmt = $pdo->prepare("UPDATE patients SET qr_code = ? WHERE id = ?");
    $stmt->execute([$qr_path, $newId]);

    // 11. Redirect to success page with patient_id in URL
    header("Location: registration_success.php?pid={$patient_code}");
    exit();
}
?>
