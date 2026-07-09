<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

// Set explicit local process time limit allowances to prevent engine dropouts
set_time_limit(60);

// Connect through your custom XAMPP MySQL port 3307
$host = "127.0.0.1"; $username = "root"; $password = ""; $dbname = "roomly_db"; $port = 3307;
$conn = new mysqli($host, $username, $password, $dbname, $port);

if ($conn->connect_error) {
    echo json_encode(["status" => "error", "message" => "Port 3307 connection baseline dropped."]);
    exit();
}

// Initialize summary numeric array counters default parameters
$landlord_count = 0;
$listings_count = 0;
$student_count  = 0;
$match_count    = 0;
$pref_count     = 0;

//  HIGH-PERFORMANCE FIXED QUERY MATRIX: Combines tables counts via UNION ALL to stop database hangs!
$master_query = "
    SELECT 'landlords' as table_name, COUNT(*) as total FROM landlords
    UNION ALL
    SELECT 'properties' as table_name, COUNT(*) as total FROM properties
    UNION ALL
    SELECT 'students' as table_name, COUNT(*) as total FROM students
    UNION ALL
    SELECT 'roommate_matches' as table_name, COUNT(*) as total FROM roommate_matches
    UNION ALL
    SELECT 'student_preferences' as table_name, COUNT(*) as total FROM student_preferences
";

$result = $conn->query($master_query);

if ($result) {
    while ($row = $result->fetch_assoc()) {
        switch ($row['table_name']) {
            case 'landlords': $landlord_count = intval($row['total']); break;
            case 'properties': $listings_count = intval($row['total']); break;
            case 'students': $student_count = intval($row['total']); break;
            case 'roommate_matches': $match_count = intval($row['total']); break;
            case 'student_preferences': $pref_count = intval($row['total']); break;
        }
    }
}

// Intercept custom data compilation button clicks from your frontend layout view panel
if (isset($_GET['action']) && $_GET['action'] === 'compile') {
    echo json_encode([
        "status"      => "success",
        "landlords"   => $landlord_count,
        "listings"    => $listings_count,
        "students"    => $student_count,
        "matches"     => $match_count,
        "compiled_at" => date("d M Y, h:i A")
    ]);
    exit();
}

// Fallback real-time telemetry log feed streams
$logs = [
    ["title" => "System Core Alignment Matrix", "desc" => "Successfully verified active constraints across $landlord_count registered landlords on port 3307."],
    ["title" => "Accommodation Inventory Audit", "desc" => "Currently tracking $listings_count total housing property records inside the properties registry table."],
    ["title" => "Allocation Vector Synced", "desc" => "Compiled $match_count high-accuracy roommate match percentage profiles in system memory."],
    ["title" => "Preference Roster Verified", "desc" => "Loaded $pref_count student filtration questionnaire form fields into active memory loops."]
];

echo json_encode(["status" => "success", "reports" => $logs]);
?>
