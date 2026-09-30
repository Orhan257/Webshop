<?php
require_once "includes/config.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

$sql = "
    SELECT carts.ID, items.name, items.price, items.image 
    FROM carts 
    JOIN items ON carts.item_id = items.id 
    WHERE carts.user_id = ?
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$total = 0;
?>
<!DOCTYPE html>
<html>
<head>
<title>Your Cart</title>
<link rel="stylesheet" href="style/styles.css">
</head>
<body>

<header class="site-header">
  <div class="container header-inner">
    <a href="index.php" class="brand">CSGO Shop</a>
  </div>
</header>

<div class="container" style="margin-top:40px;">
<h2>Your Shopping Cart</h2>
<a href="index.php" class="btn">Continue Shopping</a>
<br><br>

<table border="1" cellpadding="10">
<tr>
    <th>Image</th>
    <th>Name</th>
    <th>Price</th>
    <th>Action</th>
</tr>

<?php while($row = $result->fetch_assoc()): 
    $total += $row['price'];
?>
<tr>
    <td><img src="img/<?php echo $row['image']; ?>" width="80"></td>
    <td><?php echo $row['name']; ?></td>
    <td>€<?php echo $row['price']; ?></td>
    <td>
        <form method="POST" action="remove_from_cart.php">
            <input type="hidden" name="cart_id" value="<?php echo $row['ID']; ?>">
            <button class="btn" style="background:red;">Remove</button>
        </form>
    </td>
</tr>
<?php endwhile; ?>
</table>

<h3>Total: €<?php echo number_format($total,2); ?></h3>

</div>
</body>
</html>
