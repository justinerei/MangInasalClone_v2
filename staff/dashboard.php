<?php

require_once "../includes/auth.php";

requireRole("staff");

?>

<!DOCTYPE html>

<html>

<head>

    <title>Staff Dashboard</title>

</head>

<body>

    <h1>Staff Dashboard</h1>

    <p>
        Welcome,
        <?= htmlspecialchars($_SESSION["username"]) ?>!
    </p>

    <p>
        Role:
        <?= htmlspecialchars($_SESSION["role"]) ?>
    </p>

    <hr>

    <h2>Staff Functions</h2>

    <ul>
        <li>View Incoming Orders</li>
        <li>Update Order Status</li>
    </ul>

    <br>

    <a href="../logout.php">
        Logout
    </a>

</body>

</html>