<?php

require_once "../includes/auth.php";

requireRole("admin");

require_once "../config/database.php";

$totalProducts = $conn->query("SELECT COUNT(*) AS total FROM products")->fetch_assoc()["total"];
$totalOrders = $conn->query("SELECT COUNT(*) AS total FROM orders")->fetch_assoc()["total"];
$totalRevenue = $conn->query("SELECT COALESCE(SUM(total_amount), 0) AS total FROM orders")->fetch_assoc()["total"];

$lowStockThreshold = 10;

$lowStockStmt = $conn->prepare("SELECT COUNT(*) AS total FROM products WHERE quantity < ?");
$lowStockStmt->bind_param("i", $lowStockThreshold);
$lowStockStmt->execute();
$lowStockCount = $lowStockStmt->get_result()->fetch_assoc()["total"];
$lowStockStmt->close();

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Mang Inasal</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../style.css">
    <link rel="stylesheet" href="../admin-style.css">
</head>

<body>

<div class="admin-layout">

    <?php include "includes/sidebar.php"; ?>

    <main class="admin-main">

        <h1>Welcome, <?= htmlspecialchars($_SESSION["username"]) ?></h1>
        <p class="page-subtitle">Here's what's happening at Mang Inasal right now.</p>

        <div class="stat-grid">

            <div class="stat-card">
                <div class="stat-value"><?= (int) $totalProducts ?></div>
                <div class="stat-label">Total Products</div>
            </div>

            <div class="stat-card">
                <div class="stat-value"><?= (int) $totalOrders ?></div>
                <div class="stat-label">Total Orders</div>
            </div>

            <div class="stat-card">
                <div class="stat-value">&#8369;<?= number_format((float) $totalRevenue, 2) ?></div>
                <div class="stat-label">Total Revenue</div>
            </div>

            <div class="stat-card">
                <div class="stat-value <?= $lowStockCount > 0 ? "warning" : "" ?>"><?= (int) $lowStockCount ?></div>
                <div class="stat-label">Low Stock Items (&lt; <?= $lowStockThreshold ?>)</div>
            </div>

        </div>

        <div class="shortcut-grid">

            <a href="products.php" class="shortcut-card">
                <div class="shortcut-title">Manage Products</div>
                <div class="shortcut-desc">Add, edit, or remove menu items and stock.</div>
            </a>

            <a href="categories.php" class="shortcut-card">
                <div class="shortcut-title">Manage Categories</div>
                <div class="shortcut-desc">Keep product categories consistent.</div>
            </a>

            <a href="users.php" class="shortcut-card">
                <div class="shortcut-title">Manage Users</div>
                <div class="shortcut-desc">View accounts and change roles.</div>
            </a>

            <a href="sales.php" class="shortcut-card">
                <div class="shortcut-title">Sales Overview</div>
                <div class="shortcut-desc">Revenue and top-selling items.</div>
            </a>

            <a href="orders.php" class="shortcut-card">
                <div class="shortcut-title">View All Orders</div>
                <div class="shortcut-desc">Every order placed, with full line items.</div>
            </a>

        </div>

    </main>

</div>

</body>

</html>