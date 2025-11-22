<?php
session_start();
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}
include 'db_config.php';

// Calculate cart count
$cart_count = 0;
if (isset($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $item) {
        $cart_count += $item['qty'];
    }
}

// Handle add to cart
if(isset($_POST['add_to_cart'])){
    $menu_id = $_POST['menu_id'];
    $query = mysqli_query($conn,"SELECT id, name, price FROM menu WHERE id = '$menu_id'");
    if($item = mysqli_fetch_assoc($query)){
        // Check if item already in cart
        $found = false;
        foreach ($_SESSION['cart'] as &$cart_item) {
            if ($cart_item['id'] == $item['id']) {
                $cart_item['qty']++;
                $found = true;
                break;
            }
        }
        if (!$found) {
            $_SESSION['cart'][] = ['id' => $item['id'], 'name' => $item['name'], 'price' => $item['price'], 'qty' => 1];
        }
    }
    header("Location: #");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1">
    <title>SmartCafe</title>
    <link rel="stylesheet" href="style.css?v=2">
</head>
<body>

<div class="safe-area">

    <!-- TOP BAR -->
     <header>
      <div class="topbar">
        <div class="brand">
            <a href="index.php">
            <img src="assets/icon.png" alt="Logo" class="logo" />
            </a>
        </div>
        <div style="display:flex;gap:8px;align-items:center">
            <a href="qr_scans.php">
            <img src="assets/qr-icon.png" alt="qr" class="qr" />
            </a>
        </div>
      </div>
    </header>

    <!-- HERO BANNER -->
    <section class="hero-banner">
        <img src="assets/hero.jpg" class="hero-img">
    </section>

    <!-- FLOATING CARD -->
    <div class="floating-card">
        <h3>Selamat Datang Di Cafe ...</h3>
    </div>
    <!-- RECOMMENDED -->
    <section class="section-title">
        <h2>Makanan</h2>
    </section>

    <div class="item-list">
        <?php
        $stmt = $conn->prepare("SELECT id, name, price, image FROM menu WHERE category = 'makanan'");
        $stmt->execute();
        $result = $stmt->get_result();
        while ($item = $result->fetch_assoc()) {
            echo '<div class="menu-card">';
            echo '<img src="' . htmlspecialchars($item['image']) . '">';
            echo '<h4>' . htmlspecialchars($item['name']) . '</h4>';
            echo '<p>Mulai ' . number_format($item['price'], 0, ',', '.') . 'k</p>';
            echo '<form method="post" style="display: inline;">';
            echo '<input type="hidden" name="menu_id" value="' . $item['id'] . '">';
            echo '<button type="submit" name="add_to_cart" class="add-to-cart-btn">Add to Cart</button>';
            echo '</form>';
            echo '</div>';
        }
        $stmt->close();
        ?>
    </div>

</div>
<div>
    <nav class="bottom-nav">
        <a href="#" class="nav-item">
            <span class="icon">🍽️</span>
            <span class="label">Makanan</span>
        </a>
        <a href="minuman.php" class="nav-item">
            <span class="icon">🥤</span>
            <span class="label">Minuman</span>
        </a>
        <a href="dessert.php" class="nav-item">
            <span class="icon">🍰</span>
            <span class="label">Dessert</span>
        </a>
        <a href="cart.php" class="nav-item">
            <span class="icon">🛒</span>
            <?php if ($cart_count > 0): ?>
                <span class="cart-notification"><?php echo $cart_count; ?></span>
            <?php endif; ?>
            <span class="label">Cart</span>
        </a>
    </nav>
</div>
</body>
</html>
