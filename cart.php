<?php
session_start();
include 'db_config.php';

// Initialize cart if not set
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

$cart_count = 0;
if (isset($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $item) {
        $cart_count += $item['qty'];
    }
}

// Handle quantity updates and adding items
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['increase'])) {
        $index = key($_POST['increase']);
        $_SESSION['cart'][$index]['qty']++;
    } elseif (isset($_POST['decrease'])) {
        $index = key($_POST['decrease']);
        if ($_SESSION['cart'][$index]['qty'] > 1) {
            $_SESSION['cart'][$index]['qty']--;
        }
    } elseif (isset($_POST['delete'])) {
        $index = key($_POST['delete']);
        unset($_SESSION['cart'][$index]);
        $_SESSION['cart'] = array_values($_SESSION['cart']); // reindex array
    } elseif (isset($_POST['add_item'])) {
        header("Location: index.php");
        exit();
    } elseif (isset($_POST['checkout'])) {
        // Insert order into database
        $total = 0;
        foreach ($_SESSION['cart'] as $item) {
            $total += $item['price'] * $item['qty'];
        }
        $stmt = $conn->prepare("INSERT INTO orders (total) VALUES (?)");
        $stmt->bind_param("i", $total);
        $stmt->execute();
        $order_id = $stmt->insert_id;
        $stmt->close();

        // Insert order items
        foreach ($_SESSION['cart'] as $item) {
            $subtotal = $item['price'] * $item['qty'];
            $stmt = $conn->prepare("INSERT INTO order_items (order_id, menu_id, quantity, price) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("iiii", $order_id, $item['id'], $item['qty'], $subtotal);
            $stmt->execute();
            $stmt->close();
        }

        // Clear cart
        $_SESSION['cart'] = [];
        echo "<script>alert('Order placed successfully!');</script>";
    }
}

// Calculate total
$total = 0;
foreach ($_SESSION['cart'] as $item) {
    $total += $item['price'] * $item['qty'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1">
    <title>SmartCafe</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="safe-area">
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
</div>

<section class="section-title" style="margin-top: -80px;margin-left: 5px;">
    <h1>Pesanan</h1>
</section>

<div class="item-list">
    <?php foreach ($_SESSION['cart'] as $index => $item): ?>
    <div class="menu-card" style="margin-left: 10px;">
        <h4><?php echo htmlspecialchars($item['name']); ?></h4>
        <p>Harga: Rp <?php echo number_format($item['price'], 0, ',', '.'); ?></p>
        <p>Jumlah: <?php echo $item['qty']; ?></p>
        <form method="post" style="display: inline;">
            <button type="submit" name="decrease[<?php echo $index; ?>]">-</button>
            <button type="submit" name="increase[<?php echo $index; ?>]">+</button>
            <button type="submit" name="delete[<?php echo $index; ?>]" style="background-color: red; color: white;margin-left:120px;">X</button>
        </form>
        <p>Subtotal: Rp <?php echo number_format($item['price'] * $item['qty'], 0, ',', '.'); ?></p>
    </div>
    <?php endforeach; ?>
</div>


</div>

<div>
    <nav class="bottom-nav">
        <a href="makanan.php" class="nav-item">
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
        <a href="#" class="nav-item">
            <span class="icon">🛒</span>
            <?php if ($cart_count > 0): ?>
                <span class="cart-notification"><?php echo $cart_count; ?></span>
            <?php endif; ?>
            <span class="label">Cart</span>
        </a>
    </nav>
</div>

<!-- table selection -->

<section class="section-title">
    <h2>Pilih Meja</h2>
</section>
<div style="padding: 10px;">
    <form method="post" style="display: inline;">
        <select name="table_id" required>
            <option value="">Pilih Meja</option>
            <?php
            $stmt = $conn->prepare("SELECT id, table_number FROM tables");
            $stmt->execute();
            $result = $stmt->get_result();
            while ($table = $result->fetch_assoc()) {
                $selected = (isset($_SESSION['table_id']) && $_SESSION['table_id'] == $table['id']) ? 'selected' : '';
                echo '<option value="' . $table['id'] . '" ' . $selected . '>' . htmlspecialchars($table['table_number']) . '</option>';
            }
            $stmt->close();
            ?>
        </select>
        <button type="submit" name="select_table">Pilih</button>
    </form>
    <p style="margin-top: 10px;">Atau scan QR code meja:</p>
    <a href="qr_scans.php" style="color: blue;">Scan QR Code</a>
</div>

<div class="cart-footer">
    <h3>Total Pesanan: Rp <?php echo number_format($total, 0, ',', '.'); ?></h3>
    <form method="post" style="display: inline;">
        <button type="submit" name="add_item">Tambah Pesanan</button>
        <button type="submit" name="checkout">Checkout</button>
    </form>
</div>

</body>
</html>
