<?php
session_start();

// Connect using the exact same method you used in register_process.php
require_once "db_connect.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $conn->real_escape_string($_POST['email']);
    $password = $_POST['password'];
    $role = $conn->real_escape_string($_POST['role']); 

    $table = ($role === 'landlord') ? 'landlords' : 'students';
    
    // Check if the ID column is named student_id or landlord_id
    $id_column = ($role === 'landlord') ? 'landlord_id' : 'student_id';

    // 1. Find the user based on the email
    $sql = "SELECT $id_column, password FROM $table WHERE email = '$email'";
    $result = $conn->query($sql);

    if ($result && $result->num_rows > 0) {
        $user = $result->fetch_assoc();

        // 2. Verify the typed password against the hash saved in your 'password' column
        if (password_verify($password, $user['password'])) {
            
            // 3. Set the EXACT session variable your profile.php is looking for
          $_SESSION['role'] = $role;

if ($role === "student") {
    $_SESSION['student_id'] = $user['student_id'];
    header("Location: Student/Dashboard.php");
}

elseif ($role === "landlord") {
    $_SESSION['landlord_id'] = $user['landlord_id'];
    header("Location: Landlord/Dashboard.php");
}

exit();

        } else {
            $_SESSION['error'] = "Invalid email or password.";
            header("Location: Login.php");
            exit();
        }
    } else {
        $_SESSION['error'] = "No account found with that email.";
        header("Location: Login.php");
        exit();
    }
} else {
    header("Location: Login.php");
    exit();
}
?>