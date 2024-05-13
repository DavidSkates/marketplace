<?php
// Load environment variables from .env file
if (file_exists('.env')) {
    $env = parse_ini_file('.env');
    foreach ($env as $key => $value) {
        putenv("$key=$value");
    }
} else {
    header('Content-Type: application/json');
    echo json_encode(array("status" => "error", "message" => "No .env file found"));
    exit();
}

// Establish database connection
$servername = getenv('DB_SERVER');
$username = getenv('DB_USERNAME');
$password = getenv('DB_PASSWORD');
$dbname = getenv('DB_NAME');

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    header('Content-Type: application/json');
    echo json_encode(array("status" => "error", "message" => "Connection failed: " . $conn->connect_error));
    exit();
}

// Check if user is logged in
session_start();

$response = array();

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    $response['loggedin'] = false;
} else {
    $response['loggedin'] = true;
    $response['username'] = $_SESSION['username'];
}

header('Content-Type: application/json');
echo json_encode($response);


// Close database connection
$conn->close();
?>