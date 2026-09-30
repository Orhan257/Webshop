<?php
require_once "includes/config.php";

function getItems() {
    global $conn;
    $sql = "SELECT * FROM items";
    $result = $conn->query($sql);

    $items = [];
    while ($row = $result->fetch_assoc()) {
        $items[] = $row;
    }
    return $items;
}
?>
