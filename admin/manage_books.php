<?php
session_start();
include('../config/db.php');

// Check if the connection was successful
if (!isset($conn) || $conn->connect_error) {
    die("Database connection failed: " . (isset($conn) ? $conn->connect_error : "db.php file might have errors."));
}

// Handle Delete Operation
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $book_id = intval($_GET['delete']);
    
    // Delete the book
    $delete_query = "DELETE FROM books WHERE book_id = ?";
    $stmt = $conn->prepare($delete_query);
    $stmt->bind_param("i", $book_id);
    
    if ($stmt->execute()) {
        $_SESSION['message'] = "Book deleted successfully!";
        $_SESSION['message_type'] = "success";
    } else {
        $_SESSION['message'] = "Error deleting book: " . $conn->error;
        $_SESSION['message_type'] = "danger";
    }
    
    $stmt->close();
    // Redirect to avoid form resubmission
    header("Location: manage_books.php");
    exit();
}

// Pagination Setup
$records_per_page = 10;
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$offset = ($page - 1) * $records_per_page;

// Search functionality
$search = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';
$search_condition = '';
if (!empty($search)) {
    $search_condition = " WHERE b.title LIKE '%$search%' OR b.isbn LIKE '%$search%' OR b.description LIKE '%$search%'";
}

// First, let's check the structure of the tables to avoid errors
$author_name_field = "author_id"; // Default fallback field
$category_name_field = "category_id"; // Default fallback field

// Check if authors table has a name field
$check_authors = $conn->query("SHOW COLUMNS FROM authors LIKE 'name'");
if ($check_authors && $check_authors->num_rows > 0) {
    $author_name_field = "name";
}

// Check if categories table has a name field
$check_categories = $conn->query("SHOW COLUMNS FROM categories LIKE 'name'");
if ($check_categories && $check_categories->num_rows > 0) {
    $category_name_field = "name";
}

// Get books with pagination and search - dynamically handle column names
$sql = "SELECT b.book_id, b.title, b.isbn, b.price, b.publish_date, b.description,
               b.stock_quantity, b.cover_image, b.created_at, b.updated_at,
               b.author_id, b.category_id";

// Add author name if the field exists               
if ($author_name_field == "name") {
    $sql .= ", a.$author_name_field as author_name";
} else {
    $sql .= ", b.author_id as author_name";
}

// Add category name if the field exists
if ($category_name_field == "name") {
    $sql .= ", c.$category_name_field as category_name";
} else {
    $sql .= ", b.category_id as category_name";
}

$sql .= " FROM books b";

// Join with authors if the name field exists
if ($author_name_field == "name") {
    $sql .= " LEFT JOIN authors a ON b.author_id = a.author_id";
}

// Join with categories if the name field exists
if ($category_name_field == "name") {
    $sql .= " LEFT JOIN categories c ON b.category_id = c.category_id";
}

$sql .= " $search_condition
          ORDER BY b.title ASC
          LIMIT $offset, $records_per_page";

$result = $conn->query($sql);

// Count total records for pagination (simplified query without joins if needed)
$count_sql = "SELECT COUNT(*) as total FROM books b $search_condition";
$count_result = $conn->query($count_sql);
$total_records = $count_result->fetch_assoc()['total'];
$total_pages = ceil($total_records / $records_per_page);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Books - Bookstore Admin</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f5f5f5;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            background-color: #fff;
            padding: 15px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .header h1 {
            margin: 0;
            color: #333;
        }
        
        .btn {
            padding: 10px 15px;
            border-radius: 5px;
            text-decoration: none;
            font-weight: bold;
            display: inline-block;
        }
        
        .btn-primary {
            background-color: #4CAF50;
            color: white;
        }
        
        .btn-secondary {
            background-color: #2196F3;
            color: white;
        }
        
        .btn-danger {
            background-color: #f44336;
            color: white;
        }
        
        .search-container {
            display: flex;
            margin-bottom: 20px;
            background-color: #fff;
            padding: 15px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .search-container form {
            display: flex;
            width: 100%;
        }
        
        .search-container input {
            flex-grow: 1;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px 0 0 5px;
            outline: none;
        }
        
        .search-container button {
            padding: 10px 15px;
            background-color: #2196F3;
            color: white;
            border: none;
            border-radius: 0 5px 5px 0;
            cursor: pointer;
        }
        
        .table-container {
            background-color: #fff;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            overflow-x: auto;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        table th, table td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        
        table th {
            background-color: #f2f2f2;
            color: #333;
        }
        
        .action-buttons {
            display: flex;
            gap: 10px;
        }
        
        .pagination {
            display: flex;
            justify-content: center;
            margin-top: 20px;
        }
        
        .pagination a, .pagination span {
            display: inline-block;
            padding: 8px 16px;
            text-decoration: none;
            color: #333;
            background-color: #fff;
            border: 1px solid #ddd;
            margin: 0 4px;
            border-radius: 4px;
        }
        
        .pagination a:hover {
            background-color: #ddd;
        }
        
        .pagination .active {
            background-color: #2196F3;
            color: white;
            border: 1px solid #2196F3;
        }
        
        .message {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 8px;
        }
        
        .message-success {
            background-color: #dff0d8;
            color: #3c763d;
        }
        
        .message-danger {
            background-color: #f2dede;
            color: #a94442;
        }
        
        .empty-state {
            text-align: center;
            padding: 40px 0;
            color: #666;
        }
        
        .empty-state p {
            font-size: 18px;
            margin-bottom: 20px;
        }
        
        .book-cover {
            width: 50px;
            height: 70px;
            object-fit: cover;
            border-radius: 4px;
        }
        
        .stock-low {
            color: #f44336;
            font-weight: bold;
        }
        
        .stock-ok {
            color: #4CAF50;
        }
        
        .description-cell {
            max-width: 200px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        
        .filter-controls {
            display: flex;
            margin-bottom: 20px;
            gap: 10px;
        }
        
        .filter-controls select {
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            outline: none;
        }
    </style>
</head>
<body>
    <div class="container">
        <header class="header">
            <h1>Manage Books</h1>
            <div>
                <a href="../forms/add_book.php" class="btn btn-primary">Add New Book</a>
                <a href="dashboard.php" class="btn btn-secondary">Dashboard</a>
            </div>
        </header>
        
        <?php if (isset($_SESSION['message'])): ?>
            <div class="message message-<?php echo $_SESSION['message_type']; ?>">
                <?php 
                    echo $_SESSION['message']; 
                    unset($_SESSION['message']);
                    unset($_SESSION['message_type']);
                ?>
            </div>
        <?php endif; ?>
        
        <div class="search-container">
            <form action="" method="GET">
                <input type="text" name="search" placeholder="Search by title, ISBN or description..." value="<?php echo htmlspecialchars($search); ?>">
                <button type="submit">Search</button>
            </form>
        </div>
        
        <div class="table-container">
            <?php if ($result && $result->num_rows > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Cover</th>
                            <th>Title</th>
                            <th>Author ID</th>
                            <th>Category ID</th>
                            <th>ISBN</th>
                            <th>Price</th>
                            <th>Stock</th>
                            <th>Year</th>
                            <th>Last Updated</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo $row['book_id']; ?></td>
                                <td>
                                    <?php if (!empty($row['cover_image'])): ?>
                                        <img src="../uploads/covers/<?php echo htmlspecialchars($row['cover_image']); ?>" alt="Cover" class="book-cover">
                                    <?php else: ?>
                                        <img src="../assets/images/default_cover.jpg" alt="Default Cover" class="book-cover">
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars($row['title']); ?></td>
                                <td><?php echo $row['author_id']; ?></td>
                                <td><?php echo $row['category_id']; ?></td>
                                <td><?php echo htmlspecialchars($row['isbn']); ?></td>
                                <td>$<?php echo number_format($row['price'], 2); ?></td>
                                <td class="<?php echo ($row['stock_quantity'] < 5) ? 'stock-low' : 'stock-ok'; ?>">
                                    <?php echo $row['stock_quantity']; ?>
                                </td>
                                <td><?php echo $row['publish_date'] ? date('Y', strtotime($row['publish_date'])) : 'N/A'; ?></td>
                                <td><?php echo date('M d, Y', strtotime($row['updated_at'])); ?></td>
                                <td class="action-buttons">
                                    <a href="../forms/edit_book.php?id=<?php echo $row['book_id']; ?>" class="btn btn-secondary">Edit</a>
                                    <a href="book_details.php?id=<?php echo $row['book_id']; ?>" class="btn btn-secondary">View</a>
                                    <a href="manage_books.php?delete=<?php echo $row['book_id']; ?>" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this book?')">Delete</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="empty-state">
                    <p>No books found.</p>
                    <a href="../forms/add_book.php" class="btn btn-primary">Add Your First Book</a>
                </div>
            <?php endif; ?>
        </div>
        
        <?php if ($total_pages > 1): ?>
            <div class="pagination">
                <?php if ($page > 1): ?>
                    <a href="?page=1<?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>">&laquo; First</a>
                    <a href="?page=<?php echo $page - 1; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>">&lsaquo; Prev</a>
                <?php endif; ?>
                
                <?php
                $start_page = max(1, $page - 2);
                $end_page = min($total_pages, $page + 2);
                
                for ($i = $start_page; $i <= $end_page; $i++):
                ?>
                    <?php if ($i == $page): ?>
                        <span class="active"><?php echo $i; ?></span>
                    <?php else: ?>
                        <a href="?page=<?php echo $i; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>"><?php echo $i; ?></a>
                    <?php endif; ?>
                <?php endfor; ?>
                
                <?php if ($page < $total_pages): ?>
                    <a href="?page=<?php echo $page + 1; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>">Next &rsaquo;</a>
                    <a href="?page=<?php echo $total_pages; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>">Last &raquo;</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>

<?php $conn->close(); ?>