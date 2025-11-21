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

    <!-- TOP BAR -->
     <header>
      <div class="topbar">
        <div class="brand">
            <a href="index.php">
            <img src="assets/icon.png" alt="Logo" class="logo" />
            </a>
        </div>
        <div style="display:flex;gap:8px;align-items:center">
            <img src="assets/qr-icon.png" alt="qr" class="qr" />
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
        <div class="menu-card">
            <img src="assets/food1.jpg">
            <h4>Latte</h4>
            <p>Mulai 18k</p>
        </div>

        <div class="menu-card">
            <img src="assets/food2.jpg">
            <h4>Mocha</h4>
            <p>Mulai 22k</p>
        </div>

        <div class="menu-card">
            <img src="assets/food3.jpg">
            <h4>Americano</h4>
            <p>Mulai 15k</p>
        </div>
    </div>

</div>
<div>
    <nav class="bottom-nav">
        <a href="#makanan" class="nav-item">
            <span class="icon">🍽️</span>
            <span class="label">Makanan</span>
        </a>
        <a href="minuman.php" class="nav-item">
            <span class="icon">🥤</span>
            <span class="label">Minuman</span>
        </a>
        <a href="#" class="nav-item" onclick="openCart()">
            <span class="icon">🛒</span>
            <span class="label">Cart</span>
        </a>
    </nav>
</div>
</body>
</html>
