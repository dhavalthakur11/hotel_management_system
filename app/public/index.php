<?php
// Load configuration files
require_once '../config/database.php';
require_once '../config/constants.php';
require_once '../config/routing.php';

// Load all models
$model_dir = '../models/';
foreach (glob($model_dir . '*.php') as $file) {
    require_once $file;
}

// Load all controllers
$controller_dir = '../controllers/';
foreach (glob($controller_dir . '*.php') as $file) {
    require_once $file;
}

// Start routing
$router = new Router();
$router->route();
?>