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

// Get the item data from the form data
$title = $_POST["title"];
$description = $_POST["description"];
$category = $_POST["category"];
$price = $_POST["price"];
$username = $_SESSION['username'];
$date_posted = date('Y-m-d');

// Count the number of items the user has added today
$stmt = $conn->prepare("SELECT COUNT(*) as item_count FROM items WHERE username = ? AND date_posted = ?");
$stmt->bind_param("ss", $username, $date_posted);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

// Check if the user has already added 2 items today
if ($row['item_count'] >= 2) {
    echo json_encode(array("status" => "error", "message" => "You have already added 2 items today."));
}else{
    // Insert the item data into the database
    $stmt = $conn->prepare("INSERT INTO items (title, description, category, price, username, date_posted) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssss", $title, $description, $category, $price, $username, $date_posted);

    if ($stmt->execute()) {
    echo json_encode(array("status" => "success", "message" => "New record created successfully"));
    } else {
    echo json_encode(array("status" => "error", "message" => "Error: " . $stmt->error));
    }
}

// Close database connection
$conn->close();
?>