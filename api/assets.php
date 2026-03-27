<?php
require 'config.php';
cors();

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    auth_guard();
    
    $result = $db->query("SELECT * FROM assets ORDER BY created_at DESC");
    $assets = [];
    while ($row = $result->fetch_assoc()) {
        $assets[] = $row;
    }
    echo json_encode($assets);
} 
elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    auth_guard();
    
    $data = json_decode(file_get_contents("php://input"));
    $name = isset($data->name) ? $data->name : '';
    $asset_tag = isset($data->asset_tag) ? $data->asset_tag : '';
    $type = isset($data->type) ? $data->type : '';
    $location = isset($data->location) ? $data->location : '';
    $ip_address = isset($data->ip_address) ? $data->ip_address : '';
    $status = isset($data->status) ? $data->status : 'Online';
    $photo = isset($data->photo) ? $data->photo : null;

    if (!$name || !$asset_tag || !$type) {
        http_response_code(400);
        echo json_encode(["error" => "Name, Asset Tag, and Type are required"]);
        exit;
    }

    $stmt = $db->prepare("INSERT INTO assets (name, asset_tag, type, location, ip_address, status, photo) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssss", $name, $asset_tag, $type, $location, $ip_address, $status, $photo);
    if ($stmt->execute()) {
        http_response_code(201);
        echo json_encode(["message" => "Asset added successfully", "id" => $db->insert_id]);
    } else {
        if ($db->errno === 1062) {
            http_response_code(400);
            echo json_encode(["error" => "Asset tag already exists"]);
        } else {
            http_response_code(500);
            echo json_encode(["error" => "Database error"]);
        }
    }
} else {
    http_response_code(405);
    echo json_encode(["error" => "Method not allowed"]);
}
?>
