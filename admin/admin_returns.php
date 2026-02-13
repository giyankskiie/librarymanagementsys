<?php
session_start();
include "db.php";

if (!isset($_SESSION["admin"])) {
    header("Location: admin_login.php");
    exit();
}
?>

<?php
include "db.php";

/* =============================
   APPROVE RETURN
============================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['approve_id'])) {

    $checkoutId = intval($_POST['approve_id']);

    // Get book_id
    $stmt = $conn->prepare("SELECT book_id FROM borrow_transactions WHERE checkout_id=?");
    $stmt->bind_param("i", $checkoutId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {

        $bookId = $row['book_id'];

        // Mark transaction as returned
        $stmtUpdate = $conn->prepare("UPDATE borrow_transactions 
                                      SET status='returned', return_date=NOW() 
                                      WHERE checkout_id=?");
        $stmtUpdate->bind_param("i", $checkoutId);
        $stmtUpdate->execute();

        // Make book available again
        $stmtBook = $conn->prepare("UPDATE books SET status='Available' WHERE id=?");
        $stmtBook->bind_param("i", $bookId);
        $stmtBook->execute();

        echo "<p style='color:green; font-weight:600;'>Return approved successfully!</p>";
    }
}

/* =============================
   SHOW PENDING RETURNS
============================= */

$stmt = $conn->prepare("
    SELECT bt.checkout_id, bt.student_name, bt.student_id,
           b.title, b.author, bt.borrow_date, bt.due_date
    FROM borrow_transactions bt
    JOIN books b ON b.id = bt.book_id
    WHERE bt.status = 'pending_return'
    ORDER BY bt.borrow_date DESC
");
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html>
<head>
<title>Admin - Pending Returns</title>
<link rel="stylesheet" href="style.css">
</head>
<body>

<h1>📚 Pending Return Approvals</h1>

<?php
if ($result->num_rows === 0) {
    echo "<p>No pending return requests.</p>";
} else {
    while ($row = $result->fetch_assoc()) {

        echo "<div style='background:white; padding:20px; margin-bottom:15px; border-radius:12px; box-shadow:0 5px 15px rgba(0,0,0,0.1);'>
                <h3>".htmlspecialchars($row['title'])."</h3>
                <p><strong>Author:</strong> ".htmlspecialchars($row['author'])."</p>
                <p><strong>Student:</strong> ".htmlspecialchars($row['student_name'])." (".$row['student_id'].")</p>
                <p><strong>Borrowed:</strong> ".$row['borrow_date']."</p>
                <p><strong>Due:</strong> ".$row['due_date']."</p>

                <form method='POST'>
                    <input type='hidden' name='approve_id' value='".$row['checkout_id']."'>
                    <button type='submit'>Approve Return</button>
                </form>
              </div>";
    }
}
?>

</body>
</html>
