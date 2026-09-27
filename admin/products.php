<?php

require_once "../includes/auth.php";

requireRole("admin");

require_once "../config/database.php";

$error = "";
$success = "";


/*
|--------------------------------------------------------------------------
| Handle delete
|--------------------------------------------------------------------------
*/

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["delete_id"])) {

    $deleteId = (int) $_POST["delete_id"];

    $stmt = $conn->prepare("DELETE FROM products WHERE product_id = ?");
    $stmt->bind_param("i", $deleteId);
    $stmt->execute();
    $stmt->close();

    $success = "Product deleted.";
}


/*
|--------------------------------------------------------------------------
| Load a product into the form for editing
|--------------------------------------------------------------------------
*/

$editProduct = null;

if (isset($_GET["edit"])) {

    $editId = (int) $_GET["edit"];

    $stmt = $conn->prepare("SELECT * FROM products WHERE product_id = ?");
    $stmt->bind_param("i", $editId);
    $stmt->execute();
    $editProduct = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}


/*
|--------------------------------------------------------------------------
| Handle add / update
|--------------------------------------------------------------------------
*/

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["save_product"])) {

    $productId = (isset($_POST["product_id"]) && $_POST["product_id"] !== "")
        ? (int) $_POST["product_id"]
        : null;

    $name = trim($_POST["product_name"] ?? "");
    $description = trim($_POST["description"] ?? "");
    $category = trim($_POST["category"] ?? "");
    $price = trim($_POST["price"] ?? "");
    $quantity = trim($_POST["quantity"] ?? "");
    $imagePath = trim($_POST["image_path"] ?? "");

    if ($name === "" || $category === "" || $price === "" || $quantity === "") {

        $error = "Name, category, price, and quantity are required.";

    } elseif (!is_numeric($price) || (float) $price < 0) {

        $error = "Price must be a positive number.";

    } elseif (!ctype_digit((string) $quantity)) {

        $error = "Quantity must be a positive whole number.";

    } else {

        $price = (float) $price;
        $quantity = (int) $quantity;

        try {

            if ($productId) {

                $stmt = $conn->prepare(
                    "UPDATE products
                     SET product_name = ?, description = ?, category = ?,
                         price = ?, quantity = ?, image_path = ?
                     WHERE product_id = ?"
                );

                $stmt->bind_param(
                    "sssdisi",
                    $name,
                    $description,
                    $category,
                    $price,
                    $quantity,
                    $imagePath,
                    $productId
                );

                $stmt->execute();
                $stmt->close();

                $success = "Product updated.";

            } else {

                $stmt = $conn->prepare(
                    "INSERT INTO products
                     (product_name, description, category, price, quantity, image_path)
                     VALUES (?, ?, ?, ?, ?, ?)"
                );

                $stmt->bind_param(
                    "sssdis",
                    $name,
                    $description,
                    $category,
                    $price,
                    $quantity,
                    $imagePath
                );

                $stmt->execute();
                $stmt->close();

                $success = "Product added.";
            }

            $editProduct = null;

        } catch (mysqli_sql_exception $e) {

            $error = "Could not save product: " . $e->getMessage();
        }
    }
}


/*
|--------------------------------------------------------------------------
| Data for the page
|--------------------------------------------------------------------------
*/

$products = $conn->query("SELECT * FROM products ORDER BY product_id DESC");

$categoriesResult = $conn->query("SELECT category_name FROM categories ORDER BY category_name");
$categoryNames = [];

while ($row = $categoriesResult->fetch_assoc()) {
    $categoryNames[] = $row["category_name"];
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Products - Mang Inasal Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../style.css">
    <link rel="stylesheet" href="../admin-style.css">
</head>

<body>

<div class="admin-layout">

    <?php include "includes/sidebar.php"; ?>

    <main class="admin-main">

        <h1>Manage Products</h1>
        <p class="page-subtitle">Add, edit, and remove menu items.</p>

        <?php if ($error !== ""): ?>
            <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <?php if ($success !== ""): ?>
            <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <h2 style="margin-bottom: 16px;"><?= $editProduct ? "Edit Product" : "Add New Product" ?></h2>

        <form method="POST" class="admin-form">

            <?php if ($editProduct): ?>
                <input type="hidden" name="product_id" value="<?= (int) $editProduct["product_id"] ?>">
            <?php endif; ?>

            <div class="form-group">
                <label>Product Name</label>
                <input type="text" name="product_name" required
                       value="<?= htmlspecialchars($editProduct["product_name"] ?? "") ?>">
            </div>

            <div class="form-group">
                <label>Description</label>
                <input type="text" name="description"
                       value="<?= htmlspecialchars($editProduct["description"] ?? "") ?>">
            </div>

            <div class="form-group">
                <label>Category</label>
                <select name="category" required>
                    <option value="">-- Select category --</option>
                    <?php foreach ($categoryNames as $categoryName): ?>
                        <option value="<?= htmlspecialchars($categoryName) ?>"
                            <?= (isset($editProduct["category"]) && $editProduct["category"] === $categoryName) ? "selected" : "" ?>>
                            <?= htmlspecialchars($categoryName) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <br>
                <small><a href="categories.php">Manage categories</a></small>
            </div>

            <div class="form-group">
                <label>Price (Peso)</label>
                <input type="number" step="0.01" min="0" name="price" required
                       value="<?= htmlspecialchars($editProduct["price"] ?? "") ?>">
            </div>

            <div class="form-group">
                <label>Quantity</label>
                <input type="number" step="1" min="0" name="quantity" required
                       value="<?= htmlspecialchars($editProduct["quantity"] ?? "") ?>">
            </div>

            <div class="form-group">
                <label>Image Path</label>
                <input type="text" name="image_path"
                       value="<?= htmlspecialchars($editProduct["image_path"] ?? "") ?>">
            </div>

            <button type="submit" name="save_product" value="1" class="btn-primary">
                <?= $editProduct ? "Update Product" : "Add Product" ?>
            </button>

            <?php if ($editProduct): ?>
                <a href="products.php" class="btn-secondary">Cancel</a>
            <?php endif; ?>

        </form>

        <hr style="margin: 30px 0; border: none; border-top: 1px solid var(--border-color);">

        <h2 style="margin-bottom: 16px;">Existing Products</h2>

        <table class="admin-table">
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Category</th>
                <th>Price</th>
                <th>Quantity</th>
                <th>Actions</th>
            </tr>

            <?php while ($product = $products->fetch_assoc()): ?>
                <tr>
                    <td><?= (int) $product["product_id"] ?></td>
                    <td><?= htmlspecialchars($product["product_name"]) ?></td>
                    <td><?= htmlspecialchars($product["category"]) ?></td>
                    <td>&#8369;<?= htmlspecialchars($product["price"]) ?></td>
                    <td><?= (int) $product["quantity"] ?></td>
                    <td>
                        <a href="?edit=<?= (int) $product["product_id"] ?>" class="btn-secondary">Edit</a>

                        <form method="POST" style="display:inline;"
                              onsubmit="return confirm('Delete this product?');">
                            <input type="hidden" name="delete_id" value="<?= (int) $product["product_id"] ?>">
                            <button type="submit" class="btn-danger">Delete</button>
                        </form>
                    </td>
                </tr>
            <?php endwhile; ?>
        </table>

    </main>

</div>

</body>

</html>