<?php

session_start();

if (isset($_SESSION["user_id"])) {

    if ($_SESSION["role"] === "admin") {
        header("Location: admin/dashboard.php");
        exit();
    }

    if ($_SESSION["role"] === "staff") {
        header("Location: staff/dashboard.php");
        exit();
    }

    header("Location: index.php");
    exit();
}

require_once "config/database.php";

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $username = trim($_POST["username"] ?? "");
    $password = $_POST["password"] ?? "";

    if ($username === "" || $password === "") {

        $error = "Please enter your username and password.";

    } else {

        /*
        |--------------------------------------------------------------------------
        | Prepared Statement
        |--------------------------------------------------------------------------
        */

        $stmt = $conn->prepare(
            "SELECT user_id, username, password, role
             FROM users
             WHERE username = ?"
        );

        $stmt->bind_param("s", $username);

        $stmt->execute();

        $result = $stmt->get_result();

        if ($result->num_rows === 1) {

            $user = $result->fetch_assoc();

            /*
            |--------------------------------------------------------------------------
            | Verify Password
            |--------------------------------------------------------------------------
            */

            if (password_verify($password, $user["password"])) {

                /*
                |--------------------------------------------------------------------------
                | Prevent Session Fixation
                |--------------------------------------------------------------------------
                */

                session_regenerate_id(true);

                $_SESSION["user_id"] = $user["user_id"];
                $_SESSION["username"] = $user["username"];
                $_SESSION["role"] = $user["role"];

                /*
                |--------------------------------------------------------------------------
                | Redirect according to role
                |--------------------------------------------------------------------------
                */

                if ($user["role"] === "admin") {

                    header("Location: admin/dashboard.php");

                } elseif ($user["role"] === "staff") {

                    header("Location: staff/dashboard.php");

                } else {

                    header("Location: index.php");
                }

                exit();

            } else {

                $error = "Invalid username or password.";
            }

        } else {

            $error = "Invalid username or password.";
        }

        $stmt->close();
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Login - Mang Inasal</title>

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="auth-style.css">

</head>

<body>

<div class="auth-page">

    <div class="auth-card">

        <img src="images/logo.png" alt="Mang Inasal" class="auth-logo">

        <h1>Welcome Back</h1>
        <p class="auth-subtitle">Log in to your Mang Inasal account</p>

        <?php if ($error !== ""): ?>
            <div class="auth-alert error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST" class="auth-form">

            <div class="form-group">
                <label>Username</label>
                <input type="text" name="username" required>
            </div>

            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" required>
            </div>

            <button type="submit" class="btn-primary">Login</button>

        </form>

        <p class="auth-footer">
            Don't have an account? <a href="register.php">Register</a>
        </p>

    </div>

</div>

</body>

</html>