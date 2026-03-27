<?php
require_once 'config.php';
cors();
auth_guard();
$db = $GLOBALS['db'] ?? new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
$query = "SELECT l.id, l.asset_tag, a.name as asset_name, l.date as resolution_date, l.description as accomplishment, l.technician FROM maintenance_logs l JOIN assets a ON l.asset_tag = a.asset_tag ORDER BY l.date DESC";
$result = $db->query($query);
$logs = [];
while ($row = $result->fetch_assoc()) $logs[] = $row;
echo json_encode($logs);
?>
