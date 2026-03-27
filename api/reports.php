<?php
require_once 'config.php';
cors();
auth_guard();
$db = $GLOBALS['db'] ?? new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $status = $_GET['status'] ?? 'Pending';
    $stmt = $db->prepare("SELECT * FROM reported_problems WHERE status = ? ORDER BY reported_at DESC");
    $stmt->bind_param("s", $status);
    $stmt->execute();
    $result = $stmt->get_result();
    $reports = [];
    while ($row = $result->fetch_assoc()) $reports[] = $row;
    echo json_encode($reports);
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents("php://input"));
    $id = $data->id ?? 0;
    $new_status = $data->status ?? '';
    if ($id && $new_status) {
        $db->begin_transaction();
        try {
            $stmt = $db->prepare("UPDATE reported_problems SET status = ? WHERE id = ?");
            $stmt->bind_param("si", $new_status, $id);
            $stmt->execute();
            if ($new_status === 'Resolved') {
                $rep_stmt = $db->prepare("SELECT * FROM reported_problems WHERE id = ?");
                $rep_stmt->bind_param("i", $id);
                $rep_stmt->execute();
                $report = $rep_stmt->get_result()->fetch_assoc();
                if ($report) {
                    $tech = "IT Admin"; $desc = "RESOLVED: " . $report['description']; $log_date = date('Y-m-d');
                    $l_stmt = $db->prepare("INSERT INTO maintenance_logs (asset_tag, date, description, technician) VALUES (?, ?, ?, ?)");
                    $l_stmt->bind_param("ssss", $report['asset_tag'], $log_date, $desc, $tech);
                    $l_stmt->execute();
                }
            }
            $db->commit();
            echo json_encode(["message" => "OK"]);
        } catch (Exception $e) { $db->rollback(); }
    }
}
?>
