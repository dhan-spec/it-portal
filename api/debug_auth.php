<?php
require_once 'config.php';
cors();

$headers = function_exists('apache_request_headers') ? apache_request_headers() : [];
$server_auth = isset($_SERVER['HTTP_AUTHORIZATION']) ? $_SERVER['HTTP_AUTHORIZATION'] : (isset($_SERVER['REDIRECT_HTTP_AUTHORIZATION']) ? $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] : 'NOT_FOUND');

echo json_encode([
    "apache_headers" => $headers,
    "server_http_auth" => $server_auth,
    "static_token" => STATIC_TOKEN,
    "match" => (str_replace('Bearer ', '', $server_auth) === STATIC_TOKEN) ? "YES" : "NO"
]);
?>
