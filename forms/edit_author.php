<?php
session_start();
include('../config/db.php');

$success = false;
$error_message = '';
$existing_author = [];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $author_id = $_POST['author_id'];

    // Fetch existing author data
    $stmt = $conn->prepare("SELECT * FROM authors WHERE author_id = ?");
    $stmt->bind_param("i", $author_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $existing_author = $result->fetch_assoc();

    if (!$existing_author) {
        die("Author not found.");
    }

    // Get form data
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $bio = $_POST['bio'];
    $profile_image = $existing_author['profile_image'];

    // Handle image upload
    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] == 0) {
        $target_dir = "../uploads/";
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        $new_profile_image = basename($_FILES["profile_image"]["name"]);
        $target_file = $target_dir . $new_profile_image;

        if (move_uploaded_file($_FILES["profile_image"]["tmp_name"], $target_file)) {
            // Delete old image if exists
            if ($profile_image && file_exists("../uploads/$profile_image")) {
                unlink("../uploads/$profile_image");
            }
            $profile_image = $new_profile_image;
        }
    }

    // Update author
    $update_sql = "UPDATE authors SET 
                    first_name = ?, 
                    last_name = ?, 
                    bio = ?, 
                    profile_image = ?
                   WHERE author_id = ?";
    $stmt = $conn->prepare($update_sql);
    $stmt->bind_param("ssssi", $first_name, $last_name, $bio, $profile_image, $author_id);

    if ($stmt->execute()) {
        $success = true;
    } else {
        $error_message = "Error updating author: " . $stmt->error;
    }
} else {
    // GET request handling
    if (!isset($_GET['id'])) {
        die("Author ID not specified.");
    }

    $author_id = $_GET['id'];
    $stmt = $conn->prepare("SELECT * FROM authors WHERE author_id = ?");
    $stmt->bind_param("i", $author_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $existing_author = $result->fetch_assoc();

    if (!$existing_author) {
        die("Author not found.");
    }
}
?>

<!DOCTYPE html>
<html lang="en" dir="ltr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Edit author in BookNest database">
    <meta name="keywords" content="edit authors, BookNest">
    <meta name="author" content="BookNest Team">
    <title>Edit Author - BookNest</title>
    <link rel="icon" href="assets/icons/favicon.ico">
    <link rel="stylesheet" href="../css/form.css">
</head>

<body>

    <main>
        <!-- Header Section -->
        <header style="background-color: #232f3e; color: white; padding: 20px 0; text-align: center;">
            <div class="container">
                <h1 style="margin: 0; font-size: 2rem;">📚 BookNest Admin Panel</h1>
                <p style="margin: 5px 0 0;">Edit author to the author collection</p>
            </div>
        </header>
        <section class="form-section">
            <h2>Edit Author</h2>

            <?php if ($success): ?>
                <div class="success-message"
                    style="background-color: #d4edda; color: #155724; padding: 15px; margin-bottom: 20px; border: 1px solid #c3e6cb; border-radius: 5px;">
                    ✅ Author updated successfully!
                    <br><br>
                    <a href="../admin/dashboard.php" class="dashboard-btn"
                        style="display: inline-block; padding: 10px 20px; background-color: #28a745; color: white; text-decoration: none; border-radius: 5px;">Go
                        to Dashboard</a>
                </div>
            <?php else: ?>
                <?php if ($error_message): ?>
                    <div class="error-message"
                        style="background-color: #f8d7da; color: #721c24; padding: 15px; margin-bottom: 20px; border: 1px solid #f5c6cb; border-radius: 5px;">
                        <?php echo $error_message; ?>
                    </div>
                <?php endif; ?>

                <form action="edit_author.php" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="author_id" value="<?php echo $existing_author['author_id']; ?>">

                    <label for="first_name">First Name:</label>
                    <input type="text" id="first_name" name="first_name"
                        value="<?php echo htmlspecialchars($_SERVER['REQUEST_METHOD'] == 'POST' ? ($_POST['first_name'] ?? '') : $existing_author['first_name']); ?>"
                        required>

                    <label for="last_name">Last Name:</label>
                    <input type="text" id="last_name" name="last_name"
                        value="<?php echo htmlspecialchars($_SERVER['REQUEST_METHOD'] == 'POST' ? ($_POST['last_name'] ?? '') : $existing_author['last_name']); ?>"
                        required>

                    <label for="bio">Biography:</label>
                    <textarea id="bio" name="bio" required><?php
                    echo htmlspecialchars($_SERVER['REQUEST_METHOD'] == 'POST'
                        ? ($_POST['bio'] ?? '')
                        : $existing_author['bio']);
                    ?></textarea>

                    <label for="profile_image">Profile Image:</label>
                    <input type="file" id="profile_image" name="profile_image">
                    <?php if ($existing_author['profile_image']): ?>
                        <p>Current Image: <?php echo $existing_author['profile_image']; ?></p>
                        <img src="../uploads/<?php echo $existing_author['profile_image']; ?>" alt="Current Profile Image"
                            style="max-width: 200px; margin-top: 10px;">
                    <?php endif; ?>

                    <button type="submit">Update Author</button>
                </form>
            <?php endif; ?>
        </section>
    </main>

    <?php include('../includes/footer.php'); ?>
</body>

</html>