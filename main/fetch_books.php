<?php
include "db.php";

$search = $_GET['search'] ?? '';
$category = $_GET['category'] ?? '';

$sql = "SELECT * FROM books WHERE status='Available'";
$params = [];
$types = "";

if ($search) {
    $sql .= " AND (title LIKE ? OR author LIKE ?)";
    $param = "%$search%";
    $params[] = $param;
    $params[] = $param;
    $types .= "ss";
}

if ($category) {
    $sql .= " AND category = ?";
    $params[] = $category;
    $types .= "s";
}

$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "<p style='grid-column:1/-1; text-align:center; color:#b91c1c; font-weight:600;'>No books found.</p>";
} else {
    while ($book = $result->fetch_assoc()) {
        echo '<div class="book-card">';
        echo '<h4>' . htmlspecialchars($book['title']) . '</h4>';
        echo '<p>Author: ' . htmlspecialchars($book['author']) . '</p>';
        echo '<p>Category: ' . htmlspecialchars($book['category']) . '</p>';
        echo '<p>Published: ' . htmlspecialchars($book['published_year']) . '</p>';
        echo '<p>Status: ' . htmlspecialchars($book['status']) . '</p>';
        echo '<button '
            . 'data-id="' . $book['id'] . '" '
            . 'data-title="' . htmlspecialchars($book['title'], ENT_QUOTES) . '" '
            . 'data-author="' . htmlspecialchars($book['author'], ENT_QUOTES) . '" '
            . 'data-category="' . htmlspecialchars($book['category'], ENT_QUOTES) . '" '
            . 'data-published="' . htmlspecialchars($book['published_year'], ENT_QUOTES) . '"'
            . '>Borrow</button>';
        echo '</div>';
    }
}
