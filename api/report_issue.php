<?php
require_once 'config.php';
cors();
$db = $GLOBALS['db'] ?? new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents("php://input"));
    $tag = $data->asset_tag ?? '';
    $desc = $data->description ?? '';
    $urgency = $data->urgency ?? 'Medium';
    if ($tag && $desc) {
        $stmt = $db->prepare("INSERT INTO reported_problems (asset_tag, description, urgency, status) VALUES (?, ?, ?, 'Pending')");
        $stmt->bind_param("sss", $tag, $desc, $urgency);
        if ($stmt->execute()) echo json_encode(["message" => "OK"]);
    }
}
?>
