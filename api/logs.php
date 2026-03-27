<?php
require_once 'config.php';

cors();

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// $db is already available from config.php

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    auth_guard();

    $data = json_decode(file_get_contents("php://input"));
    $asset_tag = isset($data->asset_tag) ? $data->asset_tag : '';
    $date = isset($data->date) ? $data->date : '';
    $description = isset($data->description) ? $data->description : '';
    $technician = isset($data->technician) ? $data->technician : '';

    if (!$asset_tag || !$date || !$description) {
        http_response_code(400);
        echo json_encode(["error" => "Asset tag, date, and description are required"]);
        exit;
    }

    $stmt = $db->prepare("INSERT INTO maintenance_logs (asset_tag, date, description, technician) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $asset_tag, $date, $description, $technician);
    
    if ($stmt->execute()) {
        http_response_code(201);
        echo json_encode(["message" => "Maintenance log added successfully", "id" => $db->insert_id]);
    } else {
        http_response_code(500);
        echo json_encode(["error" => "Database error: " . $stmt->error]);
    }
    
    $stmt->close();
} else {
    http_response_code(405);
    echo json_encode(["error" => "Method not allowed"]);
}

$db->close();
?>
