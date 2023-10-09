<?php
session_start();

// Database credentials
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "nimisocials";

// Create a database connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if the login form is submitted
if (isset($_POST["submit"])) {
    // Get the entered username and password
    $username = $_POST["username"];
    $password = $_POST["password"];

    // Prepare the SQL statement
    $sql = "SELECT * FROM persons WHERE username = '$username' AND password = '$password'";
    $result = $conn->query($sql);

    if ($result->num_rows == 1) {
        // Login successful, set session data
        $user = $result->fetch_assoc();
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['token'] = generateToken(); // Generate a unique token for the session
        
        // Redirect to the profile page
        header("Location: home.php");
        exit();
    } else {
        // Login failed
        echo "Invalid username or password.";
    }
}

// Close the database connection
$conn->close();

// Function to generate a unique token
function generateToken() {
    return bin2hex(random_bytes(16)); // Generate a 32-character hexadecimal token
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nimisocial Login Page</title>
    <link rel="stylesheet" href="login.css">
</head>
<body>

    <div class="wrapper">
        <form id="login-form" method="POST">
        <div class="container">
            <h2 class="log">
                UniChat
            </h2>
        </div>    
            <label for="username">Username:</label>
            <input type="text" id="username" name="username" required>

            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required>

            <button type="submit" name="submit">Log In</button>
        </form>
    </div>
    <div id="error-message"></div>
    <script src="login.js"></script>
</body>
</html>
