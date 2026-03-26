<?php
function getEnvVars() {
    $search_paths = [
        __DIR__ . '/../backend/.env',           // Inside backend
        __DIR__ . '/../.env',                   // Inside it folder
        __DIR__ . '/../../.env',                // Inside public_html
        __DIR__ . '/../../../.env'              // Inside domains root (Safest)
    ];

    $env_file = false;
    foreach ($search_paths as $path) {
        if (file_exists($path)) {
            $env_file = $path;
            break;
        }
    }

    if (!$env_file) {
        http_response_code(500);
        die(json_encode(["error" => "Critical Error: Hostinger Git Deployment deleted your .env file! Please recreate .env inside your public_html folder to prevent it from being deleted again."]));
    }
    
    $lines = file($env_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $vars = [];
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        list($name, $value) = explode('=', $line, 2);
        $vars[trim($name)] = trim($value);
    }
    return $vars;
}

$env = getEnvVars();

// Assign credentials dynamically from .env
define('DB_HOST', isset($env['DB_HOST']) ? $env['DB_HOST'] : 'localhost');
define('DB_USER', isset($env['DB_USER']) ? $env['DB_USER'] : '');
define('DB_PASS', isset($env['DB_PASSWORD']) ? $env['DB_PASSWORD'] : '');
define('DB_NAME', isset($env['DB_NAME']) ? $env['DB_NAME'] : '');
define('ADMIN_PASSWORD', isset($env['ADMIN_PASSWORD']) ? $env['ADMIN_PASSWORD'] : 'admin');
define('JWT_SECRET', isset($env['JWT_SECRET']) ? $env['JWT_SECRET'] : 'secret123');

// Create a static deterministic token
define('STATIC_TOKEN', hash('sha256', ADMIN_PASSWORD . JWT_SECRET));

// Database connection
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
try {
    $db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    $db->set_charset("utf8mb4");
} catch (Exception $e) {
    http_response_code(500);
    die(json_encode(["error" => "DB Error: " . $e->getMessage() . " (Host: " . DB_HOST . ", User: " . DB_USER . ")"]));
}

// Helper Functions
function cors() {
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
    header("Access-Control-Allow-Headers: Content-Type, Authorization");
    header("Content-Type: application/json");
    if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
        exit(0);
    }
}

function auth_guard() {
    $auth = '';
    if (function_exists('apache_request_headers')) {
        $headers = apache_request_headers();
        if (isset($headers['Authorization'])) {
            $auth = $headers['Authorization'];
        }
    }
    if (empty($auth) && isset($_SERVER['HTTP_AUTHORIZATION'])) {
        $auth = $_SERVER['HTTP_AUTHORIZATION'];
    }
    
    $token = str_replace('Bearer ', '', $auth);
    if (trim($token) !== STATIC_TOKEN) {
        http_response_code(401);
        echo json_encode(["error" => "Unauthorized access"]);
        exit;
    }
}
?>
