<?php

require_once "../includes/auth.php";

requireRole("customer");

?>

<!DOCTYPE html>

<html>

<head>

    <title>Customer Dashboard</title>

</head>

<body>

    <h1>Customer Dashboard</h1>

    <p>
        Welcome,
        <?= htmlspecialchars($_SESSION["username"]) ?>!
    </p>

    <p>
        Role:
        <?= htmlspecialchars($_SESSION["role"]) ?>
    </p>

    <hr>

    <p>
        You are logged in as a customer.
    </p>

    <a href="../index.html">
        Browse Menu
    </a>

    <br><br>

    <a href="../logout.php">
        Logout
    </a>

</body>

</html>