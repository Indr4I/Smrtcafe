<?php
session_start();
include 'db_config.php';

// Handle table status updates
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['occupy_table'])) {
        $table_id = $_POST['table_id'];
        $stmt = $conn->prepare("UPDATE tables SET is_occupied = 1 WHERE id = ?");
        $stmt->bind_param("i", $table_id);
        $stmt->execute();
        $stmt->close();
    } elseif (isset($_POST['free_table'])) {
        $table_id = $_POST['table_id'];
        // Mark orders as paid
        $stmt = $conn->prepare("UPDATE orders SET status = 'paid' WHERE table_id = ? AND status = 'pending'");
        $stmt->bind_param("i", $table_id);
        $stmt->execute();
        $stmt->close();
        // Free the table
        $stmt = $conn->prepare("UPDATE tables SET is_occupied = 0 WHERE id = ?");
        $stmt->bind_param("i", $table_id);
        $stmt->execute();
        $stmt->close();
    } elseif (isset($_POST['clear_table'])) {
        $table_id = $_POST['table_id'];
        // Mark all pending orders for this table as completed
        $stmt = $conn->prepare("UPDATE orders SET status = 'completed' WHERE table_id = ? AND status = 'pending'");
        $stmt->bind_param("i", $table_id);
        $stmt->execute();
        $stmt->close();
        // Free the table
        $stmt = $conn->prepare("UPDATE tables SET is_occupied = 0 WHERE id = ?");
        $stmt->bind_param("i", $table_id);
        $stmt->execute();
        $stmt->close();
    }
}

// Fetch all tables with their latest pending orders
$tables_query = "
    SELECT t.id, t.table_number, IFNULL(t.is_occupied, 0) AS is_occupied,
           o.id AS order_id, o.total, o.status, o.order_date
    FROM tables t
    LEFT JOIN orders o ON t.id = o.table_id AND o.status = 'pending'
    ORDER BY t.id
";
$result = $conn->query($tables_query);
if (!$result) {
    die("Database query failed: " . $conn->error . ". Please ensure the database schema is updated with the 'is_occupied' column.");
}
$tables = [];
while ($row = $result->fetch_assoc()) {
    // Automatically set occupancy based on pending orders
    $has_pending = !is_null($row['order_id']);
    $row['is_occupied'] = $has_pending ? 1 : 0;
    // Update the database to reflect this
    $update_stmt = $conn->prepare("UPDATE tables SET is_occupied = ? WHERE id = ?");
    $update_stmt->bind_param("ii", $row['is_occupied'], $row['id']);
    $update_stmt->execute();
    $update_stmt->close();
    $tables[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cashier - SmartCafe</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .table-list { display: flex; flex-wrap: wrap; gap: 20px; padding: 20px; }
        .table-card {
            border: 1px solid #ddd; border-radius: 8px; padding: 15px;
            width: 250px; background: #f9f9f9;
        }
        .table-card.occupied { background: #ffeaa7; }
        .table-card.free { background: #d4edda; }
        .order-details { margin-top: 10px; }
        .order-item { display: flex; justify-content: space-between; }
    </style>
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
            <h1>Cashier Dashboard</h1>
        </div>
    </header>

    <div class="table-list">
        <?php foreach ($tables as $table): ?>
            <div class="table-card <?php echo $table['is_occupied'] ? 'occupied' : 'free'; ?>">
                <h3>Table <?php echo htmlspecialchars($table['table_number']); ?></h3>
                <p>Status: <?php echo $table['is_occupied'] ? 'Occupied' : 'Free'; ?></p>

                <?php if ($table['order_id']): ?>
                    <div class="order-details">
                        <h4>Current Order</h4>
                        <p>Order ID: <?php echo $table['order_id']; ?></p>
                        <p>Total: Rp <?php echo number_format($table['total'], 0, ',', '.'); ?></p>
                        <p>Status: <?php echo htmlspecialchars($table['status']); ?></p>
                        <p>Time: <?php echo htmlspecialchars($table['order_date']); ?></p>

                        <!-- Fetch order items -->
                        <?php
                        $order_items_query = "
                            SELECT m.name, oi.quantity, oi.price
                            FROM order_items oi
                            JOIN menu m ON oi.menu_id = m.id
                            WHERE oi.order_id = ?
                        ";
                        $stmt = $conn->prepare($order_items_query);
                        $stmt->bind_param("i", $table['order_id']);
                        $stmt->execute();
                        $items_result = $stmt->get_result();
                        ?>
                        <ul>
                            <?php while ($item = $items_result->fetch_assoc()): ?>
                                <li class="order-item">
                                    <span><?php echo htmlspecialchars($item['name']); ?> x<?php echo $item['quantity']; ?></span>
                                    <span>Rp <?php echo number_format($item['price'], 0, ',', '.'); ?></span>
                                </li>
                            <?php endwhile; ?>
                        </ul>
                        <?php $stmt->close(); ?>
                    </div>
                <?php else: ?>
                    <p>No pending orders</p>
                <?php endif; ?>

                <form method="post" style="margin-top: 10px;">
                    <input type="hidden" name="table_id" value="<?php echo $table['id']; ?>">
                    <div>
                    <?php if ($table['is_occupied']): ?>
                        <button type="submit" name="free_table" class="btn">Mark as Paid</button>
                        <button type="submit" name="clear_table" class="btn" style="background: #28a745; margin-left: 5px;">Clear Table</button>
                    <?php else: ?>
                        <button type="submit" name="occupy_table" class="btn">Mark as Occupied</button>
                    <?php endif; ?>
                    </div>
                </form>
            </div>
        <?php endforeach; ?>
    </div>
</div>

</body>
</html>
