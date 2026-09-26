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

    header("Location: customer/dashboard.php");
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

                    header("Location: customer/dashboard.php");
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

</head>

<body>

    <h1>Login</h1>

    <?php if ($error !== ""): ?>

        <p style="color: red;">
            <?= htmlspecialchars($error) ?>
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


        <button type="submit">
            Login
        </button>

    </form>

</body>

</html>