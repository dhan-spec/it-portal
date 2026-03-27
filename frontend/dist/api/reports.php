<?php
require_once 'config.php';

cors();

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

auth_guard(); // Admin only
$db = $GLOBALS['db'] ?? null;
if (!$db) $db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // List reports
    $status = isset($_GET['status']) ? $_GET['status'] : 'Pending';
    $stmt = $db->prepare("SELECT * FROM reported_problems WHERE status = ? ORDER BY reported_at DESC");
    $stmt->bind_param("s", $status);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $reports = [];
    while ($row = $result->fetch_assoc()) {
        $reports[] = $row;
    }
    echo json_encode($reports);

} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents("php://input"));
    $id = isset($data->id) ? $data->id : 0;
    $new_status = isset($data->status) ? $data->status : '';
    
    if (!$id || !$new_status) {
        http_response_code(400);
        echo json_encode(["error" => "ID and status are required"]);
        exit;
    }

    try {
        // Start transaction
        $db->begin_transaction();

        // 1. Update status
        $stmt = $db->prepare("UPDATE reported_problems SET status = ? WHERE id = ?");
        $stmt->bind_param("si", $new_status, $id);
        $stmt->execute();

        // 2. If Resolved, move to maintenance_logs
        if ($new_status === 'Resolved') {
            $rep_stmt = $db->prepare("SELECT * FROM reported_problems WHERE id = ?");
            $rep_stmt->bind_param("i", $id);
            $rep_stmt->execute();
            $report = $rep_stmt->get_result()->fetch_assoc();

            if ($report) {
                // Determine technician (from token or default)
                $tech = "IT Admin (Auto)";
                $desc = "RESOLVED REPORT: " . $report['description'];
                $log_date = date('Y-m-d');

                $log_stmt = $db->prepare("INSERT INTO maintenance_logs (asset_tag, date, description, technician) VALUES (?, ?, ?, ?)");
                $log_stmt->bind_param("ssss", $report['asset_tag'], $log_date, $desc, $tech);
                $log_stmt->execute();
            }
        }

        $db->commit();
        echo json_encode(["message" => "Report updated and logged successfully"]);
    } catch (Exception $e) {
        $db->rollback();
        http_response_code(500);
        echo json_encode(["error" => "Transaction failed", "details" => $e->getMessage()]);
    }
}

$db->close();
?>
