<?php
include "db.php";
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['borrow_id'])) {
    $borrowId = intval($_POST['borrow_id']);

    // Get the book_id for this borrow
    $stmt = $conn->prepare("SELECT book_id FROM borrow_transactions WHERE checkout_id = ?");
    $stmt->bind_param("i", $borrowId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        $bookId = $row['book_id'];

        // Update borrow_transactions: mark as returned
        $stmtUpdate = $conn->prepare("UPDATE borrow_transactions SET status='returned', return_date=NOW() WHERE checkout_id=?");
        $stmtUpdate->bind_param("i", $borrowId);
        $stmtUpdate->execute();

        // Update books: mark as available
        $stmtBook = $conn->prepare("UPDATE books SET status='Available' WHERE id=?");
        $stmtBook->bind_param("i", $bookId);
        $stmtBook->execute();

        echo "<p style='text-align:center; color:#15803d; font-weight:600; margin-top:15px;'>Book successfully returned!</p>";
    } else {
        echo "<p style='text-align:center; color:#b91c1c; font-weight:600; margin-top:15px;'>Invalid transaction ID</p>";
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Return a Book</title>
<link rel="stylesheet" href="style.css">
<style>
body {
    font-family: 'Inter', sans-serif;
    min-height: 100vh;
    margin: 0;
    background: linear-gradient(135deg, #e0e7ff, #f0f4ff);
    display: flex;
    justify-content: center;
    padding: 50px 20px;
}

/* Main Wrapper */
.wrapper {
    width: 100%;
    max-width: 900px;
}

/* Header Section */
.header {
    text-align: center;
    margin-bottom: 40px;
}

.header h1 {
    font-size: 36px;
    font-weight: 800;
    color: #14249c;
}

.header p {
    font-size: 16px;
    color: #555;
}

/* Form Card */
.form-card {
    background: #ffffff;
    border-radius: 25px;
    padding: 40px;
    box-shadow: 0 20px 40px rgba(0,0,0,0.1);
    margin-bottom: 50px;
}

.form-card input {
    width: 100%;
    padding: 16px;
    margin-bottom: 20px;
    border-radius: 15px;
    border: 1px solid #ccc;
    font-size: 16px;
}

.form-card input:focus {
    outline: none;
    border-color: #14249c;
    box-shadow: 0 4px 12px rgba(20,36,156,0.2);
}

.form-card button {
    width: 100%;
    padding: 16px;
    border-radius: 20px;
    border: none;
    background: linear-gradient(135deg, #14249c, #aba30e);
    color: white;
    font-size: 16px;
    font-weight: 700;
    cursor: pointer;
}

.form-card button:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(0,0,0,0.25);
}

/* Back Button */
.back-btn {
    display: inline-block;
    margin-bottom: 20px;
    background: #aba30e;
    color: white;
    padding: 12px 22px;
    border-radius: 20px;
    text-decoration: none;
    font-weight: 600;
}

.back-btn:hover {
    background: #14249c;
}

/* Borrowed Books Cards */
.borrowed-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
    gap: 25px;
}

.book-card {
    background: #ffffff;
    border-radius: 20px;
    padding: 20px 25px;
    box-shadow: 0 15px 35px rgba(0,0,0,0.08);
    position: relative;
    transition: transform 0.3s, box-shadow 0.3s;
}

.book-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 25px 50px rgba(0,0,0,0.15);
}

.book-card h3 {
    color: #14249c;
    margin-bottom: 10px;
    font-size: 18px;
    font-weight: 700;
}

.book-card p {
    margin: 5px 0;
    color: #555;
    font-size: 14px;
}

.book-card .return-btn {
    display: block;
    width: 100%;
    margin-top: 15px;
    padding: 12px;
    border-radius: 20px;
    border: none;
    background: #aba30e;
    color: white;
    font-weight: 700;
    cursor: pointer;
    transition: transform 0.2s, box-shadow 0.2s;
}

.book-card .return-btn:hover {
    background: #14249c;
    transform: translateY(-2px);
}

/* Overdue Highlight */
.overdue {
    border-left: 6px solid #b91c1c;
}

.modal {
    position: fixed;
    top:0; left:0; width:100%; height:100%;
    background: rgba(0,0,0,0.5);
    display:flex; justify-content:center; align-items:center;
    z-index:1000;
}
.modal.hidden { display: none; }
.modal-content {
    background: #fff;
    padding: 30px;
    border-radius: 20px;
    width: 400px;
    max-width: 90%;
    text-align: center;
}
.modal-content h3 { margin-top:0; color:#14249c; }
.modal-content button {
    margin-top: 15px;
    padding: 10px 20px;
    border:none;
    background:#14249c;
    color:white;
    border-radius:10px;
    cursor:pointer;
}

</style>
</head>
<body>

<div class="wrapper">
    <div class="header">
        <h1>Return a Book</h1>
        <p>Enter your Full Name and Student ID to view your borrowed books</p>
    </div>

    <a href="index.php" class="back-btn">← Back to Main Page</a>

    <div class="form-card">
        <form method="POST">
            <input type="text" name="student_name" placeholder="Full Name" required>
            <input type="text" name="student_id" placeholder="Student ID #" required>
            <button type="submit">Show My Borrowed Books</button>
        </form>
    </div>

<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $studentName = trim($_POST['student_name']);
    $studentId = trim($_POST['student_id']);

    // Borrowed books
    $stmt = $conn->prepare("
        SELECT bt.checkout_id, bt.borrow_date, bt.due_date, bt.status, b.title, b.author
        FROM borrow_transactions bt
        JOIN books b ON b.id = bt.book_id
        WHERE bt.student_name = ? AND bt.student_id = ? AND bt.status = 'borrowed'
        ORDER BY bt.borrow_date DESC
    ");
    $stmt->bind_param("ss", $studentName, $studentId);
    $stmt->execute();
    $borrowedResult = $stmt->get_result();

    // Pending return books
    $stmt = $conn->prepare("
        SELECT bt.checkout_id, bt.borrow_date, bt.due_date, bt.status, b.title, b.author
        FROM borrow_transactions bt
        JOIN books b ON b.id = bt.book_id
        WHERE bt.student_name = ? AND bt.student_id = ? AND bt.status = 'pending_return'
        ORDER BY bt.borrow_date DESC
    ");
    $stmt->bind_param("ss", $studentName, $studentId);
    $stmt->execute();
    $pendingResult = $stmt->get_result();

    if ($borrowedResult->num_rows === 0 && $pendingResult->num_rows === 0) {
        echo "<p style='margin-top:20px; color:#b91c1c; font-weight:600; text-align:center;'>No borrowed books found for this student.</p>";
    } else {
        echo "<div class='borrowed-grid'>";

        // Show borrowed books
        while ($row = $borrowedResult->fetch_assoc()) {
            $borrowed = date("M d, Y", strtotime($row['borrow_date']));
            $due = date("M d, Y", strtotime($row['due_date']));
            $overdueClass = strtotime($row['due_date']) < time() ? 'overdue' : '';

            echo "<div class='book-card $overdueClass'>
                    <h3>".htmlspecialchars($row['title'])."</h3>
                    <p><strong>Author:</strong> ".htmlspecialchars($row['author'])."</p>
                    <p><strong>Borrowed:</strong> $borrowed</p>
                    <p><strong>Due:</strong> $due</p>
                    <button class='return-btn' onclick='returnBook(".$row['checkout_id'].")'>Return</button>
                  </div>";
        }

        // Show pending returns
        while ($row = $pendingResult->fetch_assoc()) {
            $borrowed = date("M d, Y", strtotime($row['borrow_date']));
            $due = date("M d, Y", strtotime($row['due_date']));
            $overdueClass = strtotime($row['due_date']) < time() ? 'overdue' : '';

            echo "<div class='book-card $overdueClass'>
                    <h3>".htmlspecialchars($row['title'])."</h3>
                    <p><strong>Author:</strong> ".htmlspecialchars($row['author'])."</p>
                    <p><strong>Borrowed:</strong> $borrowed</p>
                    <p><strong>Due:</strong> $due</p>
                    <p style='color:#f59e0b; font-weight:600;'>Return requested — waiting for admin approval</p>
                  </div>";
        }

        echo "</div>";
    }
}
?>

</div>

<!-- Return Modal -->
<div id="returnModal" class="modal hidden">
    <div class="modal-content">
        <h3>🔔 Return Request</h3>
        <p id="returnModalText">Processing your request...</p>
        <button onclick="closeReturnModal()">Close</button>
    </div>
</div>

<script src="script.js"></script>
<script>
// AJAX call to return_book.php
function returnBook(checkoutId) {
    fetch('return_book.php', {
        method: 'POST',
        headers: {'Content-Type':'application/json'},
        body: JSON.stringify({checkoutId: checkoutId})
    })
    .then(res => res.json())
    .then(data => {
        showReturnModal(data.message);
    })
    .catch(err => {
        showReturnModal('Return request failed');
    });
}
</script>
