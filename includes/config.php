<?php
$host = "localhost";
$user = "root";
$pass = "root";
$db   = "shopdb";
$port = 8889;

$conn = new mysqli($host, $user, $pass, $db, $port);

if ($conn->connect_error) {
    die("Database connectie mislukt: " . $conn->connect_error);
}
