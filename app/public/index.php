<?php
// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set Oracle environment (Windows specific)
if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
    putenv("ORACLE_HOME=C:\\oracle\\product\\21c\\dbhomeXE");
}

// Get base path for requires
$base_path = dirname(dirname(__FILE__));

// Load configuration files
require_once $base_path . '/config/constants.php';
require_once $base_path . '/config/database.php';
require_once $base_path . '/config/routing.php';

// Load all models
$model_dir = $base_path . '/models/';
if (is_dir($model_dir)) {
    $files = glob($model_dir . '*.php');
    if ($files && is_array($files)) {
        foreach ($files as $file) {
            if (file_exists($file)) {
                require_once $file;
            }
        }
    }
}

// Load all controllers
$controller_dir = $base_path . '/controllers/';
if (is_dir($controller_dir)) {
    $files = glob($controller_dir . '*.php');
    if ($files && is_array($files)) {
        foreach ($files as $file) {
            if (file_exists($file)) {
                require_once $file;
            }
        }
    }
}

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Debug: Check if router is working
// echo "REQUEST_URI: " . $_SERVER['REQUEST_URI'] . "<br>";
// echo "SCRIPT_NAME: " . $_SERVER['SCRIPT_NAME'] . "<br>";

// Start routing
try {
    $router = new Router();
    $router->route();
} catch (Exception $e) {
    http_response_code(500);
    die("Error: " . $e->getMessage());
}
?>