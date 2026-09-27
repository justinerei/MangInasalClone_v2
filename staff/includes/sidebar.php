<?php $currentPage = basename($_SERVER["PHP_SELF"]); ?>

<aside class="admin-sidebar">

    <div class="sidebar-brand">
        Mang Inasal
        <span>Staff Panel</span>
    </div>

    <nav>
        <a href="dashboard.php" class="<?= $currentPage === "dashboard.php" ? "active" : "" ?>">Dashboard</a>
        <a href="process-order.php" class="<?= $currentPage === "process-order.php" ? "active" : "" ?>">Process New Order</a>
        <a href="orders.php" class="<?= $currentPage === "orders.php" ? "active" : "" ?>">View Orders</a>
    </nav>

    <div class="sidebar-footer">
        <?= htmlspecialchars($_SESSION["username"]) ?>
        <br>
        <a href="../logout.php">Logout</a>
    </div>

</aside>