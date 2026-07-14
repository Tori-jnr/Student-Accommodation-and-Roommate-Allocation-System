<?php
session_start();

$conn = new mysqli('localhost', 'root', '', 'roomly_db');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: login.php');
    exit();
}

$email = $conn->real_escape_string($_POST['email']);
$rawPassword = $_POST['password'];

$result = $conn->query("SELECT admin_id, full_name, password FROM admins WHERE email = '$email'");

if ($result->num_rows > 0) {
    $admin = $result->fetch_assoc();
    if (password_verify($rawPassword, $admin['password'])) {
        $_SESSION['admin_id']   = $admin['admin_id'];
        $_SESSION['admin_name'] = $admin['full_name'];
        $_SESSION['role']       = 'admin';
        header('Location: dashboard.php');
        exit();
    }
}

$_SESSION['admin_error'] = 'Invalid admin email or password.';
header('Location: login.php');
exit();