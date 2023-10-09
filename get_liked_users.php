<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    // Return an error JSON response
    echo json_encode(['error' => 'User not logged in']);
    exit();
}

if (isset($_GET['post_id']) && isset($_GET['post_type'])) {
    $postID = $_GET['post_id'];
    $postType = $_GET['post_type'];

    // Database connection
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "nimisocials";

    $conn = new mysqli($servername, $username, $password, $dbname);

    if ($conn->connect_error) {
        // Return an error JSON response
        echo json_encode(['error' => 'Connection failed']);
        exit();
    }

    $likedUsers = array();

    if ($postType === 'image') {
        $query = "SELECT persons.username, persons.full_name, profile_pictures.picture
                  FROM picture_likes
                  INNER JOIN persons ON picture_likes.user_id = persons.id
                  LEFT JOIN profile_pictures ON profile_pictures.person_id = persons.id
                  WHERE picture_likes.picture_id = ?";
    } elseif ($postType === 'text') {
        $query = "SELECT persons.username, persons.full_name, profile_pictures.picture
                  FROM text_post_likes
                  INNER JOIN persons ON text_post_likes.user_id = persons.id
                  LEFT JOIN profile_pictures ON profile_pictures.person_id = persons.id
                  WHERE text_post_likes.text_post_id = ?";
    }

    // Error handling for preparing the SQL statement
    $stmt = mysqli_prepare($conn, $query);
    if (!$stmt) {
        // Return an error JSON response
        echo json_encode(['error' => 'Error preparing SQL statement: ' . mysqli_error($conn)]);
        exit();
    }

    mysqli_stmt_bind_param($stmt, "i", $postID);
    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);

    while ($row = mysqli_fetch_assoc($result)) {
        $likedUsers[] = array(
            'username' => $row['username'],
            'full_name' => $row['full_name'],
            'profile_image' => $row['picture']
        );
    }

    // Send the liked users' data as JSON response
    echo json_encode($likedUsers);

    // Close the prepared statement
    mysqli_stmt_close($stmt);
    $conn->close();
} else {
    // Return an error JSON response
    echo json_encode(['error' => 'Invalid request']);
}
?>
