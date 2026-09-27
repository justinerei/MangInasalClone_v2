<?php

/*
|--------------------------------------------------------------------------
| Make mysqli throw exceptions instead of failing silently
|--------------------------------------------------------------------------
| Without this, a failed query just returns false and you have to remember
| to check every single call. With it, any DB error (a bad query, a broken
| foreign key, a dropped table) throws a catchable exception -- which is
| exactly what a transaction's try/catch/rollback needs to work correctly.
*/

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$host = "localhost";
$username = "root";
$password = "";
$database = "manginasal_db";

try {
    $conn = new mysqli($host, $username, $password, $database);
    $conn->set_charset("utf8mb4");
} catch (mysqli_sql_exception $e) {
    die("Database connection failed: " . $e->getMessage());
}

?>