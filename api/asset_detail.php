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
    // Fetch associated maintenance logs
    $log_stmt = $db->prepare("SELECT * FROM maintenance_logs WHERE asset_tag = ? ORDER BY date DESC, created_at DESC");
    $log_stmt->bind_param("s", $tag);
    $log_stmt->execute();
    $log_result = $log_stmt->get_result();
    
    $logs = [];
    while ($log_row = $log_result->fetch_assoc()) {
        $logs[] = $log_row;
    }
    $log_stmt->close();
    
    $row['maintenance_logs'] = $logs;

    echo json_encode($row);
} else {
    http_response_code(404);
    echo json_encode(["error" => "Asset not found"]);
}
?>
