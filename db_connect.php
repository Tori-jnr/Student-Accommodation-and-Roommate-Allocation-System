<?php

$conn = mysqli_connect('localhost', 'root', '', 'roomly_db');

if (!$conn) {
    die('Database connection failed: ' . mysqli_connect_error());
}
?>