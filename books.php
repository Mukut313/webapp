<?php
session_start();
// Include the database connection file
include('./config/db.php');

// Check if the connection was successful
if (!isset($conn) || $conn->connect_error) {
    die("Database connection failed: " . (isset($conn) ? $conn->connect_error : "db.php file might have errors."));
}

// Initialize variables for filtering
$search_query = isset($_GET['search']) ? $_GET['search'] : '';
$min_price = isset($_GET['min_price']) ? $_GET['min_price'] : '';
$max_price = isset($_GET['max_price']) ? $_GET['max_price'] : '';
$sort_by = isset($_GET['sort_by']) ? $_GET['sort_by'] : 'title_asc';
$category_filter = isset($_GET['category']) ? $_GET['category'] : '';

// Base SQL query to fetch all books with author information
$sql = "SELECT books.book_id, books.title, books.price, books.description, books.cover_image, 
               authors.first_name, authors.last_name
        FROM books
        JOIN authors ON books.author_id = authors.author_id
        WHERE 1=1";

// Add search filter if provided
if (!empty($search_query)) {
    $sql .= " AND (books.title LIKE '%" . $conn->real_escape_string($search_query) . "%' 
              OR authors.first_name LIKE '%" . $conn->real_escape_string($search_query) . "%'
              OR authors.last_name LIKE '%" . $conn->real_escape_string($search_query) . "%'
              OR books.description LIKE '%" . $conn->real_escape_string($search_query) . "%')";
}

// Add price range filters if provided
if (!empty($min_price)) {
    $sql .= " AND books.price >= " . floatval($min_price);
}
if (!empty($max_price)) {
    $sql .= " AND books.price <= " . floatval($max_price);
}

// Add category filter if provided
if (!empty($category_filter)) {
    // In a real application, you would filter by category ID from a categories table
    // For this example, we're simulating categories based on the book title's first letter
    if ($category_filter == 'Horror') {
        $sql .= " AND UPPER(SUBSTRING(books.title, 1, 1)) BETWEEN 'A' AND 'E'";
    } elseif ($category_filter == 'Fantasy') {
        $sql .= " AND UPPER(SUBSTRING(books.title, 1, 1)) BETWEEN 'F' AND 'J'";
    } elseif ($category_filter == 'Mystery') {
        $sql .= " AND UPPER(SUBSTRING(books.title, 1, 1)) BETWEEN 'K' AND 'O'";
    } elseif ($category_filter == 'Science Fiction') {
        $sql .= " AND UPPER(SUBSTRING(books.title, 1, 1)) BETWEEN 'P' AND 'T'";
    } elseif ($category_filter == 'Romance') {
        $sql .= " AND UPPER(SUBSTRING(books.title, 1, 1)) BETWEEN 'U' AND 'Z'";
    }
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

// Define categories manually
$predefinedCategories = ['Horror', 'Fantasy', 'Mystery', 'Science Fiction', 'Romance'];
?>

<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Browse our collection of books at BookNest.">
    <meta name="keywords" content="books, browse books, BookNest collection">
    <meta name="author" content="BookNest Team">
    <title>BookNest - Browse Books</title>
    <link rel="icon" href="assets/icons/favicon.ico">
    <link rel="stylesheet" href="css/nav-footer-hero.css">
    <link rel="stylesheet" href="./css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
    <style>
        /* Main Layout */
        .page-header {
            background-color: #f5f5f5;
            padding: 30px 0;
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 1px solid #e0e0e0;
        }
        
        .page-header h1 {
            margin: 0;
            color: #333;
            font-size: 2.2rem;
        }
        
        /* Filter Section */
        .filter-container {
            background-color: #f9f9f9;
            padding: 20px;
            margin-bottom: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
        }
        
        .filter-form {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            align-items: flex-end;
        }
        
        .filter-group {
            flex: 1 1 200px;
        }
        
        .filter-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 600;
            color: #555;
        }
        
        .filter-group input,
        .filter-group select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 0.95rem;
        }
        
        .filter-submit,
        .filter-reset {
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }
        
        .filter-submit {
            background-color: #4a7c59;
            color: white;
        }
        
        .filter-submit:hover {
            background-color: #3a6349;
        }
        
        .filter-reset {
            background-color: #f0f0f0;
            color: #333;
        }
        
        .filter-reset:hover {
            background-color: #e0e0e0;
        }
        
        /* Results Section */
        .books-section {
            padding: 20px 0;
        }
        
        .books-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 30px;
            margin-top: 20px;
        }
        
        .book-item {
            background-color: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            display: flex;
            flex-direction: column;
        }
        
        .book-item:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 15px rgba(0,0,0,0.15);
        }
        
        .book-cover {
            width: 100%;
            height: 300px;
            object-fit: cover;
        }
        
        .book-details {
            padding: 15px;
            flex: 1;
            display: flex;
            flex-direction: column;
        }
        
        .book-title {
            font-size: 1.1rem;
            margin: 0 0 5px;
            color: #333;
            font-weight: 700;
        }
        
        .book-author {
            font-size: 0.9rem;
            color: #666;
            margin: 0 0 15px;
        }
        
        .book-description {
            font-size: 0.85rem;
            color: #555;
            margin-bottom: 15px;
            flex-grow: 1;
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        
        .book-price-cart {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: auto;
        }
        
        .book-price {
            font-weight: bold;
            color: #333;
            margin: 0;
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
        }
        
        .add-to-cart-btn i {
            margin-right: 8px;
        }
        
        /* Category Pills */
        .category-filters {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-bottom: 20px;
        }
        
        .category-pill {
            padding: 8px 16px;
            background-color: #f0f0f0;
            border-radius: 20px;
            text-decoration: none;
            color: #333;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .category-pill:hover {
            background-color: #e0e0e0;
        }
        
        .category-pill.active {
            background-color: #4a7c59;
            color: white;
        }
        
        /* Responsive Adjustments */
        @media (max-width: 768px) {
            .books-grid {
                grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            }
            
            .filter-form {
                flex-direction: column;
            }
            
            .filter-group {
                flex: 1 1 100%;
            }
            
            .page-header h1 {
                font-size: 1.8rem;
            }
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
        
        /* No Results Message */
        .no-results {
            text-align: center;
            padding: 40px 0;
            color: #666;
            font-size: 1.1rem;
        }
    </style>
</head>
<body>

<!-- Include Navbar -->
<?php include('includes/navbar.php'); ?>

<!-- Page Header -->
<header class="page-header">
    <div class="container">
        <h1>Browse Our Collection</h1>
    </div>
</header>

<main class="container">
    <!-- Category Pills -->
    <div class="category-filters">
        <a href="book.php" class="category-pill <?php echo empty($category_filter) ? 'active' : ''; ?>">
            <i class="fas fa-book"></i> All Books
        </a>
        <?php foreach ($predefinedCategories as $category): ?>
        <a href="book.php?category=<?php echo urlencode($category); ?>" 
           class="category-pill <?php echo ($category_filter == $category) ? 'active' : ''; ?>">
            <?php 
            // Add appropriate icon for each category
            $icon = 'book';
            switch ($category) {
                case 'Horror': $icon = 'ghost'; break;
                case 'Fantasy': $icon = 'dragon'; break;
                case 'Mystery': $icon = 'magnifying-glass'; break;
                case 'Science Fiction': $icon = 'rocket'; break;
                case 'Romance': $icon = 'heart'; break;
            }
            ?>
            <i class="fas fa-<?php echo $icon; ?>"></i> <?php echo htmlspecialchars($category); ?>
        </a>
        <?php endforeach; ?>
    </div>

    <!-- Search and Filter Section -->
    <section class="filter-container">
        <form class="filter-form" method="GET" action="book.php">
            <!-- Keep category filter if already selected -->
            <?php if (!empty($category_filter)): ?>
            <input type="hidden" name="category" value="<?php echo htmlspecialchars($category_filter); ?>">
            <?php endif; ?>
            
            <div class="filter-group">
                <label for="search">Search Books:</label>
                <input type="text" id="search" name="search" placeholder="Title, author, or keywords..." value="<?php echo htmlspecialchars($search_query); ?>">
            </div>
            
            <div class="filter-group">
                <label for="min_price">Min Price:</label>
                <input type="number" id="min_price" name="min_price" min="0" step="0.01" placeholder="Min $" value="<?php echo htmlspecialchars($min_price); ?>">
            </div>
            
            <div class="filter-group">
                <label for="max_price">Max Price:</label>
                <input type="number" id="max_price" name="max_price" min="0" step="0.01" placeholder="Max $" value="<?php echo htmlspecialchars($max_price); ?>">
            </div>
            
            <div class="filter-group">
                <label for="sort_by">Sort By:</label>
                <select id="sort_by" name="sort_by">
                    <option value="title_asc" <?php echo ($sort_by == 'title_asc') ? 'selected' : ''; ?>>Title (A-Z)</option>
                    <option value="title_desc" <?php echo ($sort_by == 'title_desc') ? 'selected' : ''; ?>>Title (Z-A)</option>
                    <option value="price_asc" <?php echo ($sort_by == 'price_asc') ? 'selected' : ''; ?>>Price (Low-High)</option>
                    <option value="price_desc" <?php echo ($sort_by == 'price_desc') ? 'selected' : ''; ?>>Price (High-Low)</option>
                </select>
            </div>
            
            <button type="submit" class="filter-submit">
                <i class="fas fa-search"></i> Search
            </button>
            
            <?php 
            // Determine the reset link - if category is set, reset to that category only
            $resetLink = empty($category_filter) ? 'book.php' : 'book.php?category=' . urlencode($category_filter);
            ?>
            <a href="<?php echo $resetLink; ?>" class="filter-reset">
                <i class="fas fa-undo"></i> Reset Filters
            </a>
        </form>
    </section>

    <!-- Books Results Section -->
    <section class="books-section">
        <h2>
            <?php 
            if (!empty($category_filter)) {
                echo htmlspecialchars($category_filter) . ' Books';
            } elseif (!empty($search_query)) {
                echo 'Search Results for "' . htmlspecialchars($search_query) . '"';
            } else {
                echo 'All Books';
            }
            ?>
            <?php if ($result->num_rows > 0): ?>
            <small>(<?php echo $result->num_rows; ?> books found)</small>
            <?php endif; ?>
        </h2>
        
        <?php if ($result->num_rows > 0): ?>
        <div class="books-grid">
            <?php while ($row = $result->fetch_assoc()): ?>
            <div class="book-item">
                <img src="assets/images/<?php echo htmlspecialchars($row['cover_image']); ?>" 
                     alt="<?php echo htmlspecialchars($row['title']); ?> cover" 
                     class="book-cover">
                <div class="book-details">
                    <h3 class="book-title"><?php echo htmlspecialchars($row['title']); ?></h3>
                    <p class="book-author">by <?php echo htmlspecialchars($row['first_name'] . ' ' . $row['last_name']); ?></p>
                    <p class="book-description"><?php echo htmlspecialchars($row['description']); ?></p>
                    <div class="book-price-cart">
                        <p class="book-price">$<?php echo number_format($row['price'], 2); ?></p>
                        <button class="add-to-cart-btn" data-book-id="<?php echo $row['book_id']; ?>">
                            <i class="fas fa-cart-plus"></i> Add to Cart
                        </button>
                    </div>
                </div>
            </div>
            <?php endwhile; ?>
        </div>
        <?php else: ?>
        <div class="no-results">
            <i class="fas fa-book-open fa-3x"></i>
            <h3>No books found matching your criteria</h3>
            <p>Try adjusting your filters or search terms.</p>
        </div>
        <?php endif; ?>
    </section>
</main>

<!-- Include Footer -->
<?php include('includes/footer.php'); ?>

<!-- Scripts -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Cart functionality
    // Initialize cart if it doesn't exist
    if (!localStorage.getItem('cart')) {
        localStorage.setItem('cart', JSON.stringify([]));
    }
    
    // Add to cart button click handler
    document.querySelectorAll('.add-to-cart-btn').forEach(button => {
        button.addEventListener('click', function() {
            const bookId = this.getAttribute('data-book-id');
            const bookItem = this.closest('.book-item');
            const bookTitle = bookItem.querySelector('.book-title').innerText;
            const bookPrice = parseFloat(bookItem.querySelector('.book-price').innerText.replace('$', ''));
            
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
            
            // Update cart count in navbar if it exists
            const cartCountElement = document.querySelector('.cart-count');
            if (cartCountElement) {
                const totalItems = cart.reduce((sum, item) => sum + item.quantity, 0);
                cartCountElement.textContent = totalItems;
                cartCountElement.style.display = totalItems > 0 ? 'flex' : 'none';
            }
            
            // Show confirmation toast
            showCartToast(bookTitle);
        });
    });
    
    function showCartToast(bookTitle) {
        // Remove existing toast if present
        const existingToast = document.querySelector('.cart-toast');
        if (existingToast) {
            document.body.removeChild(existingToast);
        }
        
        const toast = document.createElement('div');
        toast.classList.add('cart-toast');
        toast.innerHTML = `<i class="fas fa-check-circle"></i> "${bookTitle}" added to cart!`;
        document.body.appendChild(toast);
        
        // Trigger animation
        setTimeout(() => {
            toast.classList.add('show');
        }, 10);
        
        // Hide and remove toast after delay
        setTimeout(() => {
            toast.classList.remove('show');
            setTimeout(() => {
                document.body.removeChild(toast);
            }, 300);
        }, 3000);
    }
});
</script>

</body>
</html>

<?php
// Close the connection
$conn->close();
?>