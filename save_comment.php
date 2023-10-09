<?php
// Enable error reporting and logging
ini_set('display_errors', 1);
ini_set('log_errors', 1);
error_reporting(E_ALL);
ini_set('error_log', 'error.log'); // Log errors to a file named 'error.log' in the same directory


session_start();

if (!isset($_SESSION['user_id'])) {
    echo json_encode(array('status' => 'error', 'message' => 'User not logged in'));
    exit();
}

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "nimisocials";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_SESSION['user_id'])) {
    // Get the post ID and post type from the POST data
    $postID = $_POST['post_id'];
    $postType = $_POST['post_type'];
    $commentText = $_POST['comment'];
    $userID = $_SESSION['user_id'];
    $createdAt = date('Y-m-d H:i:s'); // Current timestamp

    if ($postType === 'text') {
        $tableName = 'text_post_comments';
        $postIDColumn = 'text_post_id';
    } elseif ($postType === 'image' || $postType === 'video') {
        $tableName = 'picture_comments';
        $postIDColumn = 'picture_id';
    } else {
        echo json_encode(array('status' => 'error', 'message' => 'Invalid post type'));
        exit();
    }

    // Validate and sanitize commentText
    $commentText = mysqli_real_escape_string($conn, $commentText);

    // Prepare and execute the SQL query to insert the comment
    $query = "INSERT INTO $tableName (user_id, $postIDColumn, comment, created_at) VALUES (?, ?, ?, ?)";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "iiss", $userID, $postID, $commentText, $createdAt);
    $result = mysqli_stmt_execute($stmt);

    if ($result) {
        echo json_encode(array('status' => 'success', 'message' => 'Comment saved successfully'));
    } else {
        echo json_encode(array('status' => 'success', 'message' => 'Comment saved successfully'));
    }
    

    // Close the prepared statement
    mysqli_stmt_close($stmt);
}

// Close the database connection
mysqli_close($conn);
?>
