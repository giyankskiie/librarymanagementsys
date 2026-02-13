<?php
session_start();
include "db.php";

// Redirect if not logged in
if (!isset($_SESSION["admin"])) {
    header("Location: admin_login.php");
    exit();
}

// Fetch summary counts
$totalBorrowed = $conn->query("SELECT COUNT(*) as total FROM borrow_transactions WHERE status='borrowed'")->fetch_assoc()['total'];
$pendingReturns = $conn->query("SELECT COUNT(*) as total FROM borrow_transactions WHERE status='pending_return'")->fetch_assoc()['total'];
$overdueBooks = $conn->query("SELECT COUNT(*) as total FROM borrow_transactions WHERE status='borrowed' AND due_date < NOW()")->fetch_assoc()['total'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Dashboard</title>
<style>
/* Reset & Body */
* { box-sizing: border-box; margin: 0; padding: 0; }
body {
    font-family: 'Inter', sans-serif;
    background: #f4f6fb;
    min-height: 100vh;
}

/* Navbar */
.navbar {
    background: #14249c;
    color: white;
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 15px 30px;
}
.navbar h1 { font-size: 24px; }
.navbar a.logout-btn {
    background: #aba30e;
    color: white;
    padding: 10px 20px;
    border-radius: 8px;
    text-decoration: none;
    font-weight: 600;
    transition: 0.3s;
}
.navbar a.logout-btn:hover { background: #0f1a6f; }

/* Container */
.dashboard {
    max-width: 1200px; 
    margin: 30px auto;
    padding: 0 20px;
}

.dashboard h2 {
    margin-top: 40px;
}

/* Summary Cards General */
.summary-card {
    border-radius: 15px;
    padding: 20px;
    text-align: center;
    cursor: pointer;
    transition: transform 0.2s, box-shadow 0.2s;
    box-shadow: 0 10px 25px rgba(0,0,0,0.08);
}

/* Hover Effect */
.summary-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 20px 40px rgba(0,0,0,0.15);
}

/* Total Borrowed Books Card */
.summary-card.total-borrowed {
    background: #aba30e; /* Blue */
    color: white; /* Text color */
}

/* Overdue Books Card */
.summary-card.overdue {
    background: #aba30e; /* Red */
    color: white;
}

/* Pending Returns Card */
.summary-card.pending {
    color: white;
}

/* Headings and Numbers inside cards */
.summary-card h3 {
    font-size: 16px;
    margin-bottom: 8px;
}

.summary-card p {
    font-size: 28px;
    font-weight: 700;
    margin: 0;
}



.summary-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}
.summary-card {
    background: #fff;
    border-radius: 15px;
    padding: 20px;
    box-shadow: 0 15px 30px rgba(0,0,0,0.08);
    text-align: center;
    transition: transform 0.2s, box-shadow 0.2s;
}
.summary-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 25px 50px rgba(0,0,0,0.15);
}
.summary-card h3 { font-size: 16px; color: #555; margin-bottom: 8px; }
.summary-card p { font-size: 28px; color: #14249c; font-weight: 700; }

.dashboard h2 {
    color: #14249c;
    font-size: 28px;
    margin-bottom: 20px;
}

.card-grid {
    display: flex;
    flex-wrap: wrap;
    gap: 20px;
    justify-content: flex-start;
    padding-left: 50px;  /* small space from the left */
}

.return-card {
    background: #fff;
    border-radius: 15px;
    padding: 20px 25px;
    box-shadow: 0 15px 30px rgba(0,0,0,0.08);
    transition: transform 0.2s, box-shadow 0.2s;
    width: 320px;   /* fixed width */
    max-width: 100%;
}
.return-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 25px 50px rgba(0,0,0,0.15);
}
.return-card p { margin: 8px 0; font-size: 15px; color: #333; }
.return-card .status-badge {
    display: inline-block;
    padding: 4px 10px;
    border-radius: 12px;
    font-size: 13px;
    font-weight: 600;
    color: white;
    background: #f59e0b;
}

/* Buttons */
.return-card form {
    display: inline-block;
    margin-top: 12px;
    margin-right: 8px;
}
.return-card button {
    padding: 8px 15px;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    font-weight: 600;
    font-size: 14px;
    transition: 0.2s;
}
.approve-btn { background: #15803d; color: white; }
.approve-btn:hover { background: #047857; }
.reject-btn { background: #b91c1c; color: white; }
.reject-btn:hover { background: #991b1b; }

/* Responsive */
@media(max-width: 600px){
    .return-card { padding: 18px 15px; }
    .navbar h1 { font-size: 20px; }
    .return-card button { width: 100%; margin-bottom: 8px; }
    .return-card form { display: block; }
}
</style>
</head>
<body>

<!-- Navbar -->
<div class="navbar">
    <h1>Library Admin Dashboard 👑</h1>
    <a href="admin_logout.php" class="logout-btn">Logout</a>
</div>



<div class="dashboard">

    <!-- Summary -->
<div class="summary-grid">

    <div class="summary-card total-borrowed" onclick="filterBooks('all')">
        <h3>Total Borrowed Books</h3>
        <p id="totalBorrowed"><?= $totalBorrowed ?></p>
    </div>

    <div class="summary-card overdue" onclick="filterBooks('overdue')">
        <h3>Overdue Books</h3>
        <p id="overdueBooks"><?= $overdueBooks ?></p>
    </div>
    
    <div class="summary-card pending">
        <h3>Pending Returns</h3>
        <p id="pendingReturns"><?= $pendingReturns ?></p>
    </div>

</div>


    </div>

    <div id="filteredSection" style="display:none; margin-top:30px;">
        <h2 id="filteredTitle"></h2>
        <div id="filteredContainer" class="card-grid"></div>
    </div>

</div>

    <!-- Pending Return Requests -->
    <h2>Pending Return Requests</h2>
    <div id="pendingReturnsContainer" class="card-grid">
        <!-- Cards will be populated dynamically -->
    </div>
</div>

<script>
// Function to fetch pending returns and update the dashboard
async function fetchPendingReturns() {
    try {
        const res = await fetch('pending_returns.php');
        const data = await res.json();
        window.dashboardData = data;


        // Update summary
        document.getElementById('totalBorrowed').textContent = data.summary.totalBorrowed;
        document.getElementById('pendingReturns').textContent = data.summary.pendingReturns;
        document.getElementById('overdueBooks').textContent = data.summary.overdueBooks;

        // -------- Pending Returns --------
        const pendingContainer = document.getElementById('pendingReturnsContainer');
        pendingContainer.innerHTML = '';

        if(data.pending.length === 0){
            pendingContainer.innerHTML = "<p style='color:#555; font-style:italic;'>No pending return requests.</p>";
        } else {
            data.pending.forEach(r => {
    pendingContainer.innerHTML += `
        <div class="return-card">
            <p><strong>Student:</strong> ${r.student_name} (ID: ${r.student_id})</p>
            <p><strong>Book:</strong> ${r.title} by ${r.author}</p>
            <p><strong>Borrowed:</strong> ${r.borrow_date} | <strong>Due:</strong> ${r.due_date}</p>
            <span class="status-badge">Pending Approval</span>

            <div style="margin-top:12px;">
                <button class="approve-btn" onclick="approveReturn(${r.checkout_id})">
                    Approve
                </button>
                <button class="reject-btn" onclick="rejectReturn(${r.checkout_id})">
                    Reject
                </button>
            </div>
        </div>
    `;
});

        }

        // -------- ALL Borrowed Books --------
        const allContainer = document.getElementById('allBorrowedContainer');
        allContainer.innerHTML = '';

        if(data.allBorrowed.length === 0){
            allContainer.innerHTML = "<p style='color:#555; font-style:italic;'>No active borrowed books.</p>";
        } else {
            data.allBorrowed.forEach(r => {

                let statusColor = "#2563eb"; // borrowed
                if(r.status === "pending_return") statusColor = "#f59e0b";
                if(r.status === "borrowed" && new Date(r.due_date) < new Date())
                    statusColor = "#b91c1c";

                allContainer.innerHTML += `
                    <div class="return-card">
                        <p><strong>Student:</strong> ${r.student_name} (ID: ${r.student_id})</p>
                        <p><strong>Book:</strong> ${r.title} by ${r.author}</p>
                        <p><strong>Borrowed:</strong> ${r.borrow_date}</p>
                        <p><strong>Due:</strong> ${r.due_date}</p>
                        <p><strong>Status:</strong> 
                            <span style="color:${statusColor}; font-weight:600;">
                                ${r.status}
                            </span>
                        </p>
                    </div>
                `;
            });
        }

    } catch(err) {
        console.error(err);
    }
}


// Initial fetch
fetchPendingReturns();

let currentFilter = null;

function filterBooks(type) {
    const section = document.getElementById("filteredSection");
    const container = document.getElementById("filteredContainer");
    const title = document.getElementById("filteredTitle");

    // Toggle off if same filter clicked
    if (currentFilter === type) {
        section.style.display = "none";
        currentFilter = null;
        return;
    }

    currentFilter = type;
    section.style.display = "block";
    section.scrollIntoView({ behavior: "smooth" });

    container.innerHTML = "";

    let books = [];

    if (type === "all") {
        title.innerText = "All Borrowed Books";
        books = window.dashboardData.allBorrowed;
    }

    if (type === "pending") {
        title.innerText = "Pending Return Requests";
        books = window.dashboardData.pending;
    }

    if (type === "overdue") {
        title.innerText = "Overdue Books";
        books = window.dashboardData.allBorrowed.filter(b => 
            b.status === "borrowed" && new Date(b.due_date) < new Date()
        );
    }

    if (books.length === 0) {
        container.innerHTML = "<p style='color:#555;'>No records found.</p>";
        return;
    }

    books.forEach(r => {
        let statusColor = "#2563eb";
        if(r.status === "pending_return") statusColor = "#f59e0b";
        if(r.status === "borrowed" && new Date(r.due_date) < new Date())
            statusColor = "#b91c1c";

        container.innerHTML += `
            <div class="return-card">
                <p><strong>Student:</strong> ${r.student_name} (ID: ${r.student_id})</p>
                <p><strong>Book:</strong> ${r.title} by ${r.author}</p>
                <p><strong>Borrowed:</strong> ${r.borrow_date}</p>
                <p><strong>Due:</strong> ${r.due_date}</p>
                <p><strong>Status:</strong> 
                    <span style="color:${statusColor}; font-weight:600;">
                        ${r.status}
                    </span>
                </p>
            </div>
        `;
    });
}

async function approveReturn(id) {
    await fetch('approve_return.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'checkout_id=' + id
    });

    fetchPendingReturns(); // Refresh instantly
}

async function rejectReturn(id) {
    await fetch('reject_return.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'checkout_id=' + id
    });

    fetchPendingReturns(); // Refresh instantly
}


// Poll every 5 seconds
setInterval(fetchPendingReturns, 5000);
</script>

</body>
</html>
