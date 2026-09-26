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
                }

                h1 {
                    color: #b30000;
                }

                a {
                    text-decoration: none;
                    color: #0066cc;
                }
            </style>
        </head>

        <body>

            <h1>Access Denied</h1>

            <p>
                You are not authorized to access this page.
            </p>

            <a href="/index.php">
                Return to Mang Inasal
            </a>

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

?>