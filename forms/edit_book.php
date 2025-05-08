<?php
session_start();
include('../config/db.php');

// Fetch authors and categories for dropdowns
$authors_sql = "SELECT author_id, CONCAT(first_name, ' ', last_name) AS full_name FROM authors";
$authors_result = $conn->query($authors_sql);
$authors = [];
while ($row = $authors_result->fetch_assoc()) {
    $authors[] = $row;
}

$categories_sql = "SELECT category_id, name FROM categories";
$categories_result = $conn->query($categories_sql);
$categories = [];
while ($row = $categories_result->fetch_assoc()) {
    $categories[] = $row;
}

$success = false;
$existing_book = [];
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $book_id = $_POST['book_id'];

    // Fetch existing book data
    $stmt = $conn->prepare("SELECT * FROM books WHERE book_id = ?");
    $stmt->bind_param("i", $book_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $existing_book = $result->fetch_assoc();

    if (!$existing_book) {
        die("Book not found.");
    }

    // Get form data
    $title = $_POST['title'];
    $author_id = $_POST['author_id'];
    $price = $_POST['price'];
    $description = $_POST['description'];
    $publish_date = $_POST['publish_date'];
    $category_id = $_POST['category_id'];
    $stock_quantity = $_POST['stock_quantity'];
    $isbn = $_POST['isbn'];
    $cover_image = $existing_book['cover_image'];

    // Handle image upload
    if (isset($_FILES['cover_image']) && $_FILES['cover_image']['error'] == 0) {
        $target_dir = "../uploads/";
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        $new_cover_image = basename($_FILES["cover_image"]["name"]);
        $target_file = $target_dir . $new_cover_image;

        if (move_uploaded_file($_FILES["cover_image"]["tmp_name"], $target_file)) {
            // Delete old image if exists
            if ($cover_image && file_exists("../uploads/$cover_image")) {
                unlink("../uploads/$cover_image");
            }
            $cover_image = $new_cover_image;
        }
    }

    // Update book
    $update_sql = "UPDATE books SET 
                    title = ?, 
                    author_id = ?, 
                    price = ?, 
                    description = ?, 
                    publish_date = ?, 
                    category_id = ?, 
                    stock_quantity = ?, 
                    cover_image = ?, 
                    isbn = ?
                   WHERE book_id = ?";
    $stmt = $conn->prepare($update_sql);
    $stmt->bind_param(
        "sdsdsiissi",
        $title,
        $author_id,
        $price,
        $description,
        $publish_date,
        $category_id,
        $stock_quantity,
        $cover_image,
        $isbn,
        $book_id
    );

    if ($stmt->execute()) {
        $success = true;
    } else {
        $error_message = "Error updating book: " . $stmt->error;
    }
} else {
    // GET request handling
    if (!isset($_GET['id'])) {
        die("Book ID not specified.");
    }

    $book_id = $_GET['id'];
    $stmt = $conn->prepare("SELECT * FROM books WHERE book_id = ?");
    $stmt->bind_param("i", $book_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $existing_book = $result->fetch_assoc();

    if (!$existing_book) {
        die("Book not found.");
    }
}
?>

<!DOCTYPE html>
<html lang="en" dir="ltr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Book - BookNest</title>
    <link rel="stylesheet" href="../css/form.css">
</head>

<body>

    <main>
        <!-- Header Section -->
        <header style="background-color: #232f3e; color: white; padding: 20px 0; text-align: center;">
            <div class="container">
                <h1 style="margin: 0; font-size: 2rem;">📚 BookNest Admin Panel</h1>
                <p style="margin: 5px 0 0;">Edit book to the library collection</p>
            </div>
        </header>
        <section class="form-section">
            <h2>Edit Book</h2>

            <?php if ($success): ?>
                <div class="success-message">
                    ✅ Book updated successfully!
                    <br><br>
                    <a href="../admin/manage_books.php" class="dashboard-btn"
                        style="display: inline-block; padding: 10px 20px; background-color: #28a745; color: white; text-decoration: none; border-radius: 5px;">Go
                        to Manage Book</a>
                </div>
            <?php else: ?>
                <?php if ($error_message): ?>
                    <div class="error-message"><?php echo $error_message; ?></div>
                <?php endif; ?>

                <form action="edit_book.php" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="book_id" value="<?php echo $existing_book['book_id']; ?>">

                    <label for="title">Book Title:</label>
                    <input type="text" id="title" name="title"
                        value="<?php echo htmlspecialchars($_SERVER['REQUEST_METHOD'] == 'POST' ? ($_POST['title'] ?? '') : $existing_book['title']); ?>"
                        required>

                    <label for="author_id">Author:</label>
                    <select id="author_id" name="author_id" required>
                        <?php foreach ($authors as $author): ?>
                            <option value="<?php echo $author['author_id']; ?>" <?php
                               $selected_id = $_SERVER['REQUEST_METHOD'] == 'POST'
                                   ? ($_POST['author_id'] ?? '')
                                   : $existing_book['author_id'];
                               echo $author['author_id'] == $selected_id ? 'selected' : '';
                               ?>>
                                <?php echo $author['full_name']; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>

                    <label for="price">Price:</label>
                    <input type="number" id="price" name="price" step="0.01"
                        value="<?php echo htmlspecialchars($_SERVER['REQUEST_METHOD'] == 'POST' ? ($_POST['price'] ?? '') : $existing_book['price']); ?>"
                        required>

                    <label for="description">Description:</label>
                    <textarea id="description" name="description" required><?php
                    echo htmlspecialchars($_SERVER['REQUEST_METHOD'] == 'POST'
                        ? ($_POST['description'] ?? '')
                        : $existing_book['description']);
                    ?></textarea>

                    <label for="publish_date">Publish Date:</label>
                    <input type="date" id="publish_date" name="publish_date"
                        value="<?php echo htmlspecialchars($_SERVER['REQUEST_METHOD'] == 'POST' ? ($_POST['publish_date'] ?? '') : $existing_book['publish_date']); ?>"
                        required>

                    <label for="category_id">Category:</label>
                    <select id="category_id" name="category_id" required>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?php echo $category['category_id']; ?>" <?php
                               $selected_cat = $_SERVER['REQUEST_METHOD'] == 'POST'
                                   ? ($_POST['category_id'] ?? '')
                                   : $existing_book['category_id'];
                               echo $category['category_id'] == $selected_cat ? 'selected' : '';
                               ?>>
                                <?php echo $category['name']; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>

                    <label for="stock_quantity">Stock Quantity:</label>
                    <input type="number" id="stock_quantity" name="stock_quantity"
                        value="<?php echo htmlspecialchars($_SERVER['REQUEST_METHOD'] == 'POST' ? ($_POST['stock_quantity'] ?? '') : $existing_book['stock_quantity']); ?>"
                        required>

                    <label for="cover_image">Cover Image:</label>
                    <input type="file" id="cover_image" name="cover_image">
                    <?php if ($existing_book['cover_image']): ?>
                        <p>Current Image: <?php echo $existing_book['cover_image']; ?></p>
                        <img src="../uploads/<?php echo $existing_book['cover_image']; ?>" alt="Current Cover"
                            style="max-width: 200px;">
                    <?php endif; ?>

                    <label for="isbn">ISBN:</label>
                    <input type="text" id="isbn" name="isbn"
                        value="<?php echo htmlspecialchars($_SERVER['REQUEST_METHOD'] == 'POST' ? ($_POST['isbn'] ?? '') : $existing_book['isbn']); ?>"
                        required>

                    <button type="submit">Update Book</button>
                </form>
            <?php endif; ?>
        </section>
    </main>

    <?php include('../includes/footer.php'); ?>
</body>

</html>