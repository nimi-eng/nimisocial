<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    echo "not_logged_in";
    exit();
}

if (isset($_POST['post_id']) && isset($_POST['post_type'])) {
    $postID = $_POST['post_id'];
    $postType = $_POST['post_type'];
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

    // Check if the user has already saved this post
    $isSaved = hassavedPost($postID, $conn, $userID);

    if ($isSaved) {
        // User has already saved this post, so delete it to "unsave" it
        $deleteSql = getDeleteQuery($postType);
        $stmt = $conn->prepare($deleteSql);
        $stmt->bind_param("ii", $userID, $postID);

        if ($stmt->execute()) {
            echo "unsaved";
        } else {
            echo "Error: " . $stmt->error;
        }

        $stmt->close();
    } else {
        // User hasn't saved this post, so insert a new record to "save" it
        $insertSql = getInsertQuery($postType);
        $insertStmt = $conn->prepare($insertSql);
        $insertStmt->bind_param("ii", $userID, $postID);

        if ($insertStmt->execute()) {
            echo "saved";
        } else {
            echo "Error: " . $insertStmt->error;
        }

        $insertStmt->close();
    }

    $conn->close();
} else {
    echo "Invalid request";
}

function hassavedPost($postID, $conn, $userID) {
    $count = 0;

    $stmt = $conn->prepare("SELECT COUNT(*) FROM saved_picture WHERE picture_id = ? AND user_id = ?");
    $stmt->bind_param("ii", $postID, $userID);
    $stmt->execute();
    $stmt->bind_result($count);
    $stmt->fetch();
    $stmt->close();

    return $count > 0;
}

function getInsertQuery($postType) {
    if ($postType === 'text') {
        return "INSERT INTO saved_text_posts (user_id, text_post_id, created_at) VALUES (?, ?, NOW())";
    } elseif ($postType === 'image' || $postType === 'video') {
        return "INSERT INTO saved_picture (user_id, picture_id, created_at) VALUES (?, ?, NOW())";
    } else {
        echo "Invalid post type";
        exit();
    }
}

function getDeleteQuery($postType) {
    if ($postType === 'text') {
        return "DELETE FROM saved_text_posts WHERE user_id = ? AND text_post_id = ?";
    } elseif ($postType === 'image' || $postType === 'video') {
        return "DELETE FROM saved_picture WHERE user_id = ? AND picture_id = ?";
    } else {
        echo "Invalid post type";
        exit();
    }
}
?>
