<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");

$host = "127.0.0.1"; $username = "root"; $password = ""; $dbname = "roomly_db"; $port = 3307;
$conn = new mysqli($host, $username, $password, $dbname, $port);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $landlordId = isset($_POST['landlordId']) ? trim($_POST['landlordId']) : 'L1';
    $title = isset($_POST['title']) ? trim($_POST['title']) : 'Untitled Accommodation';
    $layout = isset($_POST['layout']) ? trim($_POST['layout']) : 'Single Room';
    $price = isset($_POST['price']) ? intval($_POST['price']) : 0;
    $location = isset($_POST['location']) ? trim($_POST['location']) : 'Madaraka';
    $images = isset($_POST['images']) ? trim($_POST['images']) : 'Not Uploaded';
    $tourFile = isset($_POST['tour']) ? trim($_POST['tour']) : 'Not Uploaded';

    if (empty($title) || $price <= 0) {
        echo json_encode(["status" => "error", "message" => "Please complete all mandatory inventory parameters."]);
        exit();
    }

    // 1. Deploy the accommodation parameters row directly inside properties registry with a hidden unverified status
    $status = "Pending Admin Verification"; 
    $statusColor = "var(--warning)";
    $studentName = "No Student Assigned";
    $notes = "Awaiting structural audit checks by system administrators.";

    $stmt = $conn->prepare("INSERT INTO properties (landlord_id, title, layout, price, status, status_color, student_name, student_notes, hostel_images, virtual_tour_file) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssissssss", $landlordId, $title, $layout, $price, $status, $statusColor, $studentName, $notes, $images, $tourFile);
    
    if ($stmt->execute()) {
        $propertyId = $conn->insert_id;

        // 2. Automatically seed an unverified marker row into the hostels verification queue for the admin panel
        $verifiedFlag = 0; // 0 = Pending state lock
        $hostelStmt = $conn->prepare("INSERT INTO hostels (property_id, name, location, verified) VALUES (?, ?, ?, ?)");
        $hostelStmt->bind_param("issi", $propertyId, $title, $location, $verifiedFlag);
        $hostelStmt->execute();

        // 3. Inject an entry into the system activity logs so the admin sees it on their dashboard timeline
        $logTitle = "New Listing Submitted";
        $logDesc = "Hostel listing '$title' was deployed by Host $landlordId and is currently holding in the verification queue.";
        $logType = "listing";

        $logStmt = $conn->prepare("INSERT INTO activity_log (student_id, activity_type, activity_title, activity_description) VALUES (1, ?, ?, ?)");
        $logStmt->bind_param("sss", $logType, $logTitle, $logDesc);
        $logStmt->execute();

        echo json_encode(["status" => "success", "message" => "Listing deployed to administrative verification queue!"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Database write validation exception active."]);
    }
}
?>
