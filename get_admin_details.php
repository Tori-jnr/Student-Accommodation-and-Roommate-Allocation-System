<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

// Direct pipeline handshake straight through your custom port 3307
$host = "127.0.0.1"; $username = "root"; $password = ""; $dbname = "roomly_db"; $port = 3307;
$conn = new mysqli($host, $username, $password, $dbname, $port);

if ($conn->connect_error) {
    echo json_encode(["status" => "error", "message" => "Port 3307 lock error."]);
    exit();
}

//  Aligned strictly to look at hostels and properties tables
$pending_count  = $conn->query("SELECT COUNT(*) as total FROM hostels WHERE verified = 0")->fetch_assoc()['total'];
$verified_count = $conn->query("SELECT COUNT(*) as total FROM hostels WHERE verified = 1")->fetch_assoc()['total'];
$student_count  = $conn->query("SELECT COUNT(*) as total FROM students")->fetch_assoc()['total'];
$landlord_count = $conn->query("SELECT COUNT(*) as total FROM landlords")->fetch_assoc()['total'];

// Fetch tracking telemetry from activity logs table
$activity_result = $conn->query("SELECT * FROM activity_log ORDER BY activity_time DESC LIMIT 3");
$activities = [];
if ($activity_result) {
    while ($row = $activity_result->fetch_assoc()) {
        $activities[] = [
            "title" => $row['activity_title'],
            "desc"  => $row['activity_description']
        ];
    }
}

echo json_encode([
    "status"   => "success",
    "pending"  => intval($pending_count),
    "verified" => intval($verified_count),
    "students" => intval($student_count),
    "landlords"=> intval($landlord_count),
    "logs"     => $activities
]);
?>
