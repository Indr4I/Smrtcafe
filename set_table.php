<?php
session_start();
include 'db_config.php';

if (!isset($_POST['table_code'])) {
    echo "ERROR: No table code.";
    exit;
}

$table_number = $_POST['table_code'];

// Query to get the table id from table_number
$stmt = $conn->prepare("SELECT id FROM tables WHERE table_number = ?");
$stmt->bind_param("s", $table_number);
$stmt->execute();
$result = $stmt->get_result();
if ($row = $result->fetch_assoc()) {
    $_SESSION['table_id'] = $row['id'];
    $_SESSION['table'] = $table_number; // Keep for display if needed
    echo "OK";
} else {
    echo "ERROR: Table not found.";
}
$stmt->close();
