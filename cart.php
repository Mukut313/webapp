<?php
session_start();
// Include the database connection file
include('./config/db.php');

// Check if the connection was successful
if (!isset($conn) || $conn->connect_error) {
    die("Database connection failed: " . (isset($conn) ? $conn->connect_error : "db.php file might have errors."));
}

// Handle cart item removal if requested
if(isset($_POST['remove_item']) && isset($_POST['book_id'])) {
    $bookId = $_POST['book_id'];
    echo "<script>
        let cart = JSON.parse(localStorage.getItem('cart') || '[]');
        cart = cart.filter(item => item.id !== '$bookId');
        localStorage.setItem('cart', JSON.stringify(cart));
        window.location.href = 'cart.php';
    </script>";
}

// Handle cart update if requested
if(isset($_POST['update_cart'])) {
    echo "<script>
        const formData = new FormData(document.getElementById('cart-form'));
        let cart = JSON.parse(localStorage.getItem('cart') || '[]');
        
        for(const pair of formData.entries()) {
            const bookId = pair[0].replace('quantity_', '');
            const quantity = parseInt(pair[1]);
            
            if(quantity <= 0) {
                cart = cart.filter(item => item.id !== bookId);
            } else {
                const item = cart.find(item => item.id === bookId);
                if(item) {
                    item.quantity = quantity;
                }
            }
        }
        
        localStorage.setItem('cart', JSON.stringify(cart));
        window.location.href = 'cart.php';
    </script>";
}

// Handle checkout process
if(isset($_POST['checkout']) && !empty($_POST['checkout'])) {
    // In a real application, you would process the order here 
    // For now, we'll just clear the cart and show a success message
    $_SESSION['checkout_success'] = true;
    echo "<script>
        localStorage.setItem('cart', JSON.stringify([]));
        window.location.href = 'cart.php?checkout_success=1';
    </script>";
}
?>

<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Your shopping cart at BookNest.">
    <meta name="keywords" content="books, cart, checkout, BookNest">
    <meta name="author" content="BookNest Team">
    <title>BookNest - Shopping Cart</title>
    <link rel="icon" href="assets/icons/favicon.ico">
    <link rel="stylesheet" href="css/nav-footer-hero.css">
    <link rel="stylesheet" href="./css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
    <style>
        /* Cart Page Specific Styles */
        .cart-container {
            max-width: 1200px;
            margin: 30px auto;
            padding: 20px;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        
        .cart-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid #e0e0e0;
        }
        
        .cart-header h1 {
            font-size: 1.8rem;
            color: #333;
            margin: 0;
        }
        
        .continue-shopping {
            display: inline-flex;
            align-items: center;
            color: #4a7c59;
            text-decoration: none;
            font-weight: 600;
            transition: color 0.2s;
        }
        
        .continue-shopping:hover {
            color: #3a6349;
        }
        
        .continue-shopping i {
            margin-right: 8px;
        }
        
        .cart-empty {
            text-align: center;
            padding: 40px 20px;
        }
        
        .cart-empty i {
            font-size: 4rem;
            color: #ccc;
            margin-bottom: 20px;
        }
        
        .cart-empty h2 {
            font-size: 1.5rem;
            color: #555;
            margin-bottom: 15px;
        }
        
        .cart-empty p {
            color: #777;
            margin-bottom: 25px;
        }
        
        .start-shopping-btn {
            display: inline-block;
            background: #4a7c59;
            color: white;
            padding: 10px 20px;
            border-radius: 4px;
            text-decoration: none;
            font-weight: 600;
            transition: background 0.3s;
        }
        
        .start-shopping-btn:hover {
            background: #3a6349;
        }
        
        .cart-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        
        .cart-table th {
            padding: 12px 15px;
            text-align: left;
            background: #f8f8f8;
            color: #555;
            font-weight: 600;
            border-bottom: 2px solid #e0e0e0;
        }
        
        .cart-table td {
            padding: 15px;
            border-bottom: 1px solid #e0e0e0;
            vertical-align: middle;
        }
        
        .cart-item-info {
            display: flex;
            align-items: center;
        }
        
        .cart-item-image {
            width: 80px;
            height: 120px;
            object-fit: cover;
            margin-right: 15px;
            border-radius: 4px;
        }
        
        .cart-item-details h3 {
            margin: 0 0 5px;
            font-size: 1.1rem;
        }
        
        .cart-item-details p {
            margin: 0;
            color: #777;
            font-size: 0.9rem;
        }
        
        .cart-quantity {
            width: 70px;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            text-align: center;
        }
        
        .remove-item {
            background: none;
            border: none;
            color: #e74c3c;
            cursor: pointer;
            font-size: 1.1rem;
            padding: 5px;
            transition: color 0.2s;
        }
        
        .remove-item:hover {
            color: #c0392b;
        }
        
        .cart-actions {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-top: 20px;
        }
        
        .cart-buttons {
            display: flex;
            gap: 10px;
        }
        
        .update-cart-btn, .clear-cart-btn {
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-weight: 600;
            transition: background 0.3s, transform 0.2s;
        }
        
        .update-cart-btn {
            background: #3498db;
            color: white;
        }
        
        .update-cart-btn:hover {
            background: #2980b9;
            transform: translateY(-2px);
        }
        
        .clear-cart-btn {
            background: #e74c3c;
            color: white;
        }
        
        .clear-cart-btn:hover {
            background: #c0392b;
            transform: translateY(-2px);
        }
        
        .cart-summary {
            background: #f9f9f9;
            padding: 20px;
            border-radius: 6px;
            width: 300px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
        }
        
        .cart-summary h2 {
            margin-top: 0;
            padding-bottom: 10px;
            border-bottom: 1px solid #e0e0e0;
            font-size: 1.3rem;
        }
        
        .summary-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 12px;
            color: #555;
        }
        
        .summary-total {
            display: flex;
            justify-content: space-between;
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px solid #e0e0e0;
            font-weight: 700;
            font-size: 1.1rem;
        }
        
        .checkout-btn {
            display: block;
            width: 100%;
            background: #4a7c59;
            color: white;
            border: none;
            padding: 12px 0;
            border-radius: 4px;
            margin-top: 20px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.3s, transform 0.2s;
        }
        
        .checkout-btn:hover {
            background: #3a6349;
            transform: translateY(-2px);
        }
        
        .checkout-btn:disabled {
            background: #cccccc;
            cursor: not-allowed;
            transform: none;
        }

        /* Success Message */
        .success-message {
            background-color: #d4edda;
            color: #155724;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
            display: flex;
            align-items: center;
        }

        .success-message i {
            font-size: 1.5rem;
            margin-right: 10px;
        }
        
        /* Responsive styles */
        @media (max-width: 768px) {
            .cart-actions {
                flex-direction: column;
                gap: 20px;
            }
            
            .cart-summary {
                width: 100%;
            }
            
            .cart-table {
                font-size: 0.9rem;
            }
            
            .cart-table th, .cart-table td {
                padding: 10px;
            }
            
            .cart-item-image {
                width: 60px;
                height: 90px;
            }
            
            .cart-item-details h3 {
                font-size: 1rem;
            }
        }
        
        @media (max-width: 576px) {
            .cart-table-wrapper {
                overflow-x: auto;
            }
            
            .cart-table {
                min-width: 500px;
            }
        }
    </style>
</head>
<body>

<!-- Include Navbar -->
 <?php include('./includes/navbar.php'); ?>

<main>
    <div class="cart-container">
        <div class="cart-header">
            <h1>Shopping Cart</h1>
            <a href="./index.php" class="continue-shopping">
                <i class="fas fa-arrow-left"></i> Continue Shopping
            </a>
        </div>
        
        <?php if(isset($_GET['checkout_success'])): ?>
        <div class="success-message">
            <i class="fas fa-check-circle"></i>
            <div>
                <h3>Order Completed Successfully!</h3>
                <p>Thank you for your purchase. Your order has been received and is being processed.</p>
            </div>
        </div>
        <?php endif; ?>

        <div id="cart-content">
            <!-- Cart content will be inserted here via JavaScript -->
        </div>
    </div>
</main>

<!-- Include Footer -->
<?php include('./includes/footer.php'); ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Fetch cart data from localStorage
    const cart = JSON.parse(localStorage.getItem('cart') || '[]');
    const cartContentElement = document.getElementById('cart-content');
    
    // If cart is empty, show empty cart message
    if (cart.length === 0) {
        cartContentElement.innerHTML = `
            <div class="cart-empty">
                <i class="fas fa-shopping-cart"></i>
                <h2>Your cart is empty</h2>
                <p>Looks like you haven't added any books to your cart yet.</p>
                <a href="./index.php" class="start-shopping-btn">Start Shopping</a>
            </div>
        `;
        return;
    }
    
    // Fetch book details for items in cart
    const bookIds = cart.map(item => item.id).join(',');
    
    // Prepare to calculate totals
    let subtotal = 0;
    const tax = 0.08; // 8% tax rate
    const shipping = 5.99; // Flat shipping rate
    
    // Create cart HTML
    let cartHTML = `
        <form id="cart-form" method="post" action="cart.php">
            <div class="cart-table-wrapper">
                <table class="cart-table">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Price</th>
                            <th>Quantity</th>
                            <th>Total</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
    `;
    
    // Add cart items
    cart.forEach(item => {
        const itemTotal = parseFloat(item.price) * parseInt(item.quantity);
        subtotal += itemTotal;
        
        cartHTML += `
            <tr>
                <td>
                    <div class="cart-item-info">
                        <div class="cart-item-details">
                            <h3>${item.title}</h3>
                        </div>
                    </div>
                </td>
                <td>$${parseFloat(item.price).toFixed(2)}</td>
                <td>
                    <input type="number" name="quantity_${item.id}" class="cart-quantity" value="${item.quantity}" min="1">
                </td>
                <td>$${itemTotal.toFixed(2)}</td>
                <td>
                    <button type="button" class="remove-item" onclick="removeFromCart(${item.id})">
                        <i class="fas fa-trash-alt"></i>
                    </button>
                    
                </td>
            </tr>
        `;
    });
    
    // Calculate totals
    const taxAmount = subtotal * tax;
    const total = subtotal + taxAmount + shipping;
    
    // Complete cart HTML
    cartHTML += `
                    </tbody>
                </table>
            </div>
            
            <input type="hidden" id="book_id_to_remove" name="book_id" value="">
            
            <div class="cart-actions">
                <div class="cart-buttons">
                    
                    <button type="button" class="clear-cart-btn" onclick="clearCart()">
                        <i class="fas fa-trash"></i> Clear Cart
                    </button>
                </div>
                
                <div class="cart-summary">
                    <h2>Order Summary</h2>
                    <div class="summary-item">
                        <span>Subtotal:</span>
                        <span>$${subtotal.toFixed(2)}</span>
                    </div>
                    <div class="summary-item">
                        <span>Tax (${(tax * 100).toFixed(0)}%):</span>
                        <span>$${taxAmount.toFixed(2)}</span>
                    </div>
                    <div class="summary-item">
                        <span>Shipping:</span>
                        <span>$${shipping.toFixed(2)}</span>
                    </div>
                    <div class="summary-total">
                        <span>Total:</span>
                        <span>$${total.toFixed(2)}</span>
                    </div>
                    
                    <button type="submit" name="checkout" value="1" class="checkout-btn">
                        <i class="fas fa-lock"></i> Proceed to Checkout
                    </button>
                </div>
            </div>
        </form>
    `;
    
    cartContentElement.innerHTML = cartHTML;
});

// Function to clear the cart
function clearCart() {
    if (confirm('Are you sure you want to clear your cart?')) {
        localStorage.setItem('cart', JSON.stringify([]));
        window.location.reload();
    }
}
function removeFromCart(id) {
    const cart = JSON.parse(localStorage.getItem('cart') || '[]');
    const updatedCart = cart.filter(item => item.id != id);
    localStorage.setItem('cart', JSON.stringify(updatedCart));
    window.location.reload(); // Refresh to re-render cart
}

</script>

</body>
</html>

<?php
// Close the connection
$conn->close();
?>