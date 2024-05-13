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

// Get the user's ID from the request parameters
$user = $_POST['user'];

// Prepare the SQL statement
$stmt = $conn->prepare('SELECT items.title FROM items INNER JOIN reviews ON items.title = reviews.itemName WHERE items.username = ? AND LOWER(reviews.rating) IN ("good", "excellent") AND items.title NOT IN (SELECT itemName FROM reviews WHERE LOWER(rating) IN ("poor", "fair")) GROUP BY items.title');

// Bind the user's ID to the SQL statement
$stmt->bind_param('s', $user);

// Execute the statement
if (!$stmt->execute()) {
    echo json_encode(array("status" => "error", "message" => "Error executing statement: " . $stmt->error));
    exit();
}

// Get the result
$result = $stmt->get_result();
if ($result === false) {
    echo json_encode(array("status" => "error", "message" => "Error fetching results: " . $stmt->error));
    exit();
}

// Fetch all rows as an associative array
$rows = $result->fetch_all(MYSQLI_ASSOC);

// Return the rows as a JSON response
echo json_encode($rows);

// Close database connection
$conn->close();
?>