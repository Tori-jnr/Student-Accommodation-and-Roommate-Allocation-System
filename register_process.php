<?php
session_start();

// Database connection
$conn = new mysqli('localhost', 'root', '', 'roomly_db');

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize inputs
    $full_name = $conn->real_escape_string($_POST['full_name']);
    $phone     = $conn->real_escape_string($_POST['phone']);
    $email     = $conn->real_escape_string($_POST['email']);
    $role      = $conn->real_escape_string($_POST['role']);
    $gender    = $conn->real_escape_string($_POST['gender']);
    $password  = $_POST['password'];
    $confirm   = $_POST['confirm_password'];

    // Validation
    if ($password !== $confirm) {
        $_SESSION['error'] = "Passwords do not match!";
        header("Location: register.php");
        exit();
    }

  // Check if email already exists in the selected user table
$table = ($role === "landlord") ? "landlords" : "students";
$idColumn = ($role === "landlord") ? "landlord_id" : "student_id";

$checkStmt = $conn->prepare("SELECT $idColumn FROM $table WHERE email = ?");
$checkStmt->bind_param("s", $email);
$checkStmt->execute();
$result = $checkStmt->get_result();

if ($result->num_rows > 0) {
    $_SESSION['error'] = "An account with this email already exists.";
    header("Location: register.php");
    exit();
}

$checkStmt->close();

    
   // Hash password
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

if ($role === "landlord") {

 $result = $conn->query("
SELECT landlord_id
FROM landlords
ORDER BY CAST(SUBSTRING(landlord_id,2) AS UNSIGNED) DESC
LIMIT 1
");

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $lastNumber = intval(substr($row['landlord_id'], 1));
    $landlord_id = "L" . ($lastNumber + 1);
} else {
    $landlord_id = "L1";
}
$result->free();

$stmt = $conn->prepare("
INSERT INTO landlords
(landlord_id, full_name, email, password, gender, role, phone)
VALUES (?, ?, ?, ?, ?, ?, ?)
");

    $stmt->bind_param(
        "sssssss",
        $landlord_id,
        $full_name,
        $email,
        $hashed_password,
        $gender,
        $role,
        $phone
    );

} else {

    $empty = "";

    $stmt = $conn->prepare("INSERT INTO students (full_name, phone, email, role, gender, password, profile_pic, university, bio) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");

    $stmt->bind_param(
        "sssssssss",
        $full_name,
        $phone,
        $email,
        $role,
        $gender,
        $hashed_password,
        $empty,
        $empty,
        $empty
    );
}
    if ($stmt->execute()) {
        $_SESSION['success'] = "Registration successful! You can now log in.";
        header("Location: Login.html");
        exit();
    } else {
        $_SESSION['error'] = "Database error: " . $stmt->error;
        header("Location: register.php");
        exit();
    }
    $stmt->close();
}
?>