<?php
require_once 'config.php';
cors();
$data = json_decode(file_get_contents("php://input"));
if (($data->password ?? '') === ADMIN_PASSWORD) echo json_encode(["token" => STATIC_TOKEN]);
else http_response_code(401);
?>
