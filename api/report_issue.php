<?php
require_once 'config.php';

cors();

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

$db = $GLOBALS['db'] ?? null;
if (!$db) {
    $db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents("php://input"));
    $asset_tag = isset($data->asset_tag) ? $data->asset_tag : '';
    $description = isset($data->description) ? $data->description : '';
    $urgency = isset($data->urgency) ? $data->urgency : 'Medium';

    if (!$asset_tag || !$description) {
        http_response_code(400);
        echo json_encode(["error" => "Asset tag and description are required"]);
        exit;
    }

    try {
        $stmt = $db->prepare("INSERT INTO reported_problems (asset_tag, description, urgency, status) VALUES (?, ?, ?, 'Pending')");
        $stmt->bind_param("sss", $asset_tag, $description, $urgency);
        
        if ($stmt->execute()) {
            http_response_code(201);
            echo json_encode(["message" => "Problem reported successfully", "id" => $db->insert_id]);
        } else {
            http_response_code(500);
            echo json_encode(["error" => "Database error: " . $db->error]);
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(["error" => "Internal Server Error", "details" => $e->getMessage()]);
    }
} else {
    http_response_code(405);
    echo json_encode(["error" => "Method not allowed"]);
}

$db->close();
?>
