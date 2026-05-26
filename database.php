<?php

$host = "sql200.infinityfree.com";
$user = "if0_42010894";
$pass = "aJ2dfoqghIScBjm";
$db   = "if0_42010894_faza_db";

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");

?>
