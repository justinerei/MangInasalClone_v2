<?php

require_once "../includes/auth.php";

requireRole("staff");

require_once "../config/database.php";

$error = "";
$success = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $customerId = trim($_POST["customer_id"] ?? "");
    $productId = trim($_POST["product_id"] ?? "");
    $quantity = trim($_POST["quantity"] ?? "");

    if ($customerId === "" || $productId === "" || $quantity === "") {

        $error = "Please select a customer, a product, and a quantity.";

    } elseif (!ctype_digit((string) $quantity) || (int) $quantity < 1) {

        $error = "Quantity must be a whole number greater than zero.";

    } else {

        $customerId = (int) $customerId;
        $productId = (int) $productId;
        $quantity = (int) $quantity;

        $conn->begin_transaction();

        try {

            $stmt = $conn->prepare(
                "SELECT price, quantity, product_name
                 FROM products
                 WHERE product_id = ?
                 FOR UPDATE"
            );
            $stmt->bind_param("i", $productId);
            $stmt->execute();
            $product = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            if (!$product) {
                throw new Exception("Selected product no longer exists.");
            }

            if ($quantity > (int) $product["quantity"]) {
                throw new Exception(
                    "Invalid product quantity -- only " . $product["quantity"] .
                    " \"" . $product["product_name"] . "\" left in stock."
                );
            }

            $subtotal = $product["price"] * $quantity;

            $stmt = $conn->prepare(
                "INSERT INTO orders (user_id, order_date, total_amount)
                 VALUES (?, NOW(), ?)"
            );
            $stmt->bind_param("id", $customerId, $subtotal);
            $stmt->execute();
            $orderId = $conn->insert_id;
            $stmt->close();

            $stmt = $conn->prepare(
                "INSERT INTO order_details (order_id, product_id, quantity, subtotal)
                 VALUES (?, ?, ?, ?)"
            );
            $stmt->bind_param("iiid", $orderId, $productId, $quantity, $subtotal);
            $stmt->execute();
            $stmt->close();

            $stmt = $conn->prepare(
                "UPDATE products
                 SET quantity = quantity - ?
                 WHERE product_id = ? AND quantity >= ?"
            );
            $stmt->bind_param("iii", $quantity, $productId, $quantity);
            $stmt->execute();

            if ($stmt->affected_rows === 0) {
                throw new Exception("Stock changed before this order could be saved.");
            }

            $stmt->close();

            $conn->commit();

            $success = "Order #" . $orderId . " processed successfully for "
                . $quantity . "x " . $product["product_name"] . ".";

        } catch (Exception $e) {

            $conn->rollback();

            $error = "Transaction rolled back: " . $e->getMessage();
        }
    }
}

$customers = $conn->query(
    "SELECT user_id, username FROM users WHERE role = 'customer' ORDER BY username"
);

$productsList = $conn->query(
    "SELECT product_id, product_name, price, quantity FROM products ORDER BY product_name"
);

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Process New Order - Mang Inasal Staff</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../style.css">
    <link rel="stylesheet" href="../admin-style.css">
</head>

<body>

<div class="admin-layout">

    <?php include "includes/sidebar.php"; ?>

    <main class="admin-main">

        <h1>Process New Order</h1>
        <p class="page-subtitle">For walk-in or phone orders taken by staff.</p>

        <?php if ($error !== ""): ?>
            <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <?php if ($success !== ""): ?>
            <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <form method="POST" class="admin-form">

            <div class="form-group">
                <label>Customer</label>
                <select name="customer_id" required>
                    <option value="">-- Select customer --</option>
                    <?php while ($customer = $customers->fetch_assoc()): ?>
                        <option value="<?= (int) $customer["user_id"] ?>">
                            <?= htmlspecialchars($customer["username"]) ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="form-group">
                <label>Product</label>
                <select name="product_id" required>
                    <option value="">-- Select product --</option>
                    <?php while ($product = $productsList->fetch_assoc()): ?>
                        <option value="<?= (int) $product["product_id"] ?>">
                            <?= htmlspecialchars($product["product_name"]) ?>
                            -- &#8369;<?= htmlspecialchars($product["price"]) ?>
                            (<?= (int) $product["quantity"] ?> in stock)
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="form-group">
                <label>Quantity</label>
                <input type="number" name="quantity" min="1" step="1" required>
            </div>

            <button type="submit" class="btn-primary">Process Order</button>

        </form>

    </main>

</div>

</body>

</html>