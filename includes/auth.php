<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . "/../config/database.php";


/*
|--------------------------------------------------------------------------
| Check if user is logged in
|--------------------------------------------------------------------------
*/

function requireLogin()
{
    if (!isset($_SESSION['user_id'])) {
        header("Location: ../login.php");
        exit();
    }
}


/*
|--------------------------------------------------------------------------
| Check user's role
|--------------------------------------------------------------------------
*/

function requireRole($allowedRole)
{
    requireLogin();

    if ($_SESSION['role'] !== $allowedRole) {
        http_response_code(403);

        echo "
        <!DOCTYPE html>
        <html>
        <head>
            <title>Access Denied</title>

            <style>

                body {
                    font-family: Arial, sans-serif;
                    text-align: center;
                    padding-top: 100px;
                    background-color: #f5f5f5;
                }

                .error-box {
                    background-color: white;
                    width: 400px;
                    max-width: 90%;
                    margin: auto;
                    padding: 40px;
                    border-radius: 10px;
                    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
                }

                h1 {
                    color: #b30000;
                }

                p {
                    color: #555;
                }

                a {
                    display: inline-block;
                    margin-top: 15px;
                    padding: 10px 20px;
                    background-color: #2D9751;
                    color: white;
                    text-decoration: none;
                    border-radius: 6px;
                }

                a:hover {
                    background-color: #1D4F1F;
                }

            </style>

        </head>

        <body>

            <div class='error-box'>

                <h1>Access Denied</h1>

                <p>
                    You are not authorized to access this page.
                </p>

                <a href='../index.php'>
                    Return to Mang Inasal
                </a>

            </div>

        </body>
        </html>
        ";

        exit();
    }
}


/*
|--------------------------------------------------------------------------
| Check current login status
|--------------------------------------------------------------------------
*/

function isLoggedIn()
{
    return isset($_SESSION['user_id']);
}


/*
|--------------------------------------------------------------------------
| Keep admin/staff out of the customer-facing site
|--------------------------------------------------------------------------
| Call this at the top of root-level pages (index.php, mainCourse.php,
| drinks.php, dessert.php, cart.php). Guests and customers pass through
| untouched; admin/staff get bounced to their own dashboard instead.
*/

function requireCustomerFacing()
{
    if (!isset($_SESSION['role'])) {
        return;
    }

    if ($_SESSION['role'] === 'admin') {
        header("Location: admin/dashboard.php");
        exit();
    }

    if ($_SESSION['role'] === 'staff') {
        header("Location: staff/dashboard.php");
        exit();
    }
}

/*
|--------------------------------------------------------------------------
| Load the current session user into $isLoggedIn / $username / $role
|--------------------------------------------------------------------------
| Every customer-facing page needs these three variables for its header.
| Pulling the logic into one function means it can't quietly go missing
| from one page while staying correct on the others.
*/

function loadSessionUser()
{
    global $isLoggedIn, $username, $role;

    $isLoggedIn = isset($_SESSION['user_id']);
    $username = $isLoggedIn ? $_SESSION['username'] : null;
    $role = $isLoggedIn ? $_SESSION['role'] : null;
}

?>