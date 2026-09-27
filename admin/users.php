<?php

require_once "../includes/auth.php";

requireRole("admin");

require_once "../config/database.php";

$error = "";
$success = "";

$validRoles = ["customer", "staff", "admin"];

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["update_role"])) {

    $targetId = (int) $_POST["user_id"];
    $newRole = $_POST["role"] ?? "";

    if ($targetId === (int) $_SESSION["user_id"]) {

        $error = "You can't change your own role.";

    } elseif (!in_array($newRole, $validRoles, true)) {

        $error = "Invalid role selected.";

    } else {

        $stmt = $conn->prepare("UPDATE users SET role = ? WHERE user_id = ?");
        $stmt->bind_param("si", $newRole, $targetId);
        $stmt->execute();
        $stmt->close();

        $success = "Role updated.";
    }
}

$users = $conn->query("SELECT user_id, username, role FROM users ORDER BY username");

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users - Mang Inasal Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../style.css">
    <link rel="stylesheet" href="../admin-style.css">
</head>

<body>

<div class="admin-layout">

    <?php include "includes/sidebar.php"; ?>

    <main class="admin-main">

        <h1>Manage Users</h1>
        <p class="page-subtitle">View accounts and change roles.</p>

        <?php if ($error !== ""): ?>
            <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <?php if ($success !== ""): ?>
            <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <table class="admin-table">
            <tr>
                <th>Username</th>
                <th>Current Role</th>
                <th>Change Role</th>
            </tr>

            <?php while ($user = $users->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($user["username"]) ?></td>
                    <td><span class="role-badge"><?= htmlspecialchars($user["role"]) ?></span></td>
                    <td>

                        <?php if ((int) $user["user_id"] === (int) $_SESSION["user_id"]): ?>

                            <em>(this is you)</em>

                        <?php else: ?>

                            <form method="POST" style="display:inline-flex; gap:8px;">
                                <input type="hidden" name="user_id" value="<?= (int) $user["user_id"] ?>">
                                <select name="role">
                                    <?php foreach ($validRoles as $roleOption): ?>
                                        <option value="<?= $roleOption ?>" <?= $roleOption === $user["role"] ? "selected" : "" ?>>
                                            <?= ucfirst($roleOption) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <button type="submit" name="update_role" value="1" class="btn-secondary">Update</button>
                            </form>

                        <?php endif; ?>

                    </td>
                </tr>
            <?php endwhile; ?>
        </table>

    </main>

</div>

</body>

</html>