<?php 

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

?>
<link rel="stylesheet" href="../css/nav-footer-hero.css">

<!-- Navbar -->
<nav class="navbar">
    <div class="container">
        <a href="index.php" class="logo">📚 BookNest</a>

        <ul class="nav-links">
            <li><a href="./index.php">Home</a></li>
            <li><a href="./books.php">Books</a></li>
            <li><a href="./cart.php">Cart</a></li>
            <?php if (isset($_SESSION['customer_id'])): ?>
                <li><a href="account.php">My Account</a></li>
                <li><a href="./php/auth/logout.php">Logout</a></li>
            <?php else: ?>
                <li><a href="./php/auth/login.php">Login</a></li>
                <li><a href="./php/auth/register.php">Register</a></li>
            <?php endif; ?>
        </ul>

        <div class="search-bar">
            <input type="text" placeholder="Search books...">
            <button type="submit">🔍</button>
        </div>

        <div class="hamburger" onclick="toggleMenu()">☰</div>
    </div>
</nav>
