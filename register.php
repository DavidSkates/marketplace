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

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    session_start();
    // Sanitize inputs
    $username = mysqli_real_escape_string($conn, $_POST["username"]);
    $password = mysqli_real_escape_string($conn, $_POST["password"]);
    $firstName = mysqli_real_escape_string($conn, $_POST["firstName"]);
    $lastName = mysqli_real_escape_string($conn, $_POST["lastName"]);
    $email = mysqli_real_escape_string($conn, $_POST["email"]);

    // Check if the username already exists
    $check_sql = "SELECT * FROM users WHERE username='$username'";
    $result = $conn->query($check_sql);

    // Check if the email already exists
    $check_email_query = "SELECT * FROM users WHERE email='$email'";
    $check_email_result = $conn->query($check_email_query);


    if ($result->num_rows > 0) {
        // Username already exists, return error response
        $response = array("status" => "error", "message" => "Username already exists");
        echo json_encode($response);
        exit(); // Stop further execution
    }
    if ($check_email_result->num_rows > 0) {
        // Email already exists, return error response
        $response = array("status" => "error", "message" => "Email already exists");
        echo json_encode($response);
        exit(); // Stop further execution
    }

    // Hash the password
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // Insert user data into the database
    $insert_sql = "INSERT INTO users (username, password, firstName, lastName, email) VALUES ('$username', '$hashedPassword', '$firstName', '$lastName', '$email')";
    if ($conn->query($insert_sql) === TRUE) {
        // Return success response
        $_SESSION['loggedin'] = true;
        $_SESSION['username'] = $username;
        $response = array("status" => "success", "message" => "User registered successfully", "redirect" => "index.html");
        echo json_encode($response);
    } else {
        // Return error response
        $response = array("status" => "error", "message" => "Error registering user: " . $conn->error);
        echo json_encode($response);
    }
}

// Close database connection
$conn->close();
?>
