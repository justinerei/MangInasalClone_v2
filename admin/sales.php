<?php

require_once "../includes/auth.php";

requireRole("admin");

require_once "../config/database.php";

$totalRevenue = $conn->query("SELECT COALESCE(SUM(total_amount), 0) AS total FROM orders")->fetch_assoc()["total"];
$totalOrders = $conn->query("SELECT COUNT(*) AS total FROM orders")->fetch_assoc()["total"];

$topSellers = $conn->query(
    "SELECT products.product_name, SUM(order_details.quantity) AS total_sold
     FROM order_details
     JOIN products ON order_details.product_id = products.product_id
     GROUP BY products.product_id, products.product_name
     ORDER BY total_sold DESC
     LIMIT 5"
);

$topSellersList = [];
$maxSold = 0;

while ($row = $topSellers->fetch_assoc()) {
    $topSellersList[] = $row;
    $maxSold = max($maxSold, (int) $row["total_sold"]);
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sales Overview - Mang Inasal Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../style.css">
    <link rel="stylesheet" href="../admin-style.css">
</head>

<body>

<div class="admin-layout">

    <?php include "includes/sidebar.php"; ?>

    <main class="admin-main">

        <h1>Sales Overview</h1>
        <p class="page-subtitle">All-time totals across every order ever placed.</p>

        <div class="stat-grid">

            <div class="stat-card">
                <div class="stat-value">&#8369;<?= number_format((float) $totalRevenue, 2) ?></div>
                <div class="stat-label">Total Revenue</div>
            </div>

            <div class="stat-card">
                <div class="stat-value"><?= (int) $totalOrders ?></div>
                <div class="stat-label">Total Orders</div>
            </div>

        </div>

        <h2 style="margin-bottom: 16px;">Top 5 Best-Selling Products</h2>

        <?php if (empty($topSellersList)): ?>

            <p>No sales data yet -- process a few orders first.</p>

        <?php else: ?>

            <?php foreach ($topSellersList as $seller): ?>

                <div class="bar-row">
                    <div class="bar-label"><?= htmlspecialchars($seller["product_name"]) ?></div>
                    <div class="bar-track">
                        <div class="bar-fill"
                             style="width: <?= $maxSold > 0 ? round(($seller["total_sold"] / $maxSold) * 100) : 0 ?>%;">
                        </div>
                    </div>
                    <div class="bar-value"><?= (int) $seller["total_sold"] ?> sold</div>
                </div>

            <?php endforeach; ?>

        <?php endif; ?>

    </main>

</div>

</body>

</html>