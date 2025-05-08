<?php
session_start();
include('../config/db.php');

// Fetch authors and categories for the dropdowns
$authors_sql = "SELECT author_id, CONCAT(first_name, ' ', last_name) AS full_name FROM authors";
$authors_result = $conn->query($authors_sql);

$categories_sql = "SELECT category_id, name FROM categories";
$categories_result = $conn->query($categories_sql);

$success = false;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = $_POST['title'];
    $author_id = $_POST['author_id'];
    $price = $_POST['price'];
    $description = $_POST['description'];
    $publish_date = $_POST['publish_date'];
    $category_id = $_POST['category_id'];
    $stock_quantity = $_POST['stock_quantity'];
    $isbn = $_POST['isbn'];

    // Handle image upload if provided
    $cover_image = null;
    if (isset($_FILES['cover_image']) && $_FILES['cover_image']['error'] == 0) {
        $target_dir = "../uploads/";
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        $cover_image = basename($_FILES["cover_image"]["name"]);
        $target_file = $target_dir . $cover_image;
        move_uploaded_file($_FILES["cover_image"]["tmp_name"], $target_file);
    }

    $insert_sql = "INSERT INTO books (title, author_id, price, description, publish_date, category_id, stock_quantity, cover_image, isbn)
                   VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($insert_sql);
    $stmt->bind_param("sdsdsiiss", $title, $author_id, $price, $description, $publish_date, $category_id, $stock_quantity, $cover_image, $isbn);

    if ($stmt->execute()) {
        $success = true;
    } else {
        echo "Error: " . $stmt->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en" dir="ltr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Add new books to the BookNest database">
    <meta name="keywords" content="add books, BookNest">
    <meta name="author" content="BookNest Team">
    <title>Add Book - BookNest</title>
    <link rel="icon" href="assets/icons/favicon.ico">
    <link rel="stylesheet" href="../css/form.css">
</head>

<body>

    <!-- Include Navbar -->

    <main>
        <!-- Header Section -->
        <header style="background-color: #232f3e; color: white; padding: 20px 0; text-align: center;">
            <div class="container">
                <h1 style="margin: 0; font-size: 2rem;">📚 BookNest Admin Panel</h1>
                <p style="margin: 5px 0 0;">Add a new book to the library collection</p>
            </div>
        </header>
        <section class="form-section">
            <h2>Add New Book</h2>

            <?php if ($success): ?>
                <div class="success-message"
                    style="background-color: #d4edda; color: #155724; padding: 15px; margin-bottom: 20px; border: 1px solid #c3e6cb; border-radius: 5px;">
                    ✅ Book added successfully!
                    <br><br>
                    <a href="../admin/dashboard.php" class="dashboard-btn"
                        style="display: inline-block; padding: 10px 20px; background-color: #28a745; color: white; text-decoration: none; border-radius: 5px;">Go
                        to Dashboard</a>
                </div>
            <?php endif; ?>

            <form action="add_book.php" method="POST" enctype="multipart/form-data">
                <label for="title">Book Title:</label>
                <input type="text" id="title" name="title" required>

                <label for="author_id">Author:</label>
                <select id="author_id" name="author_id" required>
                    <?php while ($author = $authors_result->fetch_assoc()) { ?>
                        <option value="<?php echo $author['author_id']; ?>"><?php echo $author['full_name']; ?></option>
                    <?php } ?>
                </select>

                <label for="price">Price:</label>
                <input type="number" id="price" name="price" step="0.01" required>

                <label for="description">Description:</label>
                <textarea id="description" name="description" required></textarea>

                <label for="publish_date">Publish Date:</label>
                <input type="date" id="publish_date" name="publish_date" required>

                <label for="category_id">Category:</label>
                <select id="category_id" name="category_id" required>
                    <?php while ($category = $categories_result->fetch_assoc()) { ?>
                        <option value="<?php echo $category['category_id']; ?>"><?php echo $category['name']; ?></option>
                    <?php } ?>
                </select>

                <label for="stock_quantity">Stock Quantity:</label>
                <input type="number" id="stock_quantity" name="stock_quantity" required>

                <label for="cover_image">Cover Image:</label>
                <input type="file" id="cover_image" name="cover_image">

                <label for="isbn">ISBN:</label>
                <input type="text" id="isbn" name="isbn" required>

                <button type="submit">Add Book</button>
            </form>
        </section>
    </main>

    <!-- Include Footer -->
    <?php include('../includes/footer.php'); ?>

</body>

</html>