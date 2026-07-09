<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

// 🚨 SYSTEM CONNECTOR OVERRIDE FOR PORT 3307
$host = "127.0.0.1";
$username = "root";
$password = "";      
$dbname = "roomly_db";
$port = 3307; // 🚨 FIXED: Tuned explicitly to match your custom XAMPP port configuration!

$conn = new mysqli($host, $username, $password, $dbname, $port);

if ($conn->connect_error) {
    echo json_encode(["status" => "error", "message" => "Pipeline Port Break: " . $conn->connect_error]);
    exit();
}

$landlordId = isset($_GET['landlordId']) && trim($_GET['landlordId']) !== "" ? $_GET['landlordId'] : 'L1';

// 1. Fetch Landlord Username securely via MySQL queries parameters
$nameStmt = $conn->prepare("SELECT name FROM landlords WHERE landlord_id = ?");
$nameStmt->bind_param("s", $landlordId);
$nameStmt->execute();
$nameResult = $nameStmt->get_result();
$row = $nameResult->fetch_assoc();
$landlordName = $row ? $row['name'] : "Property Manager";

// 2. Fetch Accommodations Inventory List Matrix
$stmt = $conn->prepare("SELECT * FROM properties WHERE landlord_id = ?");
$stmt->bind_param("s", $landlordId);
$stmt->execute();
$results = $stmt->get_result();

$properties = [];
while ($row = $results->fetch_assoc()) {
    $properties[] = [
        "title" => $row['title'],
        "layout" => $row['layout'],
        "price" => intval($row['price']),
        "status" => $row['status'],
        "statusColor" => $row['status_color'],
        "studentName" => $row['student_name'],
        "studentNotes" => $row['student_notes'],
        "imagesRecord" => $row['hostel_images'],
        "tourRecord" => $row['virtual_tour_file']
    ];
}

echo json_encode([
    "landlordName" => $landlordName,
    "properties" => $properties
]);
?>
