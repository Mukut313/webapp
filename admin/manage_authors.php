<?php
session_start();
include('../config/db.php');

// Check if the connection was successful
if (!isset($conn) || $conn->connect_error) {
    die("Database connection failed: " . (isset($conn) ? $conn->connect_error : "db.php file might have errors."));
}

// Handle Delete Operation
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $author_id = intval($_GET['delete']);
    
    // Check if there are books associated with this author
    $check_books = "SELECT COUNT(*) as book_count FROM books WHERE author_id = ?";
    $check_stmt = $conn->prepare($check_books);
    $check_stmt->bind_param("i", $author_id);
    $check_stmt->execute();
    $result = $check_stmt->get_result();
    $book_count = $result->fetch_assoc()['book_count'];
    $check_stmt->close();
    
    if ($book_count > 0) {
        $_SESSION['message'] = "Cannot delete author: There are $book_count books associated with this author.";
        $_SESSION['message_type'] = "danger";
    } else {
        // Delete the author
        $delete_query = "DELETE FROM authors WHERE author_id = ?";
        $stmt = $conn->prepare($delete_query);
        $stmt->bind_param("i", $author_id);
        
        if ($stmt->execute()) {
            $_SESSION['message'] = "Author deleted successfully!";
            $_SESSION['message_type'] = "success";
        } else {
            $_SESSION['message'] = "Error deleting author: " . $conn->error;
            $_SESSION['message_type'] = "danger";
        }
        
        $stmt->close();
    }
    
    // Redirect to avoid form resubmission
    header("Location: manage_authors.php");
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
    $search_condition = " WHERE a.first_name LIKE '%$search%' OR a.last_name LIKE '%$search%' OR a.bio LIKE '%$search%'";
}

// Get authors with pagination and search
$sql = "SELECT a.*, COUNT(b.book_id) as book_count
        FROM authors a
        LEFT JOIN books b ON a.author_id = b.author_id
        $search_condition
        GROUP BY a.author_id
        ORDER BY a.last_name ASC, a.first_name ASC
        LIMIT $offset, $records_per_page";

$result = $conn->query($sql);

// Count total records for pagination
$count_sql = "SELECT COUNT(*) as total FROM authors a $search_condition";
$count_result = $conn->query($count_sql);
$total_records = $count_result->fetch_assoc()['total'];
$total_pages = ceil($total_records / $records_per_page);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Authors - Bookstore Admin</title>
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
        
        .bio-cell {
            max-width: 300px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        
        .author-image {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            object-fit: cover;
        }
        
        .no-image {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background-color: #e0e0e0;
            color: #757575;
            font-size: 16px;
        }
    </style>
</head>
<body>
    <div class="container">
        <header class="header">
            <h1>Manage Authors</h1>
            <div>
                <a href="../forms/add_author.php" class="btn btn-primary">Add New Author</a>
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
                <input type="text" name="search" placeholder="Search by name or bio..." value="<?php echo htmlspecialchars($search); ?>">
                <button type="submit">Search</button>
            </form>
        </div>
        
        <div class="table-container">
            <?php if ($result && $result->num_rows > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Profile</th>
                            <th>Name</th>
                            <th>Biography</th>
                            <th>Books Count</th>
                            <th>Created At</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo $row['author_id']; ?></td>
                                <td>
                                    <?php if (!empty($row['profile_image']) && file_exists('../uploads/authors/' . $row['profile_image'])): ?>
                                        <img src="../uploads/authors/<?php echo $row['profile_image']; ?>" alt="<?php echo htmlspecialchars($row['first_name'] . ' ' . $row['last_name']); ?>" class="author-image">
                                    <?php else: ?>
                                        <div class="no-image"><?php echo substr($row['first_name'], 0, 1) . substr($row['last_name'], 0, 1); ?></div>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars($row['first_name'] . ' ' . $row['last_name']); ?></td>
                                <td class="bio-cell"><?php echo htmlspecialchars($row['bio'] ?? 'No biography available'); ?></td>
                                <td><?php echo $row['book_count']; ?></td>
                                <td><?php echo date('M d, Y', strtotime($row['created_at'])); ?></td>
                                <td class="action-buttons">
                                    <a href="../forms/edit_author.php?id=<?php echo $row['author_id']; ?>" class="btn btn-secondary">Edit</a>
                                    <a href="author_details.php?id=<?php echo $row['author_id']; ?>" class="btn btn-secondary">View</a>
                                    <a href="manage_authors.php?delete=<?php echo $row['author_id']; ?>" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this author? This action cannot be undone.')">Delete</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="empty-state">
                    <p>No authors found.</p>
                    <a href="../forms/add_author.php" class="btn btn-primary">Add Your First Author</a>
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