<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    echo "not_logged_in";
    exit();
}

if (isset($_POST['post_id'])) {
    $postID = $_POST['post_id'];
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

    // Delete the like record from the appropriate table based on post type
    if ($_POST['post_type'] === 'text') {
        $sql = "DELETE FROM text_post_likes WHERE user_id = ? AND text_post_id = ?";
    } elseif ($_POST['post_type'] === 'image' || $_POST['post_type'] === 'video') {
        $sql = "DELETE FROM picture_likes WHERE user_id = ? AND picture_id = ?";
    }

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $userID, $postID);

    if ($stmt->execute()) {
        echo "success";
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
} else {
    echo "Invalid request";
}
?>
