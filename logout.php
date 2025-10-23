<?php
session_start();
require_once __DIR__ . '/error_handler.php';
// Figure out where to redirect before destroying the session
$redirect = "patient_login.html"; // default

if (isset($_SESSION['role'])) {
    switch ($_SESSION['role']) {
        case 'doctor':
            $redirect = "doctor_login.php";
            break;
        case 'receptionist':
        case 'staff':
            $redirect = "staff_login.html";
            break;
        case 'admin':
            $redirect = "admin_login.php";
            break;
        case 'patient':
            $redirect = "patient_login.html";
            break;
    }
}

// Clear session
session_unset();
session_destroy();

// Redirect to the chosen login page
header("Location: $redirect");
exit;
