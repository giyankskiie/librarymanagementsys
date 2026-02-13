<?php include "db.php"; ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Available Books</title>
<link rel="stylesheet" href="style.css">
<style>
body {
    font-family: 'Inter', sans-serif;
    background: #f0f4ff;
    margin: 0;
    padding: 0;
}
header {
    background: #14249c;
    color: white;
    padding: 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}
header h1 {
    margin: 0;
}
header button {
    padding: 10px 20px;
    border: none;
    border-radius: 8px;
    background: #aba30e;
    color: white;
    cursor: pointer;
    font-weight: 600;
    transition: 0.3s;
}
header button:hover { background: #f5c200; }

.main {
    max-width: 1000px;
    margin: 30px auto;
    padding: 0 20px;
}

/* Controls */
.controls {
    display: flex;
    gap: 15px;
    margin-bottom: 25px;
    flex-wrap: wrap;
}
.controls input, .controls select {
    flex: 1;
    padding: 12px 15px;
    border-radius: 12px;
    border: 1px solid #ccc;
    font-size: 15px;
}
.controls button {
    padding: 12px 20px;
    border-radius: 12px;
    border: none;
    background: #14249c;
    color: white;
    cursor: pointer;
    font-weight: 600;
    transition: 0.3s;
}
.controls button:hover {
    background: #0f1c80;
}

/* Book Grid */
.book-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 20px;
}
.book-card {
    background: #fff;
    border-radius: 20px;
    padding: 20px;
    box-shadow: 0 8px 20px rgba(0,0,0,0.08);
    text-align: center;
    transition: transform 0.2s, box-shadow 0.2s;
}
.book-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 15px 30px rgba(0,0,0,0.15);
}
.book-card h4 {
    margin: 0 0 10px 0;
    color: #14249c;
}
.book-card p {
    margin: 5px 0;
}
.book-card button {
    margin-top: 10px;
    padding: 10px 15px;
    border-radius: 12px;
    border: none;
    background: #aba30e;
    color: white;
    cursor: pointer;
    font-weight: 600;
    transition: 0.3s;
}
.book-card button:hover {
    background: #14249c;
}

/* Modal */
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
}
.modal-content h3 { margin-top:0; color:#14249c; }
.modal-actions { display:flex; gap:10px; justify-content: flex-end; margin-top: 20px; }
.modal-actions button { flex:1; }
</style>
</head>
<body>

<header>
    <h1>📚 Available Books</h1>
    <button onclick="location.href='index.php'">← Back</button>
</header>

<div class="main">
    <!-- Search & Filter -->
    <div class="controls">
    <input type="text" id="searchInput" placeholder="Search by title or author...">
    <select id="categoryFilter">
        <option value="">All Categories</option>
        <option value="Fiction">Fiction</option>
        <option value="Non-Fiction">Non-Fiction</option>
        <option value="Science">Science</option>
        <option value="History">History</option>
        <option value="Fantasy">Fantasy</option>
    </select>
</div>

    <div class="book-grid" id="bookGrid">

    <?php
    // Prepare SQL with search and category
    $search = $_GET['search'] ?? '';
    $category = $_GET['category'] ?? '';

    $sql = "SELECT * FROM books WHERE status='Available'";
    $params = [];
    $types = "";

    if (!empty($search)) {
        $sql .= " AND (title LIKE ? OR author LIKE ?)";
        $param = "%$search%";
        $params[] = $param;
        $params[] = $param;
        $types .= "ss";
    }
    if (!empty($category)) {
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
        while ($book = $result->fetch_assoc()):
    ?>
        <div class="book-card">
            <h4><?= htmlspecialchars($book['title']) ?></h4>
            <p>Author: <?= htmlspecialchars($book['author']) ?></p>
            <p>Category: <?= htmlspecialchars($book['category']) ?></p>
            <p>Published: <?= htmlspecialchars($book['published_year']) ?></p>
            <p>Status: <?= htmlspecialchars($book['status']) ?></p>
            
            <button 
                data-id="<?= $book['id'] ?>" 
                data-title="<?= htmlspecialchars($book['title'], ENT_QUOTES) ?>" 
                data-author="<?= htmlspecialchars($book['author'], ENT_QUOTES) ?>" 
                data-category="<?= htmlspecialchars($book['category'], ENT_QUOTES) ?>"
                data-published="<?= htmlspecialchars($book['published_year'], ENT_QUOTES) ?>"
                onclick="borrowBook(this)"
            >Borrow</button>
        </div>
    <?php
        endwhile;
    }
    ?>
    </div>
</div>

<!-- Borrow Modal -->
<div id="checkoutModal" class="modal hidden">
    <div class="modal-content">
        <h3>📚 Book Checkout</h3>
        <p id="returnDateText"></p>
        <div id="bookDetails" style="margin-bottom:15px;">
            <p><strong>Title:</strong> <span id="modalBookTitle"></span></p>
            <p><strong>Author:</strong> <span id="modalBookAuthor"></span></p>
            <p><strong>Category:</strong> <span id="modalBookCategory"></span></p>
            <p><strong>Date Published:</strong> <span id="modalBookDate"></span></p>
        </div>
        <input type="text" id="borrowerName" placeholder="Full Name">
        <input type="text" id="studentId" placeholder="Student ID #">
        <div class="modal-actions">
            <button onclick="confirmBorrow()">Confirm Borrow</button>
            <button onclick="closeModal()">Cancel</button>
        </div>
    </div>
</div>

<script src="script.js"></script>
</body>
</html>
