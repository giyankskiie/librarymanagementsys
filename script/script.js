/* =============================
   LIBRARY MANAGEMENT SYSTEM JS
   ============================= */

/* ===== GLOBAL VARIABLES ===== */
let selectedBookId = null;

/* ===== UTILITY FUNCTIONS ===== */

// Calculate return date
function getReturnDate(days = 3) {
    const date = new Date();
    date.setDate(date.getDate() + days);
    return date.toLocaleDateString('en-US', {
        weekday: 'long',
        month: 'long',
        day: 'numeric',
        year: 'numeric'
    });
}

// Show/hide pages (if you implement multi-page UI)
function showPage(sectionId, title) {
    document.querySelectorAll(".page").forEach(p => p.classList.add("hidden"));
    const section = document.getElementById(sectionId);
    if (section) section.classList.remove("hidden");
    const pageTitle = document.getElementById("pageTitle");
    if (pageTitle) pageTitle.innerText = title;
}

// Logout (reloads public terminal)
function logout() {
    location.reload();
}


/* ===== MODAL HANDLING ===== */

// Open checkout modal for selected book
function borrowBook(button) {
    if (!button) return;

    selectedBookId = button.dataset.id;

    const modalTitle = document.getElementById("modalBookTitle");
    const modalAuthor = document.getElementById("modalBookAuthor");
    const modalDate = document.getElementById("modalBookDate");
    const modalCategory = document.getElementById("modalBookCategory");
    const returnText = document.getElementById("returnDateText");
    const checkoutModal = document.getElementById("checkoutModal");
    

    if (!modalTitle || !modalAuthor || !modalCategory || !modalDate || !returnText  || !checkoutModal) return;

    modalTitle.innerText = button.dataset.title || '';
    modalAuthor.innerText = button.dataset.author || '';
    modalDate.innerText = button.dataset.published || '';
    modalCategory.innerText = button.dataset.category || '';
    returnText.innerText = `Please return this book by: ${getReturnDate(3)}`;

    checkoutModal.classList.remove("hidden");
}

// Close checkout modal
function closeModal() {
    const modal = document.getElementById("checkoutModal");
    if (modal) {
        modal.classList.add("hidden");
    }
}

/* ===== BORROW / RETURN LOGIC ===== */

// Confirm borrowing a book
function confirmBorrow() {
    const nameInput = document.getElementById("borrowerName");
    const studentInput = document.getElementById("studentId");

    const name = nameInput?.value.trim();
    const studentId = studentInput?.value.trim();

    if (!name || !studentId) {
        alert("Please enter your Name and Student ID before confirming.");
        return;
    }

    fetch('borrow_book.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            bookId: selectedBookId,
            studentName: name,
            studentId: studentId
        })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            // Close checkout modal
            closeModal();

            // Clear input fields
            if (nameInput) nameInput.value = "";
            if (studentInput) studentInput.value = "";

            // Remove this book from Available Books grid
            const bookCard = document.querySelector(`.book-card button[data-id='${selectedBookId}']`)?.closest('.book-card');
            if (bookCard) bookCard.remove();


            alert("Book successfully borrowed!");

            // Refresh borrowed modal ONLY if it's currently open
            const borrowedModal = document.getElementById('borrowedModal');
            if (borrowedModal && !borrowedModal.classList.contains('hidden')) {
            refreshBorrowedModal();
}


            // Reset selectedBookId
            selectedBookId = null;
        } else {
            alert("Error: " + data.message);
        }
    })
    .catch(err => alert("Request failed: " + err));
}

// Return a borrowed book
function returnBook(checkoutId) {
    if (!checkoutId) return;
    if (!confirm("Return this book?")) return;

    fetch('return_book.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ checkoutId })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            alert("Book returned successfully!");

            // Remove from Borrowed Books modal
            const borrowedCard = document.querySelector(`.borrowed-grid button[data-checkout-id='${checkoutId}']`)?.closest('.book-card');
            if (borrowedCard) borrowedCard.remove();

            // Add back to Available Books grid
            const returnedBook = data.book;
            if (returnedBook) {
                const grid = document.querySelector('.book-grid');
                if (grid) {
                    const newCard = document.createElement('div');
                    newCard.classList.add('book-card');
                    newCard.innerHTML = `
                        <h4>${returnedBook.title}</h4>
                        <p>Author: ${returnedBook.author}</p>
                        <p class="status ok" id="status${returnedBook.id}">Available</p>
                        <button 
                            data-id="${returnedBook.id}" 
                            data-title="${returnedBook.title}" 
                            data-author="${returnedBook.author}" 
                            data-published="${returnedBook.published_year}"
                        >Borrow</button>
                    `;
                    grid.appendChild(newCard);

                    // Attach borrow button listener
                    const btn = newCard.querySelector('button');
                    if (btn) btn.addEventListener('click', function() {
                        borrowBook(this);
                    });
                }
            }
        } else {
            alert(data.message);
        }
    })
    .catch(err => alert("Error: " + err));
}

// Refresh borrowed books modal
function refreshBorrowedModal() {
    const borrowedModal = document.getElementById('borrowedModal');
    const borrowedContent = document.getElementById('borrowedContent');
    if (!borrowedModal || !borrowedContent) return;

    if (!borrowedModal.classList.contains('hidden')) {
        fetch('borrowed_books.php?ajax=1')
            .then(res => res.text())
            .then(html => {
                borrowedContent.innerHTML = html;

                // Attach return buttons
                document.querySelectorAll('.borrowed-grid button.return-btn').forEach(btn => {
                    btn.addEventListener('click', function(){
                        returnBook(this.dataset.checkoutId);
                    });
                });
            })
            .catch(err => alert("Failed to reload borrowed books: " + err));
    }
}

/* ===== MODAL OPEN/CLOSE FOR BORROWED BOOKS ===== */

function openBorrowedModal() {
    const borrowedModal = document.getElementById('borrowedModal');
    const borrowedContent = document.getElementById('borrowedContent');
    if (!borrowedModal || !borrowedContent) return;

    fetch('borrowed_books.php?ajax=1')
        .then(res => res.text())
        .then(html => {
            borrowedContent.innerHTML = html;
            borrowedModal.classList.remove('hidden');

            // Attach return buttons
            document.querySelectorAll('.borrowed-grid button.return-btn').forEach(btn => {
                btn.addEventListener('click', function(){
                    returnBook(this.dataset.checkoutId);
                });
            });
        })
        .catch(err => alert("Failed to load borrowed books: " + err));
}

function closeBorrowedModal() {
    const borrowedModal = document.getElementById('borrowedModal');
    if (borrowedModal) borrowedModal.classList.add('hidden');
}

function showPage(pageId) {
    // Hide all pages
    document.querySelectorAll('.page').forEach(p => p.classList.add('hidden'));
    // Show selected page
    document.getElementById(pageId).classList.remove('hidden');
}

function showReturnModal(message) {
    const modal = document.getElementById("returnModal");
    const text = document.getElementById("returnModalText");
    if (!modal || !text) return;

    text.innerText = message;
    modal.classList.remove("hidden");
}

function closeReturnModal() {
    const modal = document.getElementById("returnModal");
    if (modal) modal.classList.add("hidden");
}


// Load books dynamically
function loadBooks() {
    const search = document.getElementById('searchInput').value.trim();
    const category = document.getElementById('categoryFilter').value;
    const grid = document.getElementById('bookGrid');

    fetch(`fetch_books.php?search=${encodeURIComponent(search)}&category=${encodeURIComponent(category)}`)
        .then(res => res.text())
        .then(html => {
            grid.innerHTML = html;

            // Attach borrow listeners
            grid.querySelectorAll('.book-card button').forEach(btn => {
                btn.addEventListener('click', () => borrowBook(btn));
            });
        })
        .catch(err => console.error("Failed to fetch books:", err));
}


    // Optional: live search as user types
    document.getElementById('searchInput').addEventListener('input', loadBooks);
    document.getElementById('categoryFilter').addEventListener('change', loadBooks);


    /* ===== INITIALIZE EVENT LISTENERS ===== */

    document.addEventListener("DOMContentLoaded", () => {
        loadBooks(); // Load all books initially

        document.getElementById('searchInput').addEventListener('input', loadBooks);
        document.getElementById('categoryFilter').addEventListener('change', loadBooks);

        // All Borrowed Books button

        // Close modals
        const closeBorrowedBtn = document.querySelector("#borrowedModal .close-btn");
        if (closeBorrowedBtn) closeBorrowedBtn.addEventListener("click", closeBorrowedModal);

        const closeCheckoutBtn = document.querySelector("#checkoutModal button:last-child");
        if (closeCheckoutBtn) closeCheckoutBtn.addEventListener("click", closeModal);

    const bookGrid = document.querySelector('.book-grid');
    if (bookGrid) {
        bookGrid.addEventListener('click', function(e) {
            const btn = e.target.closest('button');
            if (!btn || btn.classList.contains('return-btn')) return;
            borrowBook(btn);
        });
    }




    });
    ;
        