<?php

require_once "../includes/auth.php";

requireRole("staff");

require_once "../config/database.php";

$todayStats = $conn->query(
    "SELECT COUNT(*) AS order_count, COALESCE(SUM(total_amount), 0) AS order_total
     FROM orders
     WHERE DATE(order_date) = CURDATE()"
)->fetch_assoc();


/*
|--------------------------------------------------------------------------
| Quick stock lookup widget
|--------------------------------------------------------------------------
*/

$searchTerm = trim($_GET["stock_search"] ?? "");
$stockResults = null;

if ($searchTerm !== "") {

    $stmt = $conn->prepare(
        "SELECT product_name, category, quantity
         FROM products
         WHERE product_name LIKE ?
         ORDER BY product_name"
    );

    $likeTerm = "%" . $searchTerm . "%";
    $stmt->bind_param("s", $likeTerm);
    $stmt->execute();
    $stockResults = $stmt->get_result();
    $stmt->close();
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Dashboard - Mang Inasal</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../style.css">
    <link rel="stylesheet" href="../admin-style.css">
</head>

<body>

<div class="admin-layout">

    <?php include "includes/sidebar.php"; ?>

    <main class="admin-main">

        <h1>Welcome, <?= htmlspecialchars($_SESSION["username"]) ?></h1>
        <p class="page-subtitle">Here's today at a glance.</p>

        <div class="stat-grid">

            <div class="stat-card">
                <div class="stat-value"><?= (int) $todayStats["order_count"] ?></div>
                <div class="stat-label">Orders Today</div>
            </div>

            <div class="stat-card">
                <div class="stat-value">&#8369;<?= number_format((float) $todayStats["order_total"], 2) ?></div>
                <div class="stat-label">Revenue Today</div>
            </div>

        </div>

        <div class="shortcut-grid" style="margin-bottom: 32px;">

            <a href="process-order.php" class="shortcut-card">
                <div class="shortcut-title">Process New Order</div>
                <div class="shortcut-desc">Take a walk-in or phone order.</div>
            </a>

            <a href="orders.php" class="shortcut-card">
                <div class="shortcut-title">View Orders</div>
                <div class="shortcut-desc">See every order and update its status.</div>
            </a>

        </div>

        <h2 style="margin-bottom: 16px;">Quick Stock Lookup</h2>

        <form method="GET" class="admin-form" style="margin-bottom: 20px;">
            <div class="form-group" style="display:flex; gap:10px; align-items:flex-end;">
                <div>
                    <label>Product Name</label>
                    <input type="text" name="stock_search" value="<?= htmlspecialchars($searchTerm) ?>" placeholder="e.g. Pork Sisig">
                </div>
                <button type="submit" class="btn-primary">Search</button>
            </div>
        </form>

        <?php if ($stockResults !== null): ?>

            <table class="admin-table">
                <tr>
                    <th>Product</th>
                    <th>Category</th>
                    <th>In Stock</th>
                </tr>

                <?php if ($stockResults->num_rows === 0): ?>
                    <tr>
                        <td colspan="3">No products match "<?= htmlspecialchars($searchTerm) ?>".</td>
                    </tr>
                <?php endif; ?>

                <?php while ($product = $stockResults->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($product["product_name"]) ?></td>
                        <td><?= htmlspecialchars($product["category"]) ?></td>
                        <td><?= (int) $product["quantity"] ?></td>
                    </tr>
                <?php endwhile; ?>
            </table>

        <?php endif; ?>

    </main>

</div>

</body>

</html>