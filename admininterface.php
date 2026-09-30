<?php
session_start();
require_once __DIR__ . '/includes/config.php';

// --- ADMIN ACCESS CONTROL ---
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || ($_SESSION['is_admin'] ?? 0) != 1) {
    header("Location: login.php");
    exit;
}

// --- HANDLE DELETE ACTION ---
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);

    // Fetch image first
    $stmt = $conn->prepare("SELECT image FROM items WHERE id = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $stmt->bind_result($image);
    $stmt->fetch();
    $stmt->close();

    if (!empty($image) && file_exists(__DIR__ . '/img/' . $image)) {
        unlink(__DIR__ . '/img/' . $image);
    }

    // Delete item
    $stmt = $conn->prepare("DELETE FROM items WHERE id = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $stmt->close();

    header("Location: admininterface.php");
    exit;
}

// --- HANDLE EDIT FORM ---
$editMode = false;
$product = ["id" => 0, "name" => "", "rarity" => "", "price" => "", "quantity" => "", "image" => ""];

if (isset($_GET['edit'])) {
    $editMode = true;
    $id = intval($_GET['edit']);

    $stmt = $conn->prepare("SELECT id, name, rarity, price, quantity, image FROM items WHERE id = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $stmt->bind_result($pid, $pname, $prarity, $pprice, $pquantity, $pimage);
    if ($stmt->fetch()) {
        $product = [
            'id' => $pid,
            'name' => $pname,
            'rarity' => $prarity,
            'price' => $pprice,
            'quantity' => $pquantity,
            'image' => $pimage
        ];
    }
    $stmt->close();
}

// --- HANDLE FORM SUBMISSION ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $name     = $_POST['name'] ?? '';
    $rarity   = $_POST['rarity'] ?? '';
    $price    = floatval($_POST['price'] ?? 0);
    $quantity = intval($_POST['quantity'] ?? 0);
    $id       = intval($_POST['id'] ?? 0);

    $imageName = null;

    if (!empty($_FILES['image']['name'])) {
        $imageName = time() . '_' . basename($_FILES['image']['name']);
        move_uploaded_file($_FILES['image']['tmp_name'], __DIR__ . '/img/' . $imageName);
    }

    if ($id > 0) {
        // UPDATE
        if ($imageName) {
            $stmt = $conn->prepare("UPDATE items SET name=?, rarity=?, price=?, quantity=?, image=? WHERE id=?");
            $stmt->bind_param('ssdisi', $name, $rarity, $price, $quantity, $imageName, $id);
        } else {
            $stmt = $conn->prepare("UPDATE items SET name=?, rarity=?, price=?, quantity=? WHERE id=?");
            $stmt->bind_param('ssdii', $name, $rarity, $price, $quantity, $id);
        }
        $stmt->execute();
        $stmt->close();
    } else {
        // INSERT
        $stmt = $conn->prepare("INSERT INTO items (name, rarity, price, quantity, image) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param('ssdss', $name, $rarity, $price, $quantity, $imageName);
        $stmt->execute();
        $stmt->close();
    }

    header("Location: admininterface.php");
    exit;
}

// --- FETCH ALL ITEMS ---
$stmt = $conn->prepare("SELECT id, name, rarity, price, quantity, image FROM items ORDER BY id DESC");
$stmt->execute();
$stmt->bind_result($id, $name, $rarity, $price, $quantity, $image);
$products = [];
while ($stmt->fetch()) {
    $products[] = [
        'id' => $id,
        'name' => $name,
        'rarity' => $rarity,
        'price' => $price,
        'quantity' => $quantity,
        'image' => $image
    ];
}
$stmt->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Interface</title>

<style>
    body {
        background: #121212;
        color: white;
        font-family: Arial, sans-serif;
        margin: 0;
        padding: 20px;
    }

    .admin-box {
        max-width: 700px;
        margin: auto;
        background: #1e1e1e;
        padding: 25px;
        border-radius: 18px;
        box-shadow: 0 0 12px rgba(0,0,0,0.6);
    }

    h2 {
        text-align: center;
        margin-bottom: 20px;
    }

    label {
        font-weight: bold;
        display: block;
        margin-top: 10px;
    }

    input, textarea, select {
        width: 100%;
        padding: 10px;
        margin-top: 5px;
        border-radius: 8px;
        border: none;
        background: #2a2a2a;
        color: white;
    }

    button {
        width: 100%;
        padding: 12px;
        margin-top: 20px;
        background: #ff9800;
        color: black;
        font-weight: bold;
        border: none;
        border-radius: 8px;
        cursor: pointer;
        transition: 0.2s;
    }

    button:hover {
        background: #ffb74d;
    }

    .item-card {
        background: #181818;
        padding: 12px;
        border-radius: 10px;
        margin-top: 12px;
    }

    .item-card a {
        color: #ff9800;
        text-decoration: none;
        margin-right: 8px;
    }

    .back-button {
        display: block;
        background: #444;
        color: white;
        padding: 12px;
        text-align: center;
        border-radius: 10px;
        margin: 15px 0;
        text-decoration: none;
        transition: 0.2s;
    }

    .back-button:hover {
        background: #555;
    }
</style>

</head>
<body>

<div class="admin-box">

    <a href="index.php" class="back-button">⬅ Back to Shop</a>

    <h2><?php echo $editMode ? "Edit Item" : "Add Item"; ?></h2>

    <form method="POST" enctype="multipart/form-data">
        <input type="hidden" name="id" value="<?php echo $product['id']; ?>">

        <label>Name</label>
        <input type="text" name="name" value="<?php echo htmlspecialchars($product['name']); ?>" required>

        <label>Rarity</label>
        <textarea name="rarity" required><?php echo htmlspecialchars($product['rarity']); ?></textarea>

        <label>Price</label>
        <input type="number" step="0.01" name="price" value="<?php echo $product['price']; ?>" required>

        <label>Quantity</label>
        <input type="number" name="quantity" value="<?php echo $product['quantity']; ?>" required>

        <label>Image</label>
        <input type="file" name="image">

        <?php if ($product['image']) { ?>
            <p>Current Image:</p>
            <img src="img/<?php echo $product['image']; ?>" style="height:100px; border-radius:8px;">
        <?php } ?>

        <button type="submit">Save</button>
    </form>

    <hr style="margin: 25px 0; border: 1px solid #333;">

    <h2>Item List</h2>

    <?php foreach ($products as $p): ?>
        <div class="item-card">
            <strong><?php echo htmlspecialchars($p['name']); ?></strong>
            (<?php echo htmlspecialchars($p['rarity']); ?>)<br>

            Price: $<?php echo $p['price']; ?> |
            Quantity: <?php echo $p['quantity']; ?><br><br>

            <a href="admininterface.php?edit=<?php echo $p['id']; ?>">Edit</a>
            <a href="admininterface.php?delete=<?php echo $p['id']; ?>" 
               onclick="return confirm('Delete this item?');">Delete</a>
        </div>
    <?php endforeach; ?>

</div>

</body>
</html>