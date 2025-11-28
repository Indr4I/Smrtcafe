<?php
session_start();
include 'db_config.php';

// Initialize cart if not set
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Calculate cart count
$cart_count = 0;
if (isset($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $item) {
        $cart_count += $item['qty'];
    }
}

if(isset($_POST['add_to_cart'])){
    $menu_id = $_POST['menu_id'];
    $query = mysqli_query($conn,"SELECT id, name, price, image FROM menu WHERE id = '$menu_id'");
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
            $_SESSION['cart'][] = ['id' => $item['id'], 'name' => $item['name'], 'price' => $item['price'], 'qty' => 1, 'image' => $item['image']];
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QR Scan - SmartCafe</title>
    <link rel="stylesheet" href="style.css?v=2">
    <script src="https://rawgit.com/schmich/instascan-builds/master/instascan.min.js"></script>
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
        <h3>Scan QR Code</h3>
    </div>

    <section class="section-title">
        <h2>QR Scanner</h2>
    </section>

    <div style="text-align: center; padding: 20px;">
        <video id="preview" style="width: 100%; max-width: 400px; border: 1px solid #ccc;"></video>
        <p id="result" style="margin-top: 20px; font-size: 18px;"></p>
    </div>

    <script>
        let scanner = new Instascan.Scanner({ video: document.getElementById('preview') });
        scanner.addListener('scan', function (content) {
            document.getElementById('result').innerText = 'Scanned: ' + content;
            // Handle table QR code
            if (content.startsWith('TABLE:')) {
                var tableCode = content.substring(6); // Remove 'TABLE:' prefix
                // Send to server to set session
                fetch('set_table.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'table_code=' + encodeURIComponent(tableCode)
                }).then(response => response.text()).then(data => {
                    alert('Table set to ' + tableCode);
                    window.location.href = 'cart.php';
                });
            } else if (content.startsWith('http')) {
                window.location.href = content;
            }
        });
        Instascan.Camera.getCameras().then(function (cameras) {
            if (cameras.length > 0) {
                // Prioritize back camera: use the last camera in the list (often the back camera)
                let selectedCamera = cameras[cameras.length - 1];
                scanner.start(selectedCamera);
            } else {
                document.getElementById('result').innerText = 'No cameras found.';
            }
        }).catch(function (e) {
            document.getElementById('result').innerText = 'Camera access denied.';
        });
    </script>

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
