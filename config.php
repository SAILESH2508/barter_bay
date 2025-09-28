<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Connect to SQLite database (creates file if it doesn't exist)
class MyDB extends SQLite3 {
    function __construct() {
        $this->open('barter_bay.db');  // Database file name
    }
}

$conn = new MyDB();

if (!$conn) {
    die("Connection failed: " . $conn->lastErrorMsg());
}
?>
