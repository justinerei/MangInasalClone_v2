<?php

require_once "../includes/auth.php";

requireRole("admin");

require_once "../config/database.php";

$error = "";
$success = "";


/*
|--------------------------------------------------------------------------
| Add a category
|--------------------------------------------------------------------------
*/

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["add_category"])) {

    $name = trim($_POST["category_name"] ?? "");

    if ($name === "") {

        $error = "Category name is required.";

    } else {

        try {

            $stmt = $conn->prepare("INSERT INTO categories (category_name) VALUES (?)");
            $stmt->bind_param("s", $name);
            $stmt->execute();
            $stmt->close();

            $success = "Category added.";

        } catch (mysqli_sql_exception $e) {

            $error = "Could not add category -- it may already exist.";
        }
    }
}


/*
|--------------------------------------------------------------------------
| Rename a category AND keep every product using it in sync
|--------------------------------------------------------------------------
| This is a small transaction of its own: if either update fails, neither
| should stick, or products would silently point at a category name that
| no longer exists in the categories table.
*/

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["rename_category"])) {

    $categoryId = (int) $_POST["category_id"];
    $oldName = trim($_POST["old_name"] ?? "");
    $newName = trim($_POST["new_name"] ?? "");

    if ($newName === "") {

        $error = "New category name is required.";

    } else {

        $conn->begin_transaction();

        try {

            $stmt = $conn->prepare("UPDATE categories SET category_name = ? WHERE category_id = ?");
            $stmt->bind_param("si", $newName, $categoryId);
            $stmt->execute();
            $stmt->close();

            $stmt = $conn->prepare("UPDATE products SET category = ? WHERE category = ?");
            $stmt->bind_param("ss", $newName, $oldName);
            $stmt->execute();
            $stmt->close();

            $conn->commit();

            $success = "Category renamed, and existing products were updated to match.";

        } catch (mysqli_sql_exception $e) {

            $conn->rollback();

            $error = "Could not rename category -- that name may already be taken.";
        }
    }
}


/*
|--------------------------------------------------------------------------
| Delete a category (only if no product is using it)
|--------------------------------------------------------------------------
*/

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["delete_id"])) {

    $deleteId = (int) $_POST["delete_id"];
    $deleteName = trim($_POST["delete_name"] ?? "");

    $countStmt = $conn->prepare("SELECT COUNT(*) AS total FROM products WHERE category = ?");
    $countStmt->bind_param("s", $deleteName);
    $countStmt->execute();
    $inUse = $countStmt->get_result()->fetch_assoc()["total"];
    $countStmt->close();

    if ($inUse > 0) {

        $error = "Can't delete \"$deleteName\" -- $inUse product(s) still use it. Reassign them first in Manage Products.";

    } else {

        $stmt = $conn->prepare("DELETE FROM categories WHERE category_id = ?");
        $stmt->bind_param("i", $deleteId);
        $stmt->execute();
        $stmt->close();

        $success = "Category deleted.";
    }
}


$categories = $conn->query("SELECT * FROM categories ORDER BY category_name");

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Categories - Mang Inasal Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../style.css">
    <link rel="stylesheet" href="../admin-style.css">
</head>

<body>

<div class="admin-layout">

    <?php include "includes/sidebar.php"; ?>

    <main class="admin-main">

        <h1>Manage Categories</h1>
        <p class="page-subtitle">These names populate the category dropdown in Manage Products.</p>

        <?php if ($error !== ""): ?>
            <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <?php if ($success !== ""): ?>
            <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <form method="POST" class="admin-form">
            <div class="form-group">
                <label>New Category Name</label>
                <input type="text" name="category_name" required>
            </div>
            <button type="submit" name="add_category" value="1" class="btn-primary">Add Category</button>
        </form>

        <br>

        <table class="admin-table">
            <tr>
                <th>Name</th>
                <th>Actions</th>
            </tr>

            <?php while ($category = $categories->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($category["category_name"]) ?></td>
                    <td>

                        <form method="POST" style="display:inline-flex; gap:8px; align-items:center;">
                            <input type="hidden" name="category_id" value="<?= (int) $category["category_id"] ?>">
                            <input type="hidden" name="old_name" value="<?= htmlspecialchars($category["category_name"]) ?>">
                            <input type="text" name="new_name" placeholder="Rename to..." style="max-width:160px;">
                            <button type="submit" name="rename_category" value="1" class="btn-secondary">Rename</button>
                        </form>

                        &nbsp;

                        <form method="POST" style="display:inline;" onsubmit="return confirm('Delete this category?');">
                            <input type="hidden" name="delete_id" value="<?= (int) $category["category_id"] ?>">
                            <input type="hidden" name="delete_name" value="<?= htmlspecialchars($category["category_name"]) ?>">
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