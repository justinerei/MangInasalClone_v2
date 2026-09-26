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

</head>

<body>

    <h1>Create Account</h1>

    <?php if ($error !== ""): ?>

        <p style="color: red;">
            <?= htmlspecialchars($error) ?>
        </p>

    <?php endif; ?>


    <?php if ($success !== ""): ?>

        <p style="color: green;">
            <?= htmlspecialchars($success) ?>
        </p>

    <?php endif; ?>


    <form method="POST">

        <div>

            <label>
                Username
            </label>

            <br>

            <input
                type="text"
                name="username"
                required
            >

        </div>

        <br>


        <div>

            <label>
                Password
            </label>

            <br>

            <input
                type="password"
                name="password"
                required
            >

        </div>

        <br>


        <div>

            <label>
                Confirm Password
            </label>

            <br>

            <input
                type="password"
                name="confirm_password"
                required
            >

        </div>

        <br>


        <button type="submit">
            Register
        </button>

    </form>

    <br>

    <a href="login.php">
        Already have an account? Login
    </a>

</body>

</html>