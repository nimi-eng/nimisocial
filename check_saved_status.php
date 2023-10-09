<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    echo "not_logged_in";
    exit();
}

if (isset($_GET['post_id'])) {
    $postID = $_GET['post_id'];
    $userID = $_SESSION['user_id'];

    // Database connection
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "nimisocials";

    $conn = new mysqli($servername, $username, $password, $dbname);

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Check if the user has liked the post in either table
    $sql = "SELECT * FROM saved_picture WHERE user_id = ? AND picture_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $userID, $postID);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        echo "saved";
    } else {
        // Check in the text_post_save table
        $sql = "SELECT * FROM saved_text_posts WHERE user_id = ? AND text_post_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $userID, $postID);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            echo "saved";
        } else {
            echo "not_saved";
        }
    }

    $stmt->close();
    $conn->close();
} else {
    echo "Invalid request";
}
?>
