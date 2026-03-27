<?php
require_once 'config.php';
cors();
auth_guard();
$db = $GLOBALS['db'] ?? new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
$result = $db->query("SELECT * FROM assets ORDER BY id DESC");
$assets = [];
while ($row = $result->fetch_assoc()) $assets[] = $row;
echo json_encode($assets);
?>
