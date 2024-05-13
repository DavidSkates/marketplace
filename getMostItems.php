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

// Get the date from the query parameters
$date = $_GET['date'];

// Prepare the SQL statement
$stmt = $conn->prepare('SELECT username, COUNT(*) as postCount FROM items WHERE DATE(date_posted) = ? GROUP BY username HAVING postCount = (SELECT COUNT(*) as postCount FROM items WHERE DATE(date_posted) = ? GROUP BY username ORDER BY postCount DESC LIMIT 1)');

// Bind the date parameter to the SQL statement
$stmt->bind_param("ss", $date, $date);

// Execute the statement
$stmt->execute();

// Get the result
$result = $stmt->get_result();

// Fetch all the results
$results = $result->fetch_all(MYSQLI_ASSOC);

// Output the results as JSON
echo json_encode($results);

// Close database connection
$conn->close();
?>