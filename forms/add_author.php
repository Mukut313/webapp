<?php
session_start();
include('../config/db.php'); // Include your database connection file

$success = false;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Handle form submission
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $bio = $_POST['bio'];

    // Handle image upload
    $profile_image = null;
    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] == 0) {
        $target_dir = "../uploads/";
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        $profile_image = basename($_FILES["profile_image"]["name"]);
        $target_file = $target_dir . $profile_image;
        move_uploaded_file($_FILES["profile_image"]["tmp_name"], $target_file);
    }

    // Insert author into the database
    $insert_sql = "INSERT INTO authors (first_name, last_name, bio, profile_image) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($insert_sql);
    $stmt->bind_param("ssss", $first_name, $last_name, $bio, $profile_image);

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
    <meta name="description" content="Add a new author to the BookNest database">
    <meta name="keywords" content="add authors, BookNest">
    <meta name="author" content="BookNest Team">
    <title>Add Author - BookNest</title>
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
                <p style="margin: 5px 0 0;">Add a new author to the author collection</p>
            </div>
        </header>
        <section class="form-section">
            <h2>Add New Author</h2>

            <?php if ($success): ?>
                <div class="success-message"
                    style="background-color: #d4edda; color: #155724; padding: 15px; margin-bottom: 20px; border: 1px solid #c3e6cb; border-radius: 5px;">
                    ✅ Author added successfully!
                    <br><br>
                    <a href="../admin/dashboard.php" class="dashboard-btn"
                        style="display: inline-block; padding: 10px 20px; background-color: #28a745; color: white; text-decoration: none; border-radius: 5px;">Go
                        to Dashboard</a>
                </div>
            <?php endif; ?>

            <form action="add_author.php" method="POST" enctype="multipart/form-data">
                <label for="first_name">First Name:</label>
                <input type="text" id="first_name" name="first_name" required>

                <label for="last_name">Last Name:</label>
                <input type="text" id="last_name" name="last_name" required>

                <label for="bio">Biography:</label>
                <textarea id="bio" name="bio" required></textarea>

                <label for="profile_image">Profile Image:</label>
                <input type="file" id="profile_image" name="profile_image">

                <button type="submit">Add Author</button>
            </form>
        </section>
    </main>

    <!-- Include Footer -->
    <?php include('../includes/footer.php'); ?>

</body>

</html>