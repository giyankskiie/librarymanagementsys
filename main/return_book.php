<?php
include "db.php";
header("Content-Type: application/json");


$data = json_decode(file_get_contents("php://input"), true);
$checkoutId = intval($data['checkoutId'] ?? 0);

if (!$checkoutId) {
    echo json_encode([
        "success" => false,
        "message" => "Invalid transaction ID"
    ]);
    exit;
}

// Get transaction safely
$stmt = $conn->prepare("
    SELECT book_id, status 
    FROM borrow_transactions 
    WHERE checkout_id = ?
");
$stmt->bind_param("i", $checkoutId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode([
        "success" => false,
        "message" => "Transaction not found"
    ]);
    exit;
}

$transaction = $result->fetch_assoc();

if ($transaction['status'] === 'returned') {
    echo json_encode([
        "success" => false,
        "message" => "Book already returned"
    ]);
    exit;
}

// Start transaction (important!)
$conn->begin_transaction();

try {

    $updateTxn = $conn->prepare("
        UPDATE borrow_transactions 
        SET status = 'pending_return'
        WHERE checkout_id = ?
    ");
    $updateTxn->bind_param("i", $checkoutId);
    $updateTxn->execute();

    $conn->commit();

    echo json_encode([
        "success" => true,
        "message" => "Return request sent. Waiting for admin approval."
    ]);

} catch (Exception $e) {

    $conn->rollback();

    echo json_encode([
        "success" => false,
        "message" => "Return request failed"
    ]);
}