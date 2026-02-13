<?php
session_start();
include "db.php";
header("Content-Type: application/json");

// Summary counts
$totalBorrowed = $conn->query("
    SELECT COUNT(*) as total 
    FROM borrow_transactions 
    WHERE status='borrowed'
")->fetch_assoc()['total'];

$pendingReturns = $conn->query("
    SELECT COUNT(*) as total 
    FROM borrow_transactions 
    WHERE status='pending_return'
")->fetch_assoc()['total'];


$overdueBooks = $conn->query("
    SELECT COUNT(*) as total 
    FROM borrow_transactions 
    WHERE status='borrowed' 
    AND due_date < NOW()
")->fetch_assoc()['total'];

// Get pending returns
$pending = [];
$stmt = $conn->prepare("
    SELECT bt.checkout_id, bt.student_name, bt.student_id, 
           bt.borrow_date, bt.due_date, 
           b.title, b.author
    FROM borrow_transactions bt
    JOIN books b ON bt.book_id = b.id
    WHERE bt.status = 'pending_return'
    ORDER BY bt.borrow_date DESC
");
$stmt->execute();
$result = $stmt->get_result();

while($row = $result->fetch_assoc()){
    $row['borrow_date'] = date("M d, Y", strtotime($row['borrow_date']));
    $row['due_date'] = date("M d, Y", strtotime($row['due_date']));
    $pending[] = $row;
}

// Get ALL borrowed books (not returned yet)
$allBorrowed = [];
$stmt2 = $conn->prepare("
    SELECT bt.checkout_id, bt.student_name, bt.student_id,
           bt.borrow_date, bt.due_date, bt.status,
           b.title, b.author
    FROM borrow_transactions bt
    JOIN books b ON bt.book_id = b.id
    WHERE bt.status != 'returned'
    ORDER BY bt.borrow_date DESC
");
$stmt2->execute();
$result2 = $stmt2->get_result();

while($row = $result2->fetch_assoc()){
    $row['borrow_date'] = date("M d, Y", strtotime($row['borrow_date']));
    $row['due_date'] = date("M d, Y", strtotime($row['due_date']));
    $allBorrowed[] = $row;
}

echo json_encode([
    "summary" => [
        "totalBorrowed" => $totalBorrowed,
        "pendingReturns" => $pendingReturns,
        "overdueBooks" => $overdueBooks
    ],
    "pending" => $pending,
    "allBorrowed" => $allBorrowed
]);
