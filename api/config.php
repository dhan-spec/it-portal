<?php
function getEnvVars() {
    $search_paths = [__DIR__ . '/../.env', __DIR__ . '/../../.env'];
    $env_file = false;
    foreach ($search_paths as $path) { if (file_exists($path)) { $env_file = $path; break; } }
    if (!$env_file) return [];
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
define('DB_HOST', $env['DB_HOST'] ?? 'localhost');
define('DB_USER', $env['DB_USER'] ?? '');
define('DB_PASS', $env['DB_PASSWORD'] ?? '');
define('DB_NAME', $env['DB_NAME'] ?? '');
define('ADMIN_PASSWORD', $env['ADMIN_PASSWORD'] ?? 'admin');
define('JWT_SECRET', $env['JWT_SECRET'] ?? 'secret');
define('STATIC_TOKEN', hash('sha256', ADMIN_PASSWORD . JWT_SECRET));

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
try {
    $db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    $db->set_charset("utf8mb4");
    $db->query("CREATE TABLE IF NOT EXISTS reported_problems (id INT AUTO_INCREMENT PRIMARY KEY, asset_tag VARCHAR(50) NOT NULL, description TEXT NOT NULL, urgency ENUM('Low', 'Medium', 'High') DEFAULT 'Medium', status ENUM('Pending', 'In Progress', 'Resolved') DEFAULT 'Pending', reported_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP)");
    $db->query("CREATE TABLE IF NOT EXISTS maintenance_logs (id INT AUTO_INCREMENT PRIMARY KEY, asset_tag VARCHAR(50) NOT NULL, date DATE NOT NULL, description TEXT NOT NULL, technician VARCHAR(100) NOT NULL, photo_path VARCHAR(255) DEFAULT NULL, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP)");
} catch (Exception $e) {}

function cors() {
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
    header("Access-Control-Allow-Headers: Content-Type, Authorization");
    header("Content-Type: application/json");
    if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') exit(0);
}

function auth_guard() {
    $auth = '';
    if (function_exists('apache_request_headers')) {
        $h = apache_request_headers();
        $auth = $h['Authorization'] ?? ($h['authorization'] ?? '');
    }
    if (empty($auth)) $auth = $_SERVER['HTTP_AUTHORIZATION'] ?? ($_SERVER['REDIRECT_HTTP_AUTHORIZATION'] ?? '');
    $token = str_replace('Bearer ', '', $auth);
    if (trim($token) !== STATIC_TOKEN) {
        http_response_code(401);
        echo json_encode(["error" => "Unauthorized"]);
        exit;
    }
}
?>
