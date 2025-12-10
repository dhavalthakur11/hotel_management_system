<?php
// Entry point for the application

// Start session
session_start();

// Error reporting (disable in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Load configuration files
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/routing.php';

// Initialize router
$router = new Router();

// Route the request
$router->route();
?>