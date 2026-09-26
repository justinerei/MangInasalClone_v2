<?php

require_once "includes/auth.php";

requireCustomerFacing();

$isLoggedIn = isset($_SESSION["user_id"]);
$username = $isLoggedIn ? $_SESSION["username"] : null;

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Mang Inasal | Cart</title>

    <link rel="stylesheet" href="style.css">

    <link
        href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap"
        rel="stylesheet"
    >

    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"
    >

    <link
        rel="icon"
        type="image/x-icon"
        href="images/favicon.ico"
    >

</head>

<body>


<header>

    <div class="header-left">
        <a href="index.php">
            <img src="images/logo.png" alt="Mang Inasal Logo" class="logo">
        </a>
    </div>

    <nav class="header-center">
        <ul>
            <li><a href="index.php">Home</a></li>
            <li><a href="mainCourse.php">Main Course</a></li>
            <li><a href="drinks.php">Drinks</a></li>
            <li><a href="dessert.php">Dessert</a></li>
        </ul>
    </nav>

    <div class="header-right">

        <?php if ($isLoggedIn): ?>

            <a href="customer/dashboard.php" style="text-decoration: none;">
                <button class="btn-primary">
                    <i class="fa-solid fa-user"></i>
                    <?php echo htmlspecialchars($username); ?>
                </button>
            </a>

        <?php else: ?>

            <a href="login.php" style="text-decoration: none;">
                <button class="btn-primary">Login</button>
            </a>

        <?php endif; ?>

        <a href="cart.php" style="text-decoration: none;">
            <div class="cart-container">
                <i class="fa-solid fa-cart-shopping cart-icon" style="color: var(--black);"></i>
                <span class="cart-badge" id="cart-badge">0</span>
            </div>
        </a>

    </div>

</header>


<main class="container page-content">

    <div class="cart-header">
        <h1 class="page-title">Your Cart</h1>
        <p class="breadcrumb">
            <a href="index.php">Home</a> / Cart
        </p>
    </div>


    <div class="cart-layout">

        <!-- LEFT: Cart Items -->
        <div class="cart-items-column">

            <div class="cart-table-header">
                <span class="col-product">Product</span>
                <span>Price</span>
                <span>Quantity</span>
                <span>Subtotal</span>
            </div>

            <div id="cart-items">
                <!-- Cart item rows are rendered here by script.js -->
            </div>

            <p id="empty-cart-message" style="display: none; color: var(--text-gray); padding: 20px 0;">
                Your cart is empty.
                <a href="mainCourse.php" class="text-link-btn">Browse the menu</a>
            </p>

            <div class="cart-actions-bottom">

                <div>
                    <span class="senior-label">Senior Citizen / PWD Discount</span>

                    <div class="senior-inputs">

                        <input
                            type="number"
                            id="age-input"
                            placeholder="Age"
                            min="0"
                        >

                        <label class="checkbox-container">
                            <input type="checkbox" id="senior-checkbox" disabled>
                            <span>I have a valid ID</span>
                        </label>

                        <button type="button" id="apply-discount-btn" class="text-link-btn">
                            Apply
                        </button>

                    </div>
                </div>

            </div>

        </div>


        <!-- RIGHT: Order Summary -->
        <div class="sticky-card">

            <h2 class="summary-title">Order Summary</h2>

            <ul class="summary-details">

                <li>
                    <span>Subtotal</span>
                    <span id="subtotal">₱0.00</span>
                </li>

                <li>
                    <span>Items</span>
                    <span id="item-count">0</span>
                </li>

                <li class="discount-row">
                    <span>Senior/PWD Discount</span>
                    <span id="senior-discount">-₱0.00</span>
                </li>

                <li>
                    <span>Shipping</span>
                    <span id="shipping">₱50.00</span>
                </li>

            </ul>

            <hr class="summary-divider">

            <div class="summary-total-row">
                <span>Total</span>
                <span id="total">₱0.00</span>
            </div>

            <button id="checkout-btn" class="btn-primary btn-checkout" type="button">
                Checkout
            </button>

        </div>

    </div>

</main>


<!-- RECEIPT MODAL -->
<div id="receipt-modal" class="modal-overlay hidden">

    <div class="receipt-box">

        <button id="close-receipt" class="close-btn" type="button">&times;</button>

        <div class="receipt-header">
            <img src="images/logo.png" alt="Mang Inasal" class="receipt-logo">
            <h2>Order Receipt</h2>
            <p id="receipt-date"></p>
        </div>

        <div class="receipt-body">

            <ul id="receipt-items"></ul>

            <div class="receipt-row">
                <span>Subtotal</span>
                <span id="receipt-subtotal">₱0.00</span>
            </div>

            <div class="receipt-row">
                <span>Discount</span>
                <span id="receipt-discount">-₱0.00</span>
            </div>

            <div class="receipt-row">
                <span>Shipping</span>
                <span id="receipt-shipping">₱0.00</span>
            </div>

            <div class="receipt-total">
                <span>Total</span>
                <span id="receipt-total">₱0.00</span>
            </div>

        </div>

        <button id="finalize-order" class="btn-primary btn-checkout" type="button">
            Confirm Order
        </button>

    </div>

</div>


<script src="script.js"></script>

</body>

</html>