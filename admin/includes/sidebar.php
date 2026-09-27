<?php $currentPage = basename($_SERVER["PHP_SELF"]); ?>

<aside class="admin-sidebar">

    <div class="sidebar-brand">
        Mang Inasal
        <span>Admin Panel</span>
    </div>

    <nav>
        <a href="dashboard.php" class="<?= $currentPage === "dashboard.php" ? "active" : "" ?>">Dashboard</a>
        <a href="products.php" class="<?= $currentPage === "products.php" ? "active" : "" ?>">Manage Products</a>
        <a href="categories.php" class="<?= $currentPage === "categories.php" ? "active" : "" ?>">Manage Categories</a>
        <a href="users.php" class="<?= $currentPage === "users.php" ? "active" : "" ?>">Manage Users</a>
        <a href="sales.php" class="<?= $currentPage === "sales.php" ? "active" : "" ?>">Sales Overview</a>
        <a href="orders.php" class="<?= $currentPage === "orders.php" ? "active" : "" ?>">View All Orders</a>
    </nav>

    <div class="sidebar-footer">
        <?= htmlspecialchars($_SESSION["username"]) ?>
        <br>
        <a href="../logout.php">Logout</a>
    </div>

</aside>