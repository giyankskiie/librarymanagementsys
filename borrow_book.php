<?php
header('Content-Type: application/json');

// Enable error reporting (for development)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include "db.php";

// Get JSON input
$data = json_decode(file_get_contents("php://input"), true);

$bookId = intval($data['bookId'] ?? 0);
$studentName = trim($data['studentName'] ?? '');
$studentId = trim($data['studentId'] ?? '');

// Validate input
if (!$bookId || !$studentName || !$studentId) {
    echo json_encode([
        "success" => false,
        "message" => "Invalid input data"
    ]);
    exit;
}

$stmt = $conn->prepare("
    SELECT COUNT(*) as total
    FROM borrow_transactions
    WHERE student_name = ?
    AND student_id = ?
    AND status = 'borrowed'
");
$stmt->bind_param("ss", $studentName, $studentId);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

if ($row['total'] >= 5) {
    echo json_encode([
        "success" => false,
        "message" => "Borrow limit reached. You can only borrow 5 books at a time."
    ]);
    exit;
}

// Check if book exists and is available
$checkBook = $conn->prepare("SELECT status FROM books WHERE id = ?");
$checkBook->bind_param("i", $bookId);
$checkBook->execute();
$result = $checkBook->get_result();

if ($result->num_rows === 0) {
    echo json_encode([
        "success" => false,
        "message" => "Book not found"
    ]);
    exit;
}

$book = $result->fetch_assoc();

if (($book['status']) !== 'Available') {
    echo json_encode([
        "success" => false,
        "message" => "Book is not available"
    ]);
    exit;
}

// Check if book already has an active borrow transaction
$checkTxn = $conn->prepare("
    SELECT checkout_id FROM borrow_transactions 
    WHERE book_id = ? AND status = 'borrowed'
    LIMIT 1
");
$checkTxn->bind_param("i", $bookId);
$checkTxn->execute();
$checkTxn->store_result();

if ($checkTxn->num_rows > 0) {
    echo json_encode([
        "success" => false,
        "message" => "Book already borrowed"
    ]);
    exit;
}

// Insert borrow transaction
$dueDate = date("Y-m-d H:i:s", strtotime("+3 days"));

$stmt = $conn->prepare("
    INSERT INTO borrow_transactions
    (book_id, student_id, student_name, due_date, status)
    VALUES (?, ?, ?, ?, 'borrowed')
");
$stmt->bind_param("isss", $bookId, $studentId, $studentName, $dueDate);

if (!$stmt->execute()) {
    echo json_encode([
        "success" => false,
        "message" => "Failed to record transaction"
    ]);
    exit;
}

// Update book status
$update = $conn->prepare("UPDATE books SET status = 'borrowed' WHERE id = ?");
$update->bind_param("i", $bookId);
$update->execute();

// Return success
echo json_encode([
    "success" => true,
    "message" => "Book borrowed successfully"
]);
exit;
