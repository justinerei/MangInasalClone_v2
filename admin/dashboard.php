<?php

require_once "../includes/auth.php";

requireRole("admin");

?>

<!DOCTYPE html>

<html>

<head>

    <title>Admin Dashboard</title>

</head>

<body>

    <h1>Admin Dashboard</h1>

    <p>
        Welcome,
        <?= htmlspecialchars($_SESSION["username"]) ?>!
    </p>

    <p>
        Role:
        <?= htmlspecialchars($_SESSION["role"]) ?>
    </p>

    <hr>

    <h2>Admin Functions</h2>

    <ul>
        <li>Manage Products</li>
        <li>Manage Users</li>
        <li>View All Orders</li>
    </ul>

    <br>

    <a href="../logout.php">
        Logout
    </a>

</body>

</html>