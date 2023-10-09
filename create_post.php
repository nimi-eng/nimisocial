<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    // Redirect to the login page if not logged in
    header("Location: login.php");
    exit();
}

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve the text input from the form
    $text = $_POST['post_text'];
    // Retrieve the caption input from the form
    $caption = $_POST['post_caption'];
    // Retrieve the user ID from the session
    $userID = $_SESSION['user_id'];

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

// Check if an image was uploaded
if (isset($_FILES['post_image']) && $_FILES['post_image']['error'] === UPLOAD_ERR_OK) {
    // Retrieve the image data
    $imageData = file_get_contents($_FILES['post_image']['tmp_name']);

    // Use the same caption for both images and videos
    $caption = $_POST['post_caption'];

    // Prepare and execute the SQL statement to insert the post with image into the database
    $stmt = $conn->prepare("INSERT INTO pictures (user_id, caption, picture, post_type) VALUES (?, ?, ?, 'image')");
    $stmt->bind_param("iss", $userID, $caption, $imageData);
} elseif (isset($_FILES['post_video']) && $_FILES['post_video']['error'] === UPLOAD_ERR_OK) {
    // Check if a video was uploaded
    // Retrieve the video data
    $videoData = file_get_contents($_FILES['post_video']['tmp_name']);

    // Use the caption specifically for video posts
    $caption = $_POST['post_video_caption'];

    // Prepare and execute the SQL statement to insert the post with video into the database
    $stmt = $conn->prepare("INSERT INTO pictures (user_id, caption, picture, post_type) VALUES (?, ?, ?, 'video')");
    $stmt->bind_param("iss", $userID, $caption, $videoData);
} else {
    // Prepare and execute the SQL statement to insert the post without image or video into the database
    $stmt = $conn->prepare("INSERT INTO text_posts (user_id, text) VALUES (?, ?)");
    $stmt->bind_param("is", $userID, $text);
}


    if ($stmt->execute()) {
        // Post successfully inserted
        // You can redirect the user to the home page or any other page after successful post
        header("Location: home.php");
        exit();
    } else {
        // Failed to insert the post
        // Handle the error as per your application's requirement
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
}
?>
