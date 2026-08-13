<?php
// Function to load .env file manually
function loadEnv($path) {
    if (!file_exists($path)) {
        return;
    }

    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);

        if (strpos($line, '#') === 0 || strpos($line, '=') === false) {
            continue;
        }

        list($name, $value) = explode('=', $line, 2);

        // Strip whitespace and quotes
        $value = trim($value, " \t\n\r\0\x0B\"'");

        $_ENV[trim($name)] = $value;
    }
}

// Load .env from project root
loadEnv(__DIR__ . '/../.env');

$host = $_ENV['DB_HOST'] ?? 'localhost';
$user = $_ENV['DB_USER'] ?? 'root';
$pass = $_ENV['DB_PASS'] ?? '';
$name = $_ENV['DB_NAME'] ?? 'blog_app';

$conn = new mysqli($host, $user, $pass, $name);

if ($conn->connect_error) {
    die("Database Connection Failed: " . $conn->connect_error);
}
?>