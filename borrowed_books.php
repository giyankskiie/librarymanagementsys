<?php
include "db.php";

$stmt = $conn->prepare("
    SELECT 
        bt.checkout_id,
        b.id AS book_id,
        b.title,
        b.author,
        bt.student_name,
        bt.borrow_date,
        bt.due_date
    FROM borrow_transactions bt
    JOIN books b ON b.id = bt.book_id
    WHERE bt.status = 'borrowed'
    ORDER BY bt.borrow_date DESC
");

$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()):
    $isOverdue = strtotime($row['due_date']) < time();
    $daysLate = $isOverdue 
        ? floor((time() - strtotime($row['due_date'])) / (60*60*24))
        : 0;
?>
<div class="book-card">
  <h4><?= htmlspecialchars($row['title']) ?></h4>
  <p>Author: <?= htmlspecialchars($row['author']) ?></p>
  <p>Borrowed by: <?= htmlspecialchars($row['student_name']) ?></p>
  <p>Borrow Date: <?= date("F j, Y", strtotime($row['borrow_date'])) ?></p>
  <p>Due Date: <?= date("F j, Y", strtotime($row['due_date'])) ?></p>

  <p style="color: <?= $isOverdue ? '#b91c1c' : '#15803d' ?>; font-weight:600;">
      <?= $isOverdue 
            ? "Overdue by {$daysLate} day(s)" 
            : "On Time" ?>
  </p>

  <button class="return-btn" data-checkout-id="<?= $row['checkout_id'] ?>">
      Return
  </button>
</div>
<?php endwhile; ?>
