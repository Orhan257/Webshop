<?php
require_once "includes/config.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$cart_id = $_POST['cart_id'];

$stmt = $conn->prepare("DELETE FROM carts WHERE ID = ? AND user_id = ?");
$stmt->bind_param("ii", $cart_id, $_SESSION['user_id']);
$stmt->execute();

header("Location: cart.php");
exit;
