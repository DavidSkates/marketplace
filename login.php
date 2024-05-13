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
    die("Connection failed: " . $conn->connect_error);
}

session_start();
// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize inputs
    $username = mysqli_real_escape_string($conn, $_POST["username"]);
    $password = mysqli_real_escape_string($conn, $_POST["password"]);

    // Check if the user exists
    $check_sql = "SELECT * FROM users WHERE username='$username'";
    $result = $conn->query($check_sql);

    if ($result->num_rows == 1) {
        // User found, verify password
        $row = $result->fetch_assoc();
        if (password_verify($password, $row["password"])) {
            // Password is correct, user authenticated
            $response = array("status" => "success", "redirect" => "index.html");
            $_SESSION['loggedin'] = true;
            $_SESSION['username'] = $username;
            echo json_encode($response);
            exit();
        } else {
            // Password is incorrect
            $response = array("status" => "error", "message" => "Incorrect password");
            echo json_encode($response);
        }
    } else {
        // User not found
        $response = array("status" => "error", "message" => "User not found");
        echo json_encode($response);
    }
}

// Close database connection
$conn->close();
?>
