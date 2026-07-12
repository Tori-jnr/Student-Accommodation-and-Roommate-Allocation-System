<?php
session_start();
$host = "127.0.0.1"; $username = "root"; $password = ""; $dbname = "roomly_db"; $port = 3307;
$conn = new mysqli($host, $username, $password, $dbname, $port);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $conn->real_escape_string($_POST['email']);
    $password = $_POST['password'];
    $role = $conn->real_escape_string($_POST['role']); 
    $table = ($role === 'landlord') ? 'landlords' : 'students';
    
    $id_column = ($role === 'landlord') ? 'landlord_id' : 'student_id';
    $sql = "SELECT $id_column, password FROM $table WHERE email = '$email'";
    $result = $conn->query($sql);
    if ($result && $result->num_rows > 0) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            $_SESSION['student_id'] = $user[$id_column];
            $_SESSION['role'] = $role;
            if ($role === 'landlord') {
                header("Location: Landlord/Dashboard.php");
            } else {
                header("Location: Student/Dashboard.php");
            }
            exit();
        } else {
            echo "Invalid password. <a href='Login.html'>Go back</a>";
        }
    } else {
        echo "No account found with that email. <a href='Register.php'>Register here</a>";
    }
} else {
    header("Location: Login.html");
    exit();
}
?>