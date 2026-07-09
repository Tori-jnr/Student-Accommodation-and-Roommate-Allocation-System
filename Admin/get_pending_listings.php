<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST");

$host = "127.0.0.1"; $username = "root"; $password = ""; $dbname = "roomly_db"; $port = 3307;
$conn = new mysqli($host, $username, $password, $dbname, $port);

if ($conn->connect_error) {
    echo json_encode(["status" => "error", "message" => "Database connection breakdown."]);
    exit();
}

// ========================================================
// HANDLE POST: EXECUTE APPROVALS / REJECTIONS
// ========================================================
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $listing_id = isset($_POST['listing_id']) ? intval($_POST['listing_id']) : 0;
    $action = isset($_POST['action']) ? trim($_POST['action']) : '';

    if ($listing_id <= 0 || empty($action)) {
        echo json_encode(["status" => "error", "message" => "Invalid operational parameters."]);
        exit();
    }

    if ($action === 'approve') {
        // 1. Mark verified inside hostels verification matrix
        $stmt1 = $conn->prepare("UPDATE hostels SET verified = 1 WHERE hostel_id = ? OR property_id = ?");
        $stmt1->bind_param("ii", $listing_id, $listing_id);
        $stmt1->execute();

        // 2. Flip status parameters inside properties infrastructure
        $stmt2 = $conn->prepare("UPDATE properties SET status = 'Available', status_color = 'var(--neon-cyan)' WHERE property_id = ?");
        $stmt2->bind_param("i", $listing_id);
        $stmt2->execute();
        
        echo json_encode(["status" => "success", "message" => "Listing authorized successfully."]);
    } else if ($action === 'reject') {
        // Remove or flag item out of verification grids
        $stmt1 = $conn->prepare("DELETE FROM hostels WHERE property_id = ?");
        $stmt1->bind_param("i", $listing_id);
        $stmt1->execute();

        $stmt2 = $conn->prepare("UPDATE properties SET status = 'Rejected', status_color = 'var(--danger)' WHERE property_id = ?");
        $stmt2->bind_param("i", $listing_id);
        $stmt2->execute();

        echo json_encode(["status" => "success", "message" => "Listing rejected and removed from queue."]);
    }
    exit();
}

// ========================================================
// HANDLE GET: STREAM PENDING QUEUE TELEMETRY
// ========================================================
if ($_SERVER["REQUEST_METHOD"] === "GET") {
    $query = "SELECT p.property_id AS listing_id, p.title, l.name AS landlord_name, h.location, p.price 
              FROM hostels h 
              JOIN properties p ON h.property_id = p.property_id 
              JOIN landlords l ON p.landlord_id = l.landlord_id 
              WHERE h.verified = 0";
              
    $result = $conn->query($query);
    $queue = [];

    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $queue[] = $row;
        }
    }

    echo json_encode(["queue" => $queue]);
    exit();
}
?>