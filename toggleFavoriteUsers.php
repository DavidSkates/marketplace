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

$username = $_SESSION['username'];
$favoritedUser = $_GET['username'];

// Check if the user is already a favorite
$stmt = $conn->prepare('SELECT * FROM favorites WHERE user = ? AND favorited_user = ?');
$stmt->bind_param('ss', $username, $favoritedUser);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    // If the user is already a favorite, remove them from the favorites
    $stmt = $conn->prepare('DELETE FROM favorites WHERE user = ? AND favorited_user = ?');
} else {
    // If the user is not a favorite, add them to the favorites
    $stmt = $conn->prepare('INSERT INTO favorites (user, favorited_user) VALUES (?, ?)');
}

$stmt->bind_param('ss', $username, $favoritedUser);
$stmt->execute();

// Close database connection
$conn->close();
?>