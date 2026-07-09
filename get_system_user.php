<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

$host = "127.0.0.1"; $username = "root"; $password = ""; $dbname = "roomly_db"; $port = 3307;
$conn = new mysqli($host, $username, $password, $dbname, $port);

$users = [];

$students = $conn->query("SELECT full_name, email, role FROM students");
while ($row = $students->fetch_assoc()) { $users[] = $row; }

$landlords = $conn->query("SELECT name as full_name, email, role FROM landlords");
while ($row = $landlords->fetch_assoc()) { $users[] = $row; }

echo json_encode(["status" => "success", "users" => $users]);
?>
