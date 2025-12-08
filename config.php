<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'pipe_mill_analysis');

// Application settings
define('APP_NAME', 'Программная система анализа и прогнозирования рентабельности инвестиций трубопрокатного завода');
define('UPLOAD_DIR', __DIR__ . '/uploads/');
define('REPORT_DIR', __DIR__ . '/reports/');

// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start session
session_start();
?>