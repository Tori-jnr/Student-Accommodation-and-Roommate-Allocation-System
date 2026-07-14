<?php
session_start();

require_once "../db_connect.php";

if(!isset($_SESSION['landlord_id'])){
    header("Location: ../Login.php");
    exit();
}

$landlord_id=$_SESSION['landlord_id'];

if(!isset($_GET['id'])){
    header("Location: Dashboard.php");
    exit();
}

$id=(int)$_GET['id'];


// Find hostel linked to property

$stmt=$conn->prepare("
SELECT hostel_id
FROM hostels
WHERE property_id=?
AND landlord_id=?
");

$stmt->bind_param("is",$id,$landlord_id);
$stmt->execute();

$result=$stmt->get_result();

if($row=$result->fetch_assoc()){

    $hostel=$row['hostel_id'];

    // reviews/rooms reference hostel_id with no cascade delete, must go first
    $conn->query("DELETE FROM reviews WHERE hostel_id=$hostel");

    $conn->query("DELETE FROM rooms WHERE hostel_id=$hostel");

    $conn->query("DELETE FROM hostels WHERE hostel_id=$hostel");

}

$conn->query("DELETE FROM properties WHERE property_id=$id AND landlord_id='$landlord_id'");

header("Location: Dashboard.php");
exit();