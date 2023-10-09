<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    echo "User not logged in";
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

    // Check if the like record already exists
    $checkSql = "SELECT * FROM saved_picture WHERE user_id = ? AND picture_id = ?";
    $checkStmt = $conn->prepare($checkSql);
    $checkStmt->bind_param("ii", $userID, $postID);
    $checkStmt->execute();
    $result = $checkStmt->get_result();

    if ($result->num_rows > 0) {
        echo "save record already exists";
    } else {
        $insertSql = "INSERT INTO saved_picture (user_id, picture_id, created_at) VALUES (?, ?, NOW())";
        $insertStmt = $conn->prepare($insertSql);
        $insertStmt->bind_param("ii", $userID, $postID);

        if ($insertStmt->execute()) {
            echo "Picture save saved successfully";
        } else {
            echo "Error: " . $insertStmt->error;
        }

        $insertStmt->close();
    }

    $checkStmt->close();
    $conn->close();
} else {
    echo "Invalid request";
}
?>
