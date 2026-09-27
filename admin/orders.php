<?php

require_once "../includes/auth.php";

requireRole("admin");

require_once "../config/database.php";

$orders = $conn->query(
    "SELECT orders.order_id, users.username, orders.order_date, orders.total_amount, orders.status
     FROM orders
     JOIN users ON orders.user_id = users.user_id
     ORDER BY orders.order_id DESC"
);

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Orders - Mang Inasal Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../style.css">
    <link rel="stylesheet" href="../admin-style.css">
</head>

<body>

<div class="admin-layout">

    <?php include "includes/sidebar.php"; ?>

    <main class="admin-main">

        <h1>All Orders</h1>
        <p class="page-subtitle">Read-only view of every order placed.</p>

        <?php if ($orders->num_rows === 0): ?>

            <p>No orders yet.</p>

        <?php else: ?>

            <?php while ($order = $orders->fetch_assoc()): ?>

                <div class="order-card">

                    <div class="order-card-header">
                        <strong>Order #<?= (int) $order["order_id"] ?></strong>
                        <span>Customer: <?= htmlspecialchars($order["username"]) ?></span>
                        <span><?= htmlspecialchars($order["order_date"]) ?></span>
                        <span>&#8369;<?= htmlspecialchars($order["total_amount"]) ?></span>
                        <span class="status-badge <?= htmlspecialchars($order["status"]) ?>">
                            <?= htmlspecialchars($order["status"]) ?>
                        </span>
                    </div>

                    <?php

                    $detailStmt = $conn->prepare(
                        "SELECT products.product_name, order_details.quantity, order_details.subtotal
                         FROM order_details
                         JOIN products ON order_details.product_id = products.product_id
                         WHERE order_details.order_id = ?"
                    );

                    $detailStmt->bind_param("i", $order["order_id"]);
                    $detailStmt->execute();
                    $details = $detailStmt->get_result();

                    ?>

                    <ul>
                        <?php while ($item = $details->fetch_assoc()): ?>
                            <li>
                                <?= (int) $item["quantity"] ?>x
                                <?= htmlspecialchars($item["product_name"]) ?>
                                &mdash; &#8369;<?= htmlspecialchars($item["subtotal"]) ?>
                            </li>
                        <?php endwhile; ?>
                    </ul>

                    <?php $detailStmt->close(); ?>

                </div>

            <?php endwhile; ?>

        <?php endif; ?>

    </main>

</div>

</body>

</html>