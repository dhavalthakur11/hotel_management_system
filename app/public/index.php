<?php
// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set Oracle environment
if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
    putenv("ORACLE_HOME=C:\\oracle\\product\\21c\\dbhomeXE");
}

// Get base path
$base_path = dirname(dirname(__FILE__));

// Load configuration files
require_once $base_path . '/config/constants.php';
require_once $base_path . '/config/database.php';
require_once $base_path . '/config/routing.php';

// Load all models
$model_files = glob($base_path . '/models/*.php');
if ($model_files) {
    foreach ($model_files as $file) {
        require_once $file;
    }
}

// Load all controllers
$controller_files = glob($base_path . '/controllers/*.php');
if ($controller_files) {
    foreach ($controller_files as $file) {
        require_once $file;
    }
}

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Route the request
try {
    $router = new Router();
    $router->route();
} catch (Exception $e) {
    http_response_code(500);
    echo "Error: " . $e->getMessage();
    exit;
}
?>