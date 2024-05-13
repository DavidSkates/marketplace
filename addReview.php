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
$itemName = $_POST["itemName"];
$rating = $_POST["rating"];
$description = $_POST["description"];
$username = $_SESSION['username'];
$date = date('Y-m-d');

// Check if the user has selected an item
if ($itemName == "Select an item") {
    echo json_encode(array("status" => "error", "message" => "Please select an item."));
    exit();
}

// Query to check if the user is trying to review their own item
$stmt = $conn->prepare("SELECT COUNT(*) as item_count FROM items WHERE username = ? AND title = ?");
$stmt->bind_param("ss", $username, $itemName);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

// Check if the user is trying to review their own item
if ($row['item_count'] > 0) {
    echo json_encode(array("status" => "error", "message" => "You cannot review your own item."));
} else {
    // Query to count the number of items the user has reviewed today
    $stmt = $conn->prepare("SELECT COUNT(*) as review_count FROM reviews WHERE username = ? AND date = ?");
    $stmt->bind_param("ss", $username, $date);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    // Check if the user has already added 3 reviews today
    if ($row['review_count'] >= 3) {
        echo json_encode(array("status" => "error", "message" => "You have already added 3 reviews today."));
    } else {
        // Query to check if the user has already reviewed this item
        $stmt = $conn->prepare("SELECT COUNT(*) as review_count FROM reviews WHERE username = ? AND itemName = ?");
        $stmt->bind_param("ss", $username, $itemName);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();

        // Check if the user has already reviewed this item
        if ($row['review_count'] > 0) {
            echo json_encode(array("status" => "error", "message" => "You have already reviewed this item."));
        }else{
            // Insert the review data into the database
            $stmt = $conn->prepare("INSERT INTO reviews (itemName, rating, description, username, date) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("sssss", $itemName, $rating, $description, $username, $date);

            if ($stmt->execute()) {
                echo json_encode(array("status" => "success", "message" => "Review added successfully!"));
            } else {
                echo json_encode(array("status" => "error", "message" => "Failed to add review: " . $stmt->error));
            }
        }
    }
}

// Close database connection
$conn->close();
?>