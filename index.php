<?php
session_start();
// Include the database connection file
include('./config/db.php');

// Check if the connection was successful (optional, but good practice)
if (!isset($conn) || $conn->connect_error) {
    die("Database connection failed: " . (isset($conn) ? $conn->connect_error : "db.php file might have errors."));
}

// Initialize variables for filtering
$category_filter = isset($_GET['category']) ? $_GET['category'] : '';
$search_query = isset($_GET['search']) ? $_GET['search'] : '';
$min_price = isset($_GET['min_price']) ? $_GET['min_price'] : '';
$max_price = isset($_GET['max_price']) ? $_GET['max_price'] : '';
$sort_by = isset($_GET['sort_by']) ? $_GET['sort_by'] : 'title_asc';

// Fetch all books with author information
$sql = "SELECT books.book_id, books.title, books.price, books.description, books.cover_image, 
               authors.first_name, authors.last_name
        FROM books
        JOIN authors ON books.author_id = authors.author_id
        WHERE 1=1";

// Add search filter if provided
if (!empty($search_query)) {
    $sql .= " AND (books.title LIKE '%" . $conn->real_escape_string($search_query) . "%' 
              OR authors.first_name LIKE '%" . $conn->real_escape_string($search_query) . "%'
              OR authors.last_name LIKE '%" . $conn->real_escape_string($search_query) . "%')";
}

// Add price range filters if provided
if (!empty($min_price)) {
    $sql .= " AND books.price >= " . floatval($min_price);
}
if (!empty($max_price)) {
    $sql .= " AND books.price <= " . floatval($max_price);
}

// Add sorting options
switch ($sort_by) {
    case 'price_asc':
        $sql .= " ORDER BY books.price ASC";
        break;
    case 'price_desc':
        $sql .= " ORDER BY books.price DESC";
        break;
    case 'title_desc':
        $sql .= " ORDER BY books.title DESC";
        break;
    case 'title_asc':
    default:
        $sql .= " ORDER BY books.title ASC";
        break;
}

$result = $conn->query($sql);

// Define categories manually since we don't have a category column
$predefinedCategories = ['Horror', 'Fantasy', 'Mystery', 'Science Fiction', 'Romance'];
?>

<!DOCTYPE html>
<html lang="en" dir="ltr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Discover amazing books, collections, and flash sales at BookNest.">
    <meta name="keywords" content="books, flash sales, collections, BookNest">
    <meta name="author" content="BookNest Team">
    <link rel="icon" href="assets/icons/favicon.ico">
    <title>BookNest - Home</title>
    <link rel="icon" href="assets/icons/favicon.ico">
    <link rel="stylesheet" href="css/nav-footer-hero.css">
    <link rel="stylesheet" href="./css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
    <style>
        /* Cart Button Styling */
        .book-price-cart {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 10px;
        }

        .book-price-cart p {
            margin: 0;
            font-weight: bold;
            color: #333;
        }

        .add-to-cart-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background-color: #4a7c59;
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 4px;
            cursor: pointer;
            transition: all 0.3s ease;
            font-weight: 600;
            font-size: 0.9rem;
        }

        .add-to-cart-btn:hover {
            background-color: #3a6349;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .add-to-cart-btn:active {
            transform: translateY(0);
        }

        .add-to-cart-btn i {
            margin-right: 8px;
        }

        /* Toast Notification */
        .cart-toast {
            position: fixed;
            bottom: 20px;
            right: 20px;
            background-color: #4a7c59;
            color: white;
            padding: 12px 20px;
            border-radius: 6px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            display: flex;
            align-items: center;
            transform: translateY(100px);
            opacity: 0;
            transition: all 0.3s ease;
            z-index: 1000;
            max-width: 300px;
        }

        .cart-toast.show {
            transform: translateY(0);
            opacity: 1;
        }

        .cart-toast i {
            margin-right: 8px;
            font-size: 1.2rem;
        }

        /* Cart Button Responsive Adjustments */
        @media (max-width: 768px) {
            .book-price-cart {
                flex-direction: column;
                align-items: flex-start;
            }

            .add-to-cart-btn {
                margin-top: 8px;
                width: 100%;
            }
        }

        #recommendation-icon {
            position: fixed;
            bottom: 20px;
            right: 20px;
            background-color: #4e73df;
            border-radius: 30px;
            display: flex;
            align-items: center;
            padding: 10px 20px;
            cursor: pointer;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
            z-index: 1000;
            transition: all 0.3s ease;
        }

        #recommendation-icon:hover {
            transform: scale(1.05);
            background-color: #3a5fcf;
        }

        #recommendation-icon i {
            color: white;
            font-size: 20px;
            margin-right: 8px;
        }

        #recommendation-icon span {
            color: white;
            font-weight: 500;
            font-size: 14px;
            white-space: nowrap;
        }

        #chat-popup {
            position: fixed;
            bottom: 80px;
            right: 20px;
            width: 320px;
            height: 400px;
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
            display: flex;
            flex-direction: column;
            overflow: hidden;
            z-index: 999;
            transition: all 0.3s ease;
        }

        #chat-popup.hidden {
            transform: translateY(20px);
            opacity: 0;
            pointer-events: none;
        }

        .chat-header {
            background-color: #4e73df;
            color: white;
            padding: 12px 15px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .chat-header h3 {
            margin: 0;
            font-size: 16px;
        }

        #close-chat {
            background: none;
            border: none;
            color: white;
            font-size: 20px;
            cursor: pointer;
        }

        .chat-messages {
            flex: 1;
            overflow-y: auto;
            padding: 15px;
            display: flex;
            flex-direction: column;
        }

        .message {
            margin-bottom: 10px;
            max-width: 85%;
        }

        .message.bot {
            align-self: flex-start;
        }

        .message.user {
            align-self: flex-end;
        }

        .message-content {
            padding: 8px 12px;
            border-radius: 15px;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
        }

        .bot .message-content {
            background-color: #f0f2ff;
            color: #333;
        }

        .user .message-content {
            background-color: #4e73df;
            color: white;
        }

        .chat-input {
            display: flex;
            border-top: 1px solid #eee;
            padding: 10px;
        }

        .chat-input input {
            flex: 1;
            border: 1px solid #ddd;
            border-radius: 20px;
            padding: 8px 15px;
            outline: none;
        }

        .chat-input button {
            background-color: #4e73df;
            color: white;
            border: none;
            border-radius: 50%;
            width: 35px;
            height: 35px;
            margin-left: 10px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .chat-input button:hover {
            background-color: #3a5fcf;
        }

        @keyframes pulse {
            0% {
                transform: scale(1);
            }

            50% {
                transform: scale(1.05);
            }

            100% {
                transform: scale(1);
            }
        }

        .pulse {
            animation: pulse 2s infinite;
        }

        #backToTopBtn {
            position: fixed;
            bottom: 100px;
            right: 40px;
            z-index: 99;
            background-color: #2563EB;
            color: white;
            border: none;
            outline: none;
            padding: 12px 16px;
            border-radius: 50%;
            font-size: 18px;
            cursor: pointer;
            display: none;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            transition: opacity 0.3s ease, transform 0.3s ease;
        }

        #backToTopBtn:hover {
            background-color: #1E40AF;
            transform: translateY(-2px);
        }
    </style>
</head>

<body>

    <!-- Include Navbar -->
    <?php include('includes/navbar.php'); ?>

    <!-- Category Sticky Navbar -->
    <nav class="category-navbar" id="categoryNavbar">
        <span class="brand-name">Categories</span>
        <button class="menu-toggle" id="menuToggle">
            <i class="fas fa-bars"></i>
        </button>
        <ul class="category-menu" id="categoryMenu">
            <li><a href="#all" class="active"><i class="fas fa-book"></i> All Books</a></li>
            <li><a href="#horror"><i class="fas fa-ghost"></i> Horror</a></li>
            <li><a href="#fantasy"><i class="fas fa-dragon"></i> Fantasy</a></li>
            <li><a href="#mystery"><i class="fas fa-magnifying-glass"></i> Mystery</a></li>
            <li><a href="#science-fiction"><i class="fas fa-rocket"></i> Science Fiction</a></li>
            <li><a href="#romance"><i class="fas fa-heart"></i> Romance</a></li>
        </ul>
    </nav>

    <main>

        <!-- =========== HERO SECTION (Image Only) =========== -->
        <section class="hero-section" aria-label="Featured Book Banners">
            <div class="hero-slider" role="region" aria-label="Hero image slider">
                <div class="slide fade">
                    <img src="assets/images/hero1.jpeg" alt="New Releases" loading="lazy">
                </div>
                <div class="slide fade">
                    <img src="assets/images/hero2.jpeg" alt="E-Books" loading="lazy">
                </div>
                <div class="slide fade">
                    <img src="assets/images/hero3.jpeg" alt="Popular Picks" loading="lazy">
                </div>
            </div>

            <div class="hero-dots" role="tablist" aria-label="Slider navigation dots">
                <span class="dot" role="tab" onclick="currentSlide(1)" aria-label="Slide 1"></span>
                <span class="dot" role="tab" onclick="currentSlide(2)" aria-label="Slide 2"></span>
                <span class="dot" role="tab" onclick="currentSlide(3)" aria-label="Slide 3"></span>
            </div>
        </section>
        <!-- Add this HTML code to your index.php file, just before the closing </body> tag -->
        <div id="recommendation-icon">
            <i class="fas fa-book-reader"></i>
            <span>AI Book Recommendation</span>
        </div>

        <div id="chat-popup" class="hidden">
            <div class="chat-header">
                <h3>AI Book Recommendations</h3>
                <button id="close-chat">×</button>
            </div>
            <div class="chat-messages">
                <div class="message bot">
                    <div class="message-content">
                        Hello! I can help you with book recommendations. What kind of books do you enjoy?
                    </div>
                </div>
            </div>
            <div class="chat-input">
                <input type="text" id="user-message" placeholder="Type your message...">
                <button id="send-message"><i class="fas fa-paper-plane"></i></button>
            </div>
        </div>
        <!-- =========== SEARCH AND FILTER SECTION =========== -->
        <section class="filter-container" id="all">
            <form class="filter-form" method="GET" action="index.php">
                <div class="filter-group">
                    <label for="search">Search Books:</label>
                    <input type="text" id="search" name="search" placeholder="Title or author..."
                        value="<?php echo htmlspecialchars($search_query); ?>">
                </div>

                <div class="filter-group">
                    <label for="min_price">Min Price:</label>
                    <input type="number" id="min_price" name="min_price" min="0" step="0.01" placeholder="Min $"
                        value="<?php echo htmlspecialchars($min_price); ?>">
                </div>

                <div class="filter-group">
                    <label for="max_price">Max Price:</label>
                    <input type="number" id="max_price" name="max_price" min="0" step="0.01" placeholder="Max $"
                        value="<?php echo htmlspecialchars($max_price); ?>">
                </div>

                <div class="filter-group">
                    <label for="sort_by">Sort By:</label>
                    <select id="sort_by" name="sort_by">
                        <option value="title_asc" <?php echo ($sort_by == 'title_asc') ? 'selected' : ''; ?>>Title (A-Z)
                        </option>
                        <option value="title_desc" <?php echo ($sort_by == 'title_desc') ? 'selected' : ''; ?>>Title (Z-A)
                        </option>
                        <option value="price_asc" <?php echo ($sort_by == 'price_asc') ? 'selected' : ''; ?>>Price
                            (Low-High)</option>
                        <option value="price_desc" <?php echo ($sort_by == 'price_desc') ? 'selected' : ''; ?>>Price
                            (High-Low)</option>
                    </select>
                </div>

                <button type="submit" class="filter-submit">Apply Filters</button>
                <a href="index.php" class="filter-reset">Reset Filters</a>
            </form>
        </section>

        <!-- =========== BOOKS SECTION =========== -->
        <?php
        if (!empty($search_query) || !empty($min_price) || !empty($max_price)) {
            // Display filtered results
            ?>
            <section class="books-section">
                <h2>Search Results</h2>
                <div class="books-grid">
                    <?php
                    if ($result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            ?>
                            <div class="book-item">
                                <img src="assets/images/<?php echo htmlspecialchars($row['cover_image']); ?>"
                                    alt="<?php echo htmlspecialchars($row['title']); ?> cover" class="book-cover">
                                <h3><?php echo htmlspecialchars($row['title']); ?></h3>
                                <p>by <?php echo htmlspecialchars($row['first_name'] . ' ' . $row['last_name']); ?></p>
                                <p><?php echo htmlspecialchars($row['description']); ?></p>
                                <div class="book-price-cart">
                                    <p>Price: $<?php echo number_format($row['price'], 2); ?></p>
                                    <button class="add-to-cart-btn" data-book-id="<?php echo $row['book_id']; ?>">
                                        <i class="fas fa-cart-plus"></i> Add to Cart
                                    </button>
                                </div>
                            </div>
                            <?php
                        }
                    } else {
                        echo '<div class="no-results">No books found matching your criteria. Please try different filters.</div>';
                    }
                    ?>
                </div>
            </section>
            <?php
        } else {
            // Display books by categories
            if ($result->num_rows > 0) {
                // Fetch all books into an array
                $allBooks = [];
                while ($row = $result->fetch_assoc()) {
                    $allBooks[] = $row;
                }

                // Display predefined categories
                foreach ($predefinedCategories as $category) {
                    // Convert category name to id-friendly string
                    $categoryId = strtolower(str_replace(' ', '-', $category));

                    // Simulate category assignment (in a real application, you would have this in the database)
                    // Here we'll just use a simple algorithm to distribute books among categories
                    $categoryBooks = [];
                    foreach ($allBooks as $index => $book) {
                        // Assign books to categories based on the book title's first letter
                        // This is just a simple example - in a real app, you'd use actual category data
                        $firstLetter = strtoupper(substr($book['title'], 0, 1));

                        if ($category == 'Horror' && in_array($firstLetter, ['A', 'B', 'C', 'D', 'E'])) {
                            $categoryBooks[] = $book;
                        } else if ($category == 'Fantasy' && in_array($firstLetter, ['F', 'G', 'H', 'I', 'J'])) {
                            $categoryBooks[] = $book;
                        } else if ($category == 'Mystery' && in_array($firstLetter, ['K', 'L', 'M', 'N', 'O'])) {
                            $categoryBooks[] = $book;
                        } else if ($category == 'Science Fiction' && in_array($firstLetter, ['P', 'Q', 'R', 'S', 'T'])) {
                            $categoryBooks[] = $book;
                        } else if ($category == 'Romance' && in_array($firstLetter, ['U', 'V', 'W', 'X', 'Y', 'Z'])) {
                            $categoryBooks[] = $book;
                        }

                        // Limit to 4 books per category for display
                        if (count($categoryBooks) >= 4) {
                            break;
                        }
                    }

                    // Only display category if it has books
                    if (count($categoryBooks) > 0) {
                        ?>
                        <section id="<?php echo $categoryId; ?>" class="category-section">
                            <div class="category-header">
                                <h2><?php echo htmlspecialchars($category); ?></h2>
                                <a href="#" class="view-all">View All</a>
                            </div>
                            <div class="books-grid">
                                <?php
                                foreach ($categoryBooks as $book) {
                                    ?>
                                    <div class="book-item">
                                        <img src="assets/books/<?php echo htmlspecialchars($book['cover_image']); ?>"
                                            alt="<?php echo htmlspecialchars($book['title']); ?> cover" class="book-cover">
                                        <h3><?php echo htmlspecialchars($book['title']); ?></h3>
                                        <p>by <?php echo htmlspecialchars($book['first_name'] . ' ' . $book['last_name']); ?></p>
                                        <p><?php echo htmlspecialchars($book['description']); ?></p>
                                        <div class="book-price-cart">
                                            <p>Price: $<?php echo number_format($book['price'], 2); ?></p>
                                            <button class="add-to-cart-btn" data-book-id="<?php echo $book['book_id']; ?>">
                                                <i class="fas fa-cart-plus"></i> Add to Cart
                                            </button>
                                        </div>
                                    </div>
                                    <?php
                                }
                                ?>
                            </div>
                        </section>
                        <?php
                    }
                }
            } else {
                echo '<div class="no-results">No books found. Please add books to your database.</div>';
            }
        }

        ?>
        <!-- Back to Top Button -->
        <button id="backToTopBtn" title="Go to top">
            <i class="fas fa-arrow-up"></i>
        </button>

    </main>

    <!-- Include Footer -->
    <?php include('includes/footer.php'); ?>

    <!-- Scripts -->
    <script src="js/slider.js" defer></script>
    <script>
        const backToTopBtn = document.getElementById("backToTopBtn");

        // Show button after scrolling 100px
        window.onscroll = function () {
            if (document.body.scrollTop > 100 || document.documentElement.scrollTop > 100) {
                backToTopBtn.style.display = "block";
            } else {
                backToTopBtn.style.display = "none";
            }
        };

        // Scroll to top when clicked
        backToTopBtn.addEventListener("click", function () {
            window.scrollTo({
                top: 0,
                behavior: "smooth"
            });
        });
        // JavaScript for sticky navbar functionality
        document.addEventListener('DOMContentLoaded', function () {
            const categoryNavbar = document.getElementById('categoryNavbar');
            const menuToggle = document.getElementById('menuToggle');
            const categoryMenu = document.getElementById('categoryMenu');
            const menuLinks = document.querySelectorAll('.category-menu a');

            // Toggle mobile menu
            menuToggle.addEventListener('click', function () {
                categoryMenu.classList.toggle('active');
            });

            // Change active link on click
            menuLinks.forEach(link => {
                link.addEventListener('click', function (e) {
                    // Remove active class from all links
                    menuLinks.forEach(l => l.classList.remove('active'));
                    // Add active class to clicked link
                    this.classList.add('active');

                    // Close mobile menu after clicking
                    categoryMenu.classList.remove('active');
                });
            });

            // Add sticky class on scroll
            window.addEventListener('scroll', function () {
                if (window.scrollY > 100) {
                    categoryNavbar.classList.add('scrolled');
                } else {
                    categoryNavbar.classList.remove('scrolled');
                }
            });

            // Smooth scrolling for anchor links
            document.querySelectorAll('a[href^="#"]').forEach(anchor => {
                anchor.addEventListener('click', function (e) {
                    e.preventDefault();

                    const targetId = this.getAttribute('href').substring(1);
                    const targetElement = document.getElementById(targetId);

                    if (targetElement) {
                        window.scrollTo({
                            top: targetElement.offsetTop - 70, // Adjust for navbar height
                            behavior: 'smooth'
                        });
                    }
                });
            });

            // Cart functionality
            // Initialize cart if it doesn't exist
            if (!localStorage.getItem('cart')) {
                localStorage.setItem('cart', JSON.stringify([]));
            }

            // Add to cart button click handler
            document.querySelectorAll('.add-to-cart-btn').forEach(button => {
                button.addEventListener('click', function () {
                    const bookId = this.getAttribute('data-book-id');
                    const bookTitle = this.closest('.book-item').querySelector('h3').innerText;
                    const bookPrice = parseFloat(this.closest('.book-item').querySelector('.book-price-cart p').innerText.replace('Price: $', ''));

                    // Get current cart
                    let cart = JSON.parse(localStorage.getItem('cart'));

                    // Check if book is already in cart
                    const existingItem = cart.find(item => item.id === bookId);

                    if (existingItem) {
                        existingItem.quantity += 1;
                    } else {
                        cart.push({
                            id: bookId,
                            title: bookTitle,
                            price: bookPrice,
                            quantity: 1
                        });
                    }

                    // Save cart back to localStorage
                    localStorage.setItem('cart', JSON.stringify(cart));

                    // Show confirmation toast
                    showCartToast(bookTitle);
                });
            });

            function showCartToast(bookTitle) {
                const toast = document.createElement('div');
                toast.classList.add('cart-toast');
                toast.innerHTML = `<i class="fas fa-check-circle"></i> "${bookTitle}" added to cart!`;
                document.body.appendChild(toast);

                // Remove toast after animation
                setTimeout(() => {
                    toast.classList.add('show');
                }, 10);

                setTimeout(() => {
                    toast.classList.remove('show');
                    setTimeout(() => {
                        document.body.removeChild(toast);
                    }, 300);
                }, 3000);
            }
        });

        document.addEventListener('DOMContentLoaded', function () {
            const recommendationIcon = document.getElementById('recommendation-icon');
            const chatPopup = document.getElementById('chat-popup');
            const closeChat = document.getElementById('close-chat');
            const userMessageInput = document.getElementById('user-message');
            const sendMessageButton = document.getElementById('send-message');
            const chatMessages = document.querySelector('.chat-messages');

            // Variable to track if the user has interacted with the chat
            let userInteracted = false;

            // Auto open the chat popup after 5 seconds
            setTimeout(function () {
                chatPopup.classList.remove('hidden');
                recommendationIcon.classList.add('pulse');

                // Auto close after 3 seconds unless the user has interacted with it
                setTimeout(function () {
                    if (!userInteracted) {
                        chatPopup.classList.add('hidden');
                    }

                    // Stop pulsing after auto close
                    recommendationIcon.classList.remove('pulse');
                }, 3000);
            }, 5000);

            // Toggle chat popup when clicking the recommendation icon
            recommendationIcon.addEventListener('click', function () {
                chatPopup.classList.remove('hidden');
                recommendationIcon.classList.remove('pulse');
                userInteracted = true; // Mark as interacted when user clicks icon
            });

            // Close chat when clicking the close button
            closeChat.addEventListener('click', function () {
                chatPopup.classList.add('hidden');
                userInteracted = true; // Mark as interacted when user closes chat
            });

            // Mark as interacted when user clicks in input field
            userMessageInput.addEventListener('focus', function () {
                userInteracted = true;
            });

            // Send message function
            function sendMessage() {
                const userMessage = userMessageInput.value.trim();

                if (userMessage) {
                    // Add user message to chat
                    addMessage(userMessage, 'user');

                    // Clear input field
                    userMessageInput.value = '';

                    // Send response after a short delay (simulating processing)
                    setTimeout(function () {
                        const botResponse = "I'm sorry, this feature is currently under development. Please check back later!";
                        addMessage(botResponse, 'bot');
                    }, 500);

                    userInteracted = true; // Mark as interacted when user sends message
                }
            }

            // Add a message to the chat window
            function addMessage(text, sender) {
                const messageDiv = document.createElement('div');
                messageDiv.classList.add('message', sender);

                const messageContent = document.createElement('div');
                messageContent.classList.add('message-content');
                messageContent.textContent = text;

                messageDiv.appendChild(messageContent);
                chatMessages.appendChild(messageDiv);

                // Scroll to the bottom of chat messages
                chatMessages.scrollTop = chatMessages.scrollHeight;
            }

            // Send message when clicking the send button
            sendMessageButton.addEventListener('click', sendMessage);

            // Send message when pressing Enter key
            userMessageInput.addEventListener('keypress', function (e) {
                if (e.key === 'Enter') {
                    sendMessage();
                }
            });
        });
    </script>

</body>

</html>

<?php
// Close the connection
$conn->close();
?>