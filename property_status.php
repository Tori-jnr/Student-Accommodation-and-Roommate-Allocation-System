<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

// Calibrated to route commands straight through your active custom 3307 port
$host = "127.0.0.1"; $username = "root"; $password = ""; $dbname = "roomly_db"; $port = 3307;
$conn = new mysqli($host, $username, $password, $dbname, $port);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = $_POST['title'] ?? '';
    $actionType = $_POST['action'] ?? '';

    if (empty($title)) {
        echo json_encode(["status" => "error", "message" => "Target parameter blank."]);
        exit();
    }

    if ($actionType === 'update') {
        // Flips state rows back and forth between Vacant and Allocated live
        $stmt = $conn->prepare("UPDATE properties SET status = CASE WHEN status LIKE '%Vacant%' THEN 'Fully Allocated (1/1)' ELSE 'Vacant (0/1)' END, status_color = CASE WHEN status LIKE '%Vacant%' THEN 'var(--success)' ELSE 'var(--danger)' END WHERE title = ?");
    } else if ($actionType === 'deactivate') {
        // Sets state row to offline maintenance
        $stmt = $conn->prepare("UPDATE properties SET status = 'Offline Maintenance', status_color = 'var(--text-secondary)' WHERE title = ?");
    }
    
    $stmt->bind_param("s", $title);
    if ($stmt->execute()) {
        echo json_encode(["status" => "success"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Execution failed"]);
    }
}
?>
