<?php
session_start();
include 'db_config.php';

// Calculate cart count (though cart should be empty after order)
$cart_count = 0;
if (isset($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $item) {
        $cart_count += $item['qty'];
    }
}

// Check if there's a valid order
if (!isset($_SESSION['last_order_id'])) {
    header("Location: index.php");
    exit();
}

// Get table number and order details for the last order
$table_number = '';
$order_items = [];
$total = 0;

$stmt = $conn->prepare("SELECT t.table_number, o.total FROM orders o JOIN tables t ON o.table_id = t.id WHERE o.id = ?");
$stmt->bind_param("i", $_SESSION['last_order_id']);
$stmt->execute();
$result = $stmt->get_result();
if ($row = $result->fetch_assoc()) {
    $table_number = $row['table_number'];
    $total = $row['total'];
} else {
    // Order not found, redirect
    unset($_SESSION['last_order_id']);
    header("Location: index.php");
    exit();
}
$stmt->close();

// Get order items
$stmt = $conn->prepare("SELECT m.name, oi.quantity, oi.price FROM order_items oi JOIN menu m ON oi.menu_id = m.id WHERE oi.order_id = ?");
$stmt->bind_param("i", $_SESSION['last_order_id']);
$stmt->execute();
$result = $stmt->get_result();
while ($item = $result->fetch_assoc()) {
    $order_items[] = $item;
}
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1">
    <title>SmartCafe - Order Status</title>
    <link rel="stylesheet" href="style.css?v=2">
    <style>
        .order-status {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 60vh;
            text-align: center;
        }
        .big-check {
            font-size: 100px;
            color: green;
            margin-bottom: 20px;
        }
        .status-message {
            font-size: 24px;
            color: #333;
        }
        .order-summary {
            margin: 20px 0;
            background: #f9f9f9;
            padding: 15px;
            border-radius: 8px;
            width: 100%;
            max-width: 400px;
        }
        .order-summary h3 {
            margin: 0 0 10px 0;
            color: #333;
        }
        .order-summary p {
            margin: 5px 0;
            color: #666;
        }
        .order-summary h4 {
            margin: 10px 0 0 0;
            color: #333;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }
    </style>
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

    <!-- ORDER STATUS -->
    <div class="order-status">
        <div class="big-check">✔</div>
        <div class="status-message">
            <?php if (!empty($table_number)): ?>
                Pesanan meja <?php echo htmlspecialchars($table_number); ?> sedang dibuat
            <?php else: ?>
                Your order is on the way to making!
            <?php endif; ?>
        </div>

        <!-- ORDER SUMMARY -->
        <div class="order-summary">
            <h3>Ringkasan Pesanan</h3>
            <?php foreach ($order_items as $item): ?>
                <p><?php echo htmlspecialchars($item['name']); ?> x<?php echo $item['quantity']; ?> - Rp <?php echo number_format($item['price'], 0, ',', '.'); ?></p>
            <?php endforeach; ?>
            <h4>Total: Rp <?php echo number_format($total, 0, ',', '.'); ?></h4>
        </div>

        <a href="index.php" class="btn" style="margin-top: 20px;">Back to Home</a>
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
