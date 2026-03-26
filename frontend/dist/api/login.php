<?php
require 'config.php';
cors();

$data = json_decode(file_get_contents("php://input"));
$password = isset($data->password) ? $data->password : '';

if ($password === ADMIN_PASSWORD) {
    echo json_encode(["token" => STATIC_TOKEN]);
} else {
    http_response_code(401);
    echo json_encode(["error" => "Invalid master password"]);
}
?>
