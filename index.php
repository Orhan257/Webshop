<?php
require_once "includes/functions.php";
$items = getItems();
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>CSGO Shop</title>

    <!-- CSS Link -->
    <link rel="stylesheet" href="style/styles.css">
  </head>

  <body>
    <!-- HEADER -->
    <header class="site-header">
      <div class="container header-inner">
        <a href="index.php" class="brand">CSGO Shop</a>
        <nav class="nav">
          <?php if (!isset($_SESSION['user'])): ?>
            <a href="login.php">Sign Up/Log in</a>
            <a href="login.php">Shopping Cart</a>
          <?php else: ?>
            <span style="color:white;">Hello, <?php echo $_SESSION['user']; ?></span>
            <a href="cart.php">Shopping Cart</a>
            <a href="logout.php">Logout</a>
          <?php endif; ?>
        </nav>
      </div>
    </header>

    <!-- HERO -->
    <section class="section hero">
      <div class="container grid two-col">
        <div class="hero-text">
          <h1>Low Prices</h1>
          <p class="lead">Opened recently</p>

          <div class="info-box">
            <div class="info-item"><strong>What</strong><span>Cyber Monday Sale!</span></div>
            <div class="info-item"><strong>When</strong><span>December 1th — 6am–10pm</span></div>
            <div class="info-item">
              <strong>Where</strong>
              <span>Street name — <a href="#phonenumber" class="text-link">Telephone nummer</a></span>
            </div>

            <?php if (!isset($_SESSION['user'])): ?>
              <a href="login.php" class="btn">Sign Up / Log In to Shop</a>
            <?php else: ?>
              <a href="#products" class="btn">Start Shopping</a>
            <?php endif; ?>
          </div>
        </div>

        <aside class="hero-aside">
          <div class="info-box">
            <h3>Follow</h3>
            <p><a href="https://www.instagram.com" class="text-link">@UniqueCSGOShop On Instagram</a></p>
            <p class="muted small">CSGO Shop © 2025 All Rights Reserved</p>
          </div>
        </aside>
      </div>
    </section>

    <!-- PRODUCTS FROM DATABASE -->
    <section class="section" id="products">
      <div class="container">
        <div class="grid product-grid">

          <?php foreach ($items as $item): ?>
            <article class="product-card">
              <img src="img/<?php echo $item['image']; ?>" alt="<?php echo $item['name']; ?>">
              <h4><?php echo $item['name']; ?></h4>
              <p class="muted small">Rarity: <?php echo $item['rarity']; ?></p>
              <p class="muted small">Price: €<?php echo $item['price']; ?></p>
              <p class="muted small">Quantity: <?php echo $item['quantity']; ?></p>

              <?php if (isset($_SESSION['user'])): ?>
                <form action="add_to_cart.php" method="POST">
                    <input type="hidden" name="item_id" value="<?php echo $item['id']; ?>">
                    <button class="btn" style="margin-top:10px;">Add to Cart</button>
                </form>
              <?php else: ?>
                <p style="color:orange; margin-top:10px;">Login to buy</p>
              <?php endif; ?>

            </article>
          <?php endforeach; ?>

        </div>
      </div>
    </section>

    <!-- FOOTER -->
    <footer class="site-footer">
      <div class="container">
        <p>© CSGO Shop 2025 - All Rights Reserved</p>
      </div>
    </footer>
  </body>
</html>
