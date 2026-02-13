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

    // Get the book_id
    $stmt = $conn->prepare("SELECT book_id FROM borrow_transactions WHERE checkout_id = ?");
    $stmt->bind_param("i", $checkoutId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        $bookId = $row['book_id'];

        // Mark transaction as returned
        $stmtUpdate = $conn->prepare("UPDATE borrow_transactions SET status='returned', return_date=NOW() WHERE checkout_id=?");
        $stmtUpdate->bind_param("i", $checkoutId);
        $stmtUpdate->execute();

        // Make book available
        $stmtBook = $conn->prepare("UPDATE books SET status='Available' WHERE id=?");
        $stmtBook->bind_param("i", $bookId);
        $stmtBook->execute();

        $_SESSION['message'] = "Return approved successfully!";
    } else {
        $_SESSION['message'] = "Invalid transaction ID.";
    }
} else {
    $_SESSION['message'] = "No transaction ID provided.";
}

// Redirect back to admin dashboard
header("Location: admin_dashboard.php");
exit();
?>
