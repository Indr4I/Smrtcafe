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
    } elseif (isset($_POST['select_table'])) {
        if (isset($_POST['table_id']) && !empty($_POST['table_id'])) {
            $_SESSION['table_id'] = $_POST['table_id'];
        }
    } elseif (isset($_POST['add_item'])) {
        header("Location: index.php");
        exit();
    } elseif (isset($_POST['checkout'])) {
        // Check if cart is empty
        if (empty($_SESSION['cart'])) {
            $error_message = "Keranjang kosong. Silakan tambah pesanan terlebih dahulu.";
        } elseif (!isset($_SESSION['table_id']) || empty($_SESSION['table_id'])) {
            $error_message = "Silakan pilih meja terlebih dahulu.";
        } else {
            unset($error_message); // Clear error if table is selected
            // Insert order into database
            $total = 0;
            foreach ($_SESSION['cart'] as $item) {
                $total += $item['price'] * $item['qty'];
            }
            $stmt = $conn->prepare("INSERT INTO orders (total, table_id) VALUES (?, ?)");
            $stmt->bind_param("ii", $total, $_SESSION['table_id']);
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
            $_SESSION['last_order_id'] = $order_id;
            header("Location: s_order.php");
            exit();
        }
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
    <link rel="stylesheet" href="style.css?v=1">
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

<div class="main-content">
    <section class="section-title" style="margin-top: -10px;margin-left: 15px;">
        <h1>Pesanan</h1>
    </section>

    <div class="item-list cart-list">
        <?php foreach ($_SESSION['cart'] as $index => $item): ?>
        <?php
        // Fetch image if not in session
        if (!isset($item['image'])) {
            $stmt = $conn->prepare("SELECT image FROM menu WHERE id = ?");
            $stmt->bind_param("i", $item['id']);
            $stmt->execute();
            $result = $stmt->get_result();
            $menu_item = $result->fetch_assoc();
            $item['image'] = $menu_item['image'] ?? 'img/default.jpg';
            $_SESSION['cart'][$index]['image'] = $item['image']; // Update session
            $stmt->close();
        }
        ?>
        <div class="cart-item">

            <!-- Image -->
            <img src="<?php echo htmlspecialchars($item['image']); ?>" class="cart-img">

            <!-- Info -->
            <div class="cart-info">
                <h4><?php echo htmlspecialchars($item['name']); ?></h4>
                <span class="price">Rp <?php echo number_format($item['price'], 0, ',', '.'); ?></span>
            </div>

            <!-- Controls -->
            <form method="post" class="cart-controls">
                <button type="submit" name="decrease[<?php echo $index; ?>]" class="qty-btn">-</button>
                <span class="qty"><?php echo $item['qty']; ?></span>
                <button type="submit" name="increase[<?php echo $index; ?>]" class="qty-btn">+</button>
                <button type="submit" name="delete[<?php echo $index; ?>]" class="delete-btn">🗑</button>
            </form>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- table selection -->

    <section class="section-title">
        <h2>Pilih Meja</h2>
    </section>

    <div class="setting-box">
        <form method="post">
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
            <button type="submit" name="select_table" class="btn">Pilih</button>
        </form>

        <a href="qr_scans.php" class="scan-link">📷 Scan QR Code</a>
    </div>

    <div class="cart-footer">
        <h3>Total Pesanan: Rp <?php echo number_format($total, 0, ',', '.'); ?></h3>

        <?php if (isset($error_message)): ?>
            <p style="color: red;"><?php echo htmlspecialchars($error_message); ?></p>
        <?php endif; ?>

        <form method="post" class="action-buttons">
            <button type="submit" name="add_item" class="btn">Tambah Pesanan</button>
            <button type="submit" name="checkout" class="btn">Checkout</button>
        </form>
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

</body>
</html>
