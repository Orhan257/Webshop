<?php
require_once "includes/config.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$item_id = $_POST['item_id'];
$user_id = $_SESSION['user_id'];

$stmt = $conn->prepare("INSERT INTO carts (item_id, user_id) VALUES (?, ?)");
$stmt->bind_param("ii", $item_id, $user_id);
$stmt->execute();

header("Location: index.php");
exit;
