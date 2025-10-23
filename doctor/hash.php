<?php
require_once __DIR__ . '/error_handler.php';
$password = password_hash("secret123", PASSWORD_DEFAULT);
echo $password;
?>
