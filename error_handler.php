<?php
/**
 * error_handler.php — Global error & exception logging
 * Logs all PHP warnings, notices, and uncaught exceptions
 * into /logs/error_log_[date].log
 */

date_default_timezone_set('Asia/Colombo'); // adjust if needed

$logDir = __DIR__ . '/logs';
if (!is_dir($logDir)) {
    mkdir($logDir, 0775, true);
}

// Function to log errors to a daily file
function logErrorToFile($message) {
    $logFile = __DIR__ . '/logs/error_log_' . date('Y-m-d') . '.log';
    $entry = "[" . date("Y-m-d H:i:s") . "] " . $message . PHP_EOL;
    file_put_contents($logFile, $entry, FILE_APPEND);
}

// Handle all PHP errors (warnings, notices, etc.)
set_error_handler(function($errno, $errstr, $errfile, $errline) {
    $message = "PHP Error [$errno]: $errstr in $errfile on line $errline";
    logErrorToFile($message);
    return false; // continue with PHP’s internal handler if needed
});

// Handle uncaught exceptions
set_exception_handler(function($exception) {
    $message = "Uncaught Exception: " . $exception->getMessage() .
               " in " . $exception->getFile() . " on line " . $exception->getLine();
    logErrorToFile($message);
    http_response_code(500);
    echo "⚠️ Sorry, something went wrong. Please try again later.";
});

// Handle fatal errors on shutdown
register_shutdown_function(function() {
    $error = error_get_last();
    if ($error !== null && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        $message = "Fatal Error: {$error['message']} in {$error['file']} on line {$error['line']}";
        logErrorToFile($message);
    }
});
?>
