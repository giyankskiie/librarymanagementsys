<?php
session_start();
include "db.php";

// Only allow admin
if (!isset($_SESSION["admin"])) {
    header("Location: admin_login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['checkout_id'])) {
    $checkoutId = intval($_POST['checkout_id']);

    // Only change status back to 'borrowed'
    $stmt = $conn->prepare("UPDATE borrow_transactions SET status='borrowed' WHERE checkout_id=? AND status='pending_return'");
    $stmt->bind_param("i", $checkoutId);
    $stmt->execute();

    $_SESSION['message'] = "Return rejected successfully!";
} else {
    $_SESSION['message'] = "No transaction ID provided.";
}

// Redirect back to admin dashboard
header("Location: admin_dashboard.php");
exit();
?>
