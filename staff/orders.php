<?php

require_once "../includes/auth.php";

requireRole("staff");

require_once "../config/database.php";

$error = "";
$success = "";

$validStatuses = ["pending", "preparing", "completed"];

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["update_status"])) {

    $orderId = (int) $_POST["order_id"];
    $newStatus = $_POST["status"] ?? "";

    if (!in_array($newStatus, $validStatuses, true)) {

        $error = "Invalid status selected.";

    } else {

        $stmt = $conn->prepare("UPDATE orders SET status = ? WHERE order_id = ?");
        $stmt->bind_param("si", $newStatus, $orderId);
        $stmt->execute();
        $stmt->close();

        $success = "Order #" . $orderId . " updated to \"" . $newStatus . "\".";
    }
}

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
    <title>Orders - Mang Inasal Staff</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../style.css">
    <link rel="stylesheet" href="../admin-style.css">
</head>

<body>

<div class="admin-layout">

    <?php include "includes/sidebar.php"; ?>

    <main class="admin-main">

        <h1>Orders</h1>
        <p class="page-subtitle">Update an order's status as it moves through the kitchen.</p>

        <?php if ($error !== ""): ?>
            <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <?php if ($success !== ""): ?>
            <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

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

                        <form method="POST" style="display:inline-flex; gap:8px;">
                            <input type="hidden" name="order_id" value="<?= (int) $order["order_id"] ?>">
                            <select name="status">
                                <?php foreach ($validStatuses as $statusOption): ?>
                                    <option value="<?= $statusOption ?>" <?= $statusOption === $order["status"] ? "selected" : "" ?>>
                                        <?= ucfirst($statusOption) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <button type="submit" name="update_status" value="1" class="btn-secondary">Update</button>
                        </form>
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