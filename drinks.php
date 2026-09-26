<?php

require_once "includes/auth.php";

requireCustomerFacing();

require_once "config/database.php";

loadSessionUser();


/*
|--------------------------------------------------------------------------
| Get Drinks
|--------------------------------------------------------------------------
*/

$products = [];

$stmt = $conn->prepare(
    "SELECT product_id, product_name, description, category, price, quantity, image_path
     FROM products
     WHERE category = 'Drinks'
     ORDER BY product_id ASC"
);

$stmt->execute();

$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $products[] = $row;
}

$stmt->close();

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Drinks - Mang Inasal Clone</title>

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


<body class="bg-off-white">


<header>

    <div class="header-left">

        <a href="index.php">

            <img
                src="images/logo.png"
                alt="Mang Inasal Logo"
                class="logo"
            >

        </a>

    </div>


    <nav class="header-center">

        <ul>

            <li>
                <a href="index.php">
                    Home
                </a>
            </li>

            <li>
                <a href="mainCourse.php">
                    Main Course
                </a>
            </li>

            <li>
                <a href="drinks.php" class="active">
                    Drinks
                </a>
            </li>

            <li>
                <a href="dessert.php">
                    Dessert
                </a>
            </li>

        </ul>

    </nav>


    <div class="header-right">

        <?php if ($isLoggedIn): ?>

            <button class="btn-primary" type="button">

                <i class="fa-solid fa-user"></i>

                <?php echo htmlspecialchars($username); ?>

            </button>

            
                href="logout.php"
                style="text-decoration: none;"
                title="Logout"
            >
                <i
                    class="fa-solid fa-right-from-bracket"
                    style="color: var(--black); font-size: 1.1rem;"
                ></i>
            </a>

        <?php else: ?>

            <a
                href="login.php"
                style="text-decoration: none;"
            >

                <button class="btn-primary">
                    Login
                </button>

            </a>

        <?php endif; ?>


        <a
            href="cart.php"
            style="text-decoration: none;"
        >

            <div class="cart-container">

                <i
                    class="fa-solid fa-cart-shopping cart-icon"
                    style="color: var(--black);"
                ></i>

                <span
                    class="cart-badge"
                    id="cart-badge"
                >
                    0
                </span>

            </div>

        </a>

    </div>

</header>


<main class="container page-content">


    <section
        class="menu-category"
        id="drinks"
        data-category="drinks"
    >

        <h3 class="category-title">
            Drinks
        </h3>

        <hr class="category-divider">


        <div class="menu-grid">

            <?php foreach ($products as $product): ?>

                <article class="menu-card">

                    <img
                        src="<?php echo htmlspecialchars($product["image_path"]); ?>"
                        alt="<?php echo htmlspecialchars($product["product_name"]); ?>"
                    >

                    <h4 class="card-title">
                        <?php echo htmlspecialchars($product["product_name"]); ?>
                    </h4>

                    <p class="card-price">
                        ₱<?php echo number_format($product["price"], 2); ?>
                    </p>

                    <button
                        class="btn-primary add-to-cart-btn"
                        data-product-id="<?php echo $product["product_id"]; ?>"
                    >

                        <i class="fa-solid fa-cart-plus"></i>

                        Add to Cart

                    </button>

                </article>

            <?php endforeach; ?>

        </div>

    </section>


</main>


<script src="script.js"></script>

</body>

</html>