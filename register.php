<?php

session_start();

require_once "config/database.php";

$error = "";
$success = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $username = trim($_POST["username"] ?? "");
    $password = $_POST["password"] ?? "";
    $confirmPassword = $_POST["confirm_password"] ?? "";

    // Validate required fields
    if ($username === "" || $password === "" || $confirmPassword === "") {

        $error = "All fields are required.";

    // Validate password match
    } elseif ($password !== $confirmPassword) {

        $error = "Passwords do not match.";

    // Basic password length validation
    } elseif (strlen($password) < 6) {

        $error = "Password must be at least 6 characters.";

    } else {

        /*
        |--------------------------------------------------------------------------
        | Check if username already exists
        |--------------------------------------------------------------------------
        */

        $stmt = $conn->prepare(
            "SELECT user_id
             FROM users
             WHERE username = ?"
        );

        $stmt->bind_param("s", $username);
        $stmt->execute();

        $result = $stmt->get_result();

        if ($result->num_rows > 0) {

            $error = "Username is already taken.";

        } else {

            /*
            |--------------------------------------------------------------------------
            | Hash password
            |--------------------------------------------------------------------------
            */

            $hashedPassword = password_hash(
                $password,
                PASSWORD_DEFAULT
            );

            /*
            |--------------------------------------------------------------------------
            | New public accounts are ALWAYS customers
            |--------------------------------------------------------------------------
            */

            $role = "customer";

            /*
            |--------------------------------------------------------------------------
            | Insert user using prepared statement
            |--------------------------------------------------------------------------
            */

            $stmt = $conn->prepare(
                "INSERT INTO users
                (username, password, role)
                VALUES (?, ?, ?)"
            );

            $stmt->bind_param(
                "sss",
                $username,
                $hashedPassword,
                $role
            );

            if ($stmt->execute()) {

                $success = "Registration successful! You can now log in.";

            } else {

                $error = "Registration failed. Please try again.";
            }
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

    <title>Register - Mang Inasal</title>

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="auth-style.css">

</head>

<body>

<div class="auth-page">

    <div class="auth-card">

        <img src="images/logo.png" alt="Mang Inasal" class="auth-logo">

        <h1>Create Account</h1>
        <p class="auth-subtitle">Join Mang Inasal to start ordering</p>

        <?php if ($error !== ""): ?>
            <div class="auth-alert error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <?php if ($success !== ""): ?>
            <div class="auth-alert success"><?= htmlspecialchars($success) ?></div>
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

            <div class="form-group">
                <label>Confirm Password</label>
                <input type="password" name="confirm_password" required>
            </div>

            <button type="submit" class="btn-primary">Register</button>

        </form>

        <p class="auth-footer">
            Already have an account? <a href="login.php">Login</a>
        </p>

    </div>

</div>

</body>

</html>