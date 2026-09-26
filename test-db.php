<?php

require_once "config/database.php";

$sql = "
    SELECT 
        orders.order_id,
        users.username,
        orders.order_date,
        orders.total_amount
    FROM orders
    INNER JOIN users
        ON orders.user_id = users.user_id
    ORDER BY orders.order_id DESC
";

$result = $conn->query($sql);

if (!$result) {
    die("Query failed: " . $conn->error);
}

echo "<h1>Orders</h1>";

if ($result->num_rows === 0) {
    echo "<p>No orders yet.</p>";
} else {

    while ($order = $result->fetch_assoc()) {

        echo "<p>";
        echo "Order #" . $order["order_id"];
        echo " | Customer: " . $order["username"];
        echo " | Date: " . $order["order_date"];
        echo " | Total: ₱" . $order["total_amount"];
        echo "</p>";

    }

}

?>