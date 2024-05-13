<?php
session_start(); // Start the session

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

header('Content-Type: application/json');

// Get the usernames from the query parameters
$userX = $_GET['userX'];
$userY = $_GET['userY'];

// Prepare the SQL statement
$stmt = $conn->prepare('SELECT a.favorited_user FROM favorites a INNER JOIN favorites b ON a.favorited_user = b.favorited_user WHERE a.user = ? AND b.user = ?');

// Bind the parameter
$stmt->bind_param('ss', $userX, $userY);

// Execute the statement
$stmt->execute();

// Get the result
$result = $stmt->get_result();

// Fetch the users and echo them as a JSON array
$favorites = $result->fetch_all(MYSQLI_ASSOC);
echo json_encode($favorites);
?>