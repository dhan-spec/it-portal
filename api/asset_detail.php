<?php
require 'config.php';
cors();

$tag = isset($_GET['tag']) ? $_GET['tag'] : '';

if (!$tag) {
    http_response_code(400);
    echo json_encode(["error" => "No tag provided"]);
    exit;
}

$stmt = $db->prepare("SELECT * FROM assets WHERE asset_tag = ? LIMIT 1");
$stmt->bind_param("s", $tag);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    echo json_encode($row);
} else {
    http_response_code(404);
    echo json_encode(["error" => "Asset not found"]);
}
?>
