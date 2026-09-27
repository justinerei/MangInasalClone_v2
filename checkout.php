<?php

require_once "includes/auth.php";

requireCustomerFacing();

require_once "config/database.php";

header("Content-Type: application/json");


/*
|--------------------------------------------------------------------------
| Must be logged in to place an order
|--------------------------------------------------------------------------
| orders.user_id points at a real customer row, so guests get bounced
| back to login instead of a broken insert.
*/

if (!isset($_SESSION["user_id"])) {
    http_response_code(401);
    echo json_encode([
        "success" => false,
        "message" => "Please log in to check out.",
        "redirect" => "login.php"
    ]);
    exit();
}

$userId = (int) $_SESSION["user_id"];


/*
|--------------------------------------------------------------------------
| Read + validate the request body
|--------------------------------------------------------------------------
*/

$payload = json_decode(file_get_contents("php://input"), true);

if (!is_array($payload) || empty($payload["items"]) || !is_array($payload["items"])) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "Your cart is empty."]);
    exit();
}

$rawItems = $payload["items"];
$age = isset($payload["age"]) ? (int) $payload["age"] : 0;
$seniorRequested = !empty($payload["seniorDiscount"]);

$SENIOR_MIN_AGE = 60;
$SENIOR_DISCOUNT_RATE = 0.20;
$SHIPPING_FEE = 50.00;


/*
|--------------------------------------------------------------------------
| Normalize + de-duplicate the incoming line items
|--------------------------------------------------------------------------
| The cart lives in localStorage, so nothing here can be trusted --
| product IDs and quantities are re-validated, and price always comes
| from the products table, never from the request body.
*/

$quantitiesByProduct = [];

foreach ($rawItems as $item) {

    if (!is_array($item) || !isset($item["id"]) || !isset($item["qty"])) {
        continue;
    }

    $productId = (int) $item["id"];
    $qty = (int) $item["qty"];

    if ($productId <= 0 || $qty <= 0) {
        continue;
    }

    $quantitiesByProduct[$productId] = ($quantitiesByProduct[$productId] ?? 0) + $qty;
}

if (empty($quantitiesByProduct)) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "Your cart is empty."]);
    exit();
}


/*
|--------------------------------------------------------------------------
| Recompute the senior/PWD discount server-side
|--------------------------------------------------------------------------
| The checkbox and age input are just UI -- eligibility is re-checked
| here so a tampered request can't force a discount through.
*/

$seniorEligible = $seniorRequested && $age >= $SENIOR_MIN_AGE;


/*
|--------------------------------------------------------------------------
| Process the order in a transaction
|--------------------------------------------------------------------------
| Same pattern as staff/process-order.php: lock each product row,
| verify stock, insert the order + its line items, then decrement
| stock -- all or nothing.
*/

$conn->begin_transaction();

try {

    $subtotal = 0.00;
    $lineItems = [];

    foreach ($quantitiesByProduct as $productId => $qty) {

        $stmt = $conn->prepare(
            "SELECT product_id, product_name, price, quantity
             FROM products
             WHERE product_id = ?
             FOR UPDATE"
        );
        $stmt->bind_param("i", $productId);
        $stmt->execute();
        $product = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$product) {
            throw new Exception("One of the items in your cart is no longer available.");
        }

        if ($qty > (int) $product["quantity"]) {
            throw new Exception(
                "Only " . $product["quantity"] . " \"" . $product["product_name"] . "\" left in stock."
            );
        }

        $lineSubtotal = (float) $product["price"] * $qty;
        $subtotal += $lineSubtotal;

        $lineItems[] = [
            "product_id" => $productId,
            "product_name" => $product["product_name"],
            "qty" => $qty,
            "subtotal" => $lineSubtotal
        ];
    }

    $discountAmount = $seniorEligible ? round($subtotal * $SENIOR_DISCOUNT_RATE, 2) : 0.00;
    $shippingFee = $SHIPPING_FEE;
    $totalAmount = $subtotal - $discountAmount + $shippingFee;

    $stmt = $conn->prepare(
        "INSERT INTO orders (user_id, order_date, total_amount, discount_amount, shipping_fee)
         VALUES (?, NOW(), ?, ?, ?)"
    );
    $stmt->bind_param("iddd", $userId, $totalAmount, $discountAmount, $shippingFee);
    $stmt->execute();
    $orderId = $conn->insert_id;
    $stmt->close();

    $detailStmt = $conn->prepare(
        "INSERT INTO order_details (order_id, product_id, quantity, subtotal)
         VALUES (?, ?, ?, ?)"
    );

    $stockStmt = $conn->prepare(
        "UPDATE products
         SET quantity = quantity - ?
         WHERE product_id = ? AND quantity >= ?"
    );

    foreach ($lineItems as $line) {

        $detailStmt->bind_param(
            "iiid",
            $orderId,
            $line["product_id"],
            $line["qty"],
            $line["subtotal"]
        );
        $detailStmt->execute();

        $stockStmt->bind_param("iii", $line["qty"], $line["product_id"], $line["qty"]);
        $stockStmt->execute();

        if ($stockStmt->affected_rows === 0) {
            throw new Exception(
                "Stock changed for \"" . $line["product_name"] . "\" before your order could be saved."
            );
        }
    }

    $detailStmt->close();
    $stockStmt->close();

    $conn->commit();

    echo json_encode([
        "success" => true,
        "orderId" => $orderId,
        "subtotal" => round($subtotal, 2),
        "discount" => $discountAmount,
        "shipping" => $shippingFee,
        "total" => round($totalAmount, 2)
    ]);

} catch (Exception $e) {

    $conn->rollback();

    http_response_code(409);
    echo json_encode([
        "success" => false,
        "message" => $e->getMessage()
    ]);
}

?>