<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    // Redirect to the login page if not logged in
    header("Location: login.php");
    exit();
}

// Retrieve user ID from the session
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

// Define the base URL of your website
$baseUrl = "https://unichat.com";

function get_post_link($postID) {
    global $baseUrl;
    // Create the post link using the base URL and the post's ID
    return "{$baseUrl}/post/{$postID}";
}

// Define the hasLikedPost function
function hasLikedPost($postID, $conn, $userID) {
    $count = 0; // Initialize the $count variable

    $stmt = $conn->prepare("SELECT COUNT(*) FROM picture_likes WHERE picture_id = ? AND user_id = ?");
    $stmt->bind_param("ii", $postID, $userID);
    $stmt->execute();
    $stmt->bind_result($count);
    $stmt->fetch();
    $stmt->close();

    return $count > 0;
}
function hassavedPost($conn, $postID, $postType, $userID) {
    $count = 0; // Initialize the $count variable

    if ($postType === 'image') {
        $stmt = $conn->prepare("SELECT COUNT(*) FROM saved_picture WHERE picture_id = ? AND user_id = ?");
    } elseif ($postType === 'text') {
        $stmt = $conn->prepare("SELECT COUNT(*) FROM saved_text_posts WHERE text_post_id = ? AND user_id = ?");
    } else {
        // Handle other post types or show an error
        return false;
    }

    if ($stmt) {
        $stmt->bind_param("ii", $postID, $userID);
        $stmt->execute();
        $stmt->bind_result($count);
        $stmt->fetch();
        $stmt->close();

        return $count > 0;
    } else {
        // Handle the case where prepare failed
        return false;
    }
}

// Retrieve the liked users' data
        $likedUsersStmt = $conn->prepare("SELECT persons.full_name, profile_pictures.picture 
                                          FROM picture_likes 
                                          JOIN persons ON picture_likes.user_id = persons.id 
                                          LEFT JOIN profile_pictures ON profile_pictures.person_id = persons.id 
                                          WHERE picture_likes.picture_id = ?");
        $likedUsersStmt->bind_param("i", $postID);
        $likedUsersStmt->execute();
        $likedUsersResult = $likedUsersStmt->get_result();
    
        function getLikesCount($postID, $conn) {
            $count = 0; // Initialize the $count variable
            
            $stmt = $conn->prepare("SELECT COUNT(*) FROM picture_likes WHERE picture_id = ?");
            $stmt->bind_param("i", $postID);
            $stmt->execute();
            
            // Bind the result to the initialized variable
            $stmt->bind_result($count);
            $stmt->fetch();
            
            // Close the statement
            $stmt->close();
        
            return $count;
        }
           
// Function to get the likes data for a post
function getLikes($postID, $conn) {
    $stmt = $conn->prepare("SELECT persons.full_name, profile_pictures.picture AS profile_picture, picture_likes.user_id
                            FROM picture_likes
                            JOIN persons ON picture_likes.user_id = persons.id
                            LEFT JOIN profile_pictures ON profile_pictures.person_id = persons.id
                            WHERE picture_id = ?");
    $stmt->bind_param("i", $postID);
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();

    return $result;
}

// Prepare the SQL statement to retrieve the profile picture
$stmt = $conn->prepare("SELECT picture FROM profile_pictures WHERE person_id = ?");
$stmt->bind_param("i", $userID);
$stmt->execute();
$stmt->bind_result($pictureData);
$stmt->fetch();
$stmt->close();

// Check if a profile picture is found
if ($pictureData) {
    // Use base64_decode to convert the image data back to binary
    $fileUrl = 'data:image/jpeg;base64,' . base64_encode($pictureData);
} else {
    // Output a default profile picture if no picture is found
    $fileUrl = "/images/default-profile.jpg";
}

// Prepare the SQL statement to retrieve the user's full name and username
$stmt = $conn->prepare("SELECT full_name, username FROM persons WHERE id = ?");
$stmt->bind_param("i", $userID);
$stmt->execute();
$stmt->bind_result($fullName, $username);
$stmt->fetch();
$stmt->close();

// Check if the form is submitted and the "create_post" button is clicked
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['create_post'])) {
    // Retrieve the text input from the form
    $text = $_POST['post_text'];
    // Retrieve the user ID from the session
    $userID = $_SESSION['user_id']; // Note: $userID variable is being used here

    // Prepare and execute the SQL statement to insert the text post into the database
    $stmt = $conn->prepare("INSERT INTO text_posts (user_id, text) VALUES (?, ?)");
    $stmt->bind_param("is", $userID, $text);

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
}

$stmt = $conn->prepare("SELECT persons.full_name, persons.username, text_posts.created_at, text_posts.text AS caption, NULL AS post_picture, 'text' AS post_type, profile_pictures.picture AS profile_picture, text_posts.id AS post_id
FROM text_posts
JOIN persons ON text_posts.user_id = persons.id
LEFT JOIN profile_pictures ON profile_pictures.person_id = persons.id
WHERE text_posts.user_id = ?
UNION
SELECT persons.full_name, persons.username, pictures.created_at, pictures.caption, pictures.picture AS post_picture, pictures.post_type, profile_pictures.picture AS profile_picture, pictures.id AS post_id
FROM friends 
JOIN persons ON friends.friend_id = persons.id
JOIN pictures ON pictures.user_id = persons.id
LEFT JOIN profile_pictures ON profile_pictures.person_id = persons.id
WHERE friends.user_id = ?
UNION
SELECT persons.full_name, persons.username, text_posts.created_at, text_posts.text AS caption, NULL AS post_picture, 'text' AS post_type, profile_pictures.picture AS profile_picture, text_posts.id AS post_id
FROM text_posts
JOIN persons ON text_posts.user_id = persons.id
LEFT JOIN profile_pictures ON profile_pictures.person_id = persons.id
WHERE text_posts.user_id = ?
ORDER BY created_at DESC");
$stmt->bind_param("iii", $userID, $userID, $userID);
$stmt->execute();
$result = $stmt->get_result();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nimisocial</title>
</head>
<link rel="stylesheet" href="path/to/font-awesome/css/font-awesome.min.css">
<link rel="stylesheet" href="https://unicons.iconscout.com/release/v4.0.8/css/line.css">
<link rel="stylesheet" href="path/to/font-awesome/css/font-awesome.min.css">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<link rel="stylesheet" href="home.css">
<>
    <nav>
        <div class="container">
            <h2 class="log">
                UniChat
            </h2>
            <div class="search-bar">
                <i class="uil uil-search"></i>
                <input type="search" placeholder="search for friends and post">
            </div>
            <div class="create">
                <a class="btn btn-primary" href="create_post.html">Create</a>
                <div class="profile-photo">
                    <img src="<?php echo $fileUrl; ?>">
                </div>
        </div>
        </div>
    </nav>

    <!--------------------------------Main Section---------------------------->
    <main>
        <div class="container">
            <div class="left">
                <a class="profile" href="edit_profile.php"> 
                    <div class="profile-photo">
                        <img src="<?php echo $fileUrl; ?>">
                    </div>
                    <div class="handle">
                        <h4><?php echo $fullName; ?></h4>
                        <p class="text-muted">
                            @<?php echo $username; ?>
                        </p>
                    </div>
                </a>
                <!------------------------------side bar---------------------->
                <div class="sidebar">
                    <a class="menu-item active">
                        <span><i class="uil uil-home"></i></span><h3>Home</h3>
                    </a>
                    <a class="menu-item">
                        <span><i class="uil uil-compass"></i></span><h3>Explore</h3>
                    </a>
                    <a class="menu-item" id="addfriend">
                    <span><i class="uil uil-user-plus"></i></span><h3>Add Friend</h3>
                    <div class="addfriend">
                        <h3>Add Friend</h3>
                        <div class="addfriends">
                            <input type="search" placeholder="Search friends" >
                        </div>
                        <input type="submit" value="Search" class="btn btn-primary">
                    </div>
                    </a>
                    <a class="menu-item" id="notifications">
                        <span><i class="uil uil-bell"><small class="notification-count">9+</small></i></span><h3>Notifications</h3>
                        <!------------------------------notifications popup---------------------------------->
                        <div class="notifications-popup">
                            <div>
                                <div class="profile-photo">
                                    <img src="/images/profile-2.jpg">
                                </div>
                                <div class="notification-body">
                                    <b>David Jofi</b> accepted your friend request
                                    <small class="text-muted">2 Days ago</small>
                                </div>
                            </div>
                            <div>
                                <div class="profile-photo">
                                    <img src="/images/profile-3.jpg">
                                </div>
                                <div class="notification-body">
                                    <b>Bella Don</b> commented on your post
                                    <small class="text-muted">1 Hour ago</small>
                                </div>
                            </div>
                            <div>
                                <div class="profile-photo">
                                    <img src="/images/profile-4.jpg">
                                </div>
                                <div class="notification-body">
                                    <b>Silver Chris</b> and <b>283 others</b> liked your post
                                    <small class="text-muted">2 Days ago</small>
                                </div>
                            </div>
                            <div>
                                <div class="profile-photo">
                                    <img src="/images/profile-2.jpg">
                                </div>
                                <div class="notification-body">
                                    <b>David Jofi</b> accepted your friend request
                                    <small class="text-muted">2 Days ago</small>
                                </div>
                            </div>
                            <div>
                                <div class="profile-photo">
                                    <img src="/images/profile-2.jpg">
                                </div>
                                <div class="notification-body">
                                    <b>David Jofi</b> accepted your friend request
                                    <small class="text-muted">2 Days ago</small>
                                </div>
                            </div>
                            <div>
                                <div class="profile-photo">
                                    <img src="/images/profile-5.jpg">
                                </div>
                                <div class="notification-body">
                                    <b>Jane Doe</b> commented on your post
                                    <small class="text-muted">1 hour ago</small>
                                </div>
                            </div>
                        </div>
                    </a>
                    <a class="menu-item" id="messages-notifications">
                        <span><i class="uil uil-envelope-alt"><small class="notification-count">6</small></i></span><h3>messages</h3>
                    </a>
                    <a class="menu-item">
                        <span><i class="uil uil-bookmark"></i></span><h3>Bookmarks</h3>
                    </a>
                    <a class="menu-item">
                        <span><i class="uil uil-chart-line"></i></span><h3>Analytics</h3>
                    </a>
                    <a class="menu-item" id="theme">
                        <span><i class="uil uil-palette"></i></span><h3>Theme</h3>
                    </a>
                    <a class="menu-item">
                        <span><i class="uil uil-setting"></i></span><h3>Settings</h3>
                    </a>
                </div>
                <!-----------------------end of the side bar----------------->
                <label for="create-post" class="btn btn-primary">Create Post</label>
            </div>
            <!-------------------------------Middle------------------------------>
            <div class="middle">
                <form class="create-post" action="home.php" method="post">
                    <div class="profile-photo">
                        <img src="<?php echo $fileUrl; ?>">
                    </div>
                    <input type="text" placeholder="What is on your mind?" name="post_text" id="create-post">
                    <!-- Remove the nested form element below -->
                    <!-- <form class="create-post" action="home.php" method="post"></form> -->
                    <input type="submit" value="Post" class="btn btn-primary" name="create_post">
                </form>
                <div class="feeds">
                    
                    <!----loop through the posts from friends--->
                    <?php while ($row = $result->fetch_assoc()) : ?>
                    <div class="feed-container">
                    <!---------------------------------feed---------------->
                    <div class="feed" data-post-id="<?php echo $row['post_id']; ?>" data-post-type="<?php echo $row['post_type']; ?>">
                            
                            <div class="head">
                                <div class="user">
                                    <div class="profile-photo">
                                        <img src="<?php echo 'data:image/jpeg;base64,' . base64_encode($row['profile_picture']); ?>">
                                    </div>
                                    <div class="ingo">
                                        <h3><?php echo $row['full_name']; ?></h3>
                                        <small><?php echo get_time_difference($row['created_at']); ?></small>
                                    </div>
                                </div>
                                <span class="edit">
                                    <i class="uil uil-ellipsis-h"></i>
                                </span>
                            </div>
                            <!----check if the post is an image -->
                            <?php if ($row['post_type'] === 'image') : ?>
                                <div class="photo">
                                    <img src="<?php echo 'data:image/jpeg;base64,' . base64_encode($row['post_picture']); ?>" alt="Post Picture">
                                </div>
                            <?php elseif ($row['post_type'] === 'video') : ?>
                                <div class="post-video">
                                    <video controls>
                                        <source src="<?php echo 'data:video/mp4;base64,' . base64_encode($row['post_picture']); ?>" type="video/mp4">
                                        Your browser does not support the video tag.
                                    </video>
                                </div>
                            <?php endif; ?>

                            <?php if ($row['post_type'] === 'text') : ?>
                                <div class="post-text">
                                    <?php echo $row['caption']; ?>
                                </div>
                            <?php endif; ?>
                            <div class="action-button">
                                <div class="interaction-buttons">
                                    <?php
                                    $postID = $row['post_id'];
                                    $postType = $row['post_type'];
                                    $isLiked = hasLikedPost($postID, $conn, $userID);

                                    echo '<button class="like-buttons';
                                    if ($isLiked) {
                                        echo ' liked';
                                    }
                                    echo '" data-post-id="' . $postID . '" data-post-type="' . $postType . '">';
                                    ?>
                                    <i class="uil uil-heart-sign"></i>
                                    </button>
                                    <!-- Comments icon -->
                                    <button class="comment-buttons" data-post-id="<?php echo $row['post_id']; ?>" data-post-type="<?php echo $row['post_type']; ?>">
                                        <i class="uil uil-comment-alt-chart-lines"></i>
                                    </button>
                                    <div class="comments-section" style="display: none;">
                                    <div class="comment-box">
                                        <input type="text" placeholder="Add a comment..." class="comment-input">
                                        <button class="btn btn-primary post-comment" data-post-id="<?php echo $row['post_id']; ?>" data-post-type="<?php echo $row['post_type']; ?>">Post</button>
                                    </div>
                                        <!-- Additional logic can go here to display existing comments -->
                                    </div>
                                    <button class="share-buttons" data-post-id="<?php echo $row['post_id']; ?>" data-post-link="<?php echo get_post_link($row['post_id']); ?>">
                                        <i class="uil uil-share-alt"></i>
                                    </button>
                                </div>
                                <div class="bookmark">
                                        <?php
                                        $postID = $row['post_id'];
                                        $postType = $row['post_type'];
                                        $issaved = hassavedPost($conn, $postID, $postType, $userID); // Pass the database connection as the first argument

                                        echo '<button class="save-button';
                                        if ($issaved) {
                                            echo ' saved';
                                        }
                                        echo '" data-post-id="' . $postID . '" data-post-type="' . $postType . '">';
                                        ?>
                                        <i class="uil uil-bookmark-full"></i>
                                    </button>
                                </div>
                            </div>
                                <?php
                                // Retrieve the number of likes for the post
                                $likesCount = getLikesCount($row['post_id'], $conn);

                                // Retrieve the likes data for the post
                                $likesResult = getLikes($row['post_id'], $conn);
                                ?>
                            <!-- Display the liked-by section -->
                            <div class="liked-by">
                            <?php
                            if ($likesCount > 0) {
                                $remainingLikes = $likesCount - 1;
                                $currentUserLiked = false;
                                // Rewind the result set cursor to the beginning
                                $likesResult->data_seek(0);

                                // Fetch the first like row separately to get the name
                                $firstLikeRow = $likesResult->fetch_assoc();

                                // Display the profile picture of the first user
                                echo '<span><img src="data:image/jpeg;base64,' . base64_encode($firstLikeRow['profile_picture']) . '"></span>';

                                // Display the profile pictures of liked users
                                while ($likeRow = $likesResult->fetch_assoc()) {
                                    if ($likeRow['user_id'] === $userID) {
                                        $currentUserLiked = true;
                                    } else {
                                        echo '<span><img src="data:image/jpeg;base64,' . base64_encode($likeRow['profile_picture']) . '"></span>';
                                    }
                                }
                                while ($likeRow = $likesResult->fetch_assoc()) {
                                    // Check if the current user has liked the post
                                    if ($likeRow['user_id'] === $userID) {
                                        $currentUserLiked = true;
                                    }
                                }

                                if ($currentUserLiked) {
                                    // Display the current user's name as "You" if they have liked the post
                                    echo '<p>liked by <b>You</b>';
                                    if ($remainingLikes > 0) {
                                        echo ' and <b>' . $remainingLikes . ' others</b></p>';
                                    } else {
                                        echo '</p>';
                                    }
                                } else {
                                    // Display the name of the first user and the remaining likes
                                    echo '<p>liked by <b>' . $firstLikeRow['full_name'] . '</b>';
                                    if ($remainingLikes > 0) {
                                        echo '</b> and <a href="" class="show-liked-users" data-post-id="' . $row['post_id'] . '">' . $remainingLikes . ' others</a></p>';
                                    } else {
                                        echo '</p>';
                                    }
                                }
                            }
                            ?>
                            </div>
                            <div class="caption">
                                <p><b><?php echo $row['full_name']; ?>  </b><?php echo $row['caption']; ?></p>
                            </div>
                            <div class="comment class-muted">view all 108 comments</div>
                        </div>
                        <div class="likes-box-container" id="likes-box-container-<?php echo $row['post_id']; ?>">
                            <!-- Likes Box -->
                            <div class="likes-box" id="likes-box-<?php echo $row['post_id']; ?>">
                                <!-- Likes content goes here -->
                                <!-- ... -->
                            </div>
                        </div>
                    </div>
                    <?php endwhile; ?>
                </div>
                <!----------------------end of feed-------------------->
            </div>
            <!----------------------------end ------------------------->
            <div class="right">
                <!---------------------messages--------------->
                        <div class="messages">
                            <div class="heading">
                                <h4>Messages</h4><i class="uil uil-edit"></i>
                            </div>
                            <!-------------search bar---------------->
                            <div class="search-bar">
                                <i class="uil uil-search"></i>
                                <input type="search" placeholder="Search messages" id ="message-search">
                            </div>
                            <div class="category">
                                <h6 class="active">Primary</h6>
                                <h6>General</h6>
                                <h6 class="message-requests">Requests(7)</h6>
                            </div>
                            <!-------------------------------messages-------------------->
                            <div class="message">
                                <div class="profile-photo active">
                                    <img src="/images/profile-17.jpg">
                                </div>
                                <div class="message-body">
                                    <h5>Edem Quist</h5>
                                    <p class="text-muted">Just woke up bruhh</p>
                                </div>
                            </div>
                            <div class="message">
                                <div class="profile-photo active">
                                    <img src="/images/profile-11.jpg">
                                </div>
                                <div class="message-body">
                                    <h5>Francan Delta</h5>
                                    <p class="text-muted">Recieved bruhh, thanks</p>
                                </div>
                            </div>
                            <div   div class="message">
                                <div class="profile-photo active">
                                    <img src="/images/profile-9.jpg">
                                </div>
                                <div class="message-body">
                                    <h5>Jane Doe</h5>
                                    <p class="text-bold">Ok</p>
                                </div>
                            </div>
                            <!----------end of messages--------------->
                        </div>
                    <!--------------------friend request------------------->
                        <div class="friend-requests">
                            <h4>Requests</h4>
                            <div class="request">
                                <div class="info">
                                    <div class="profile-photo">
                                        <img src="/images/profile-13.jpg">
                                    </div>
                                    <div>
                                        <h5>Hajia Bintu</h5>
                                        <p class="text-muted">
                                            8 mutual friends
                                        </p>
                                    </div>
                                </div>
                                <div class="action">
                                    <button class="btn btn-primary">
                                        Accept
                                    </button>
                                    <button class="btn">
                                        Decline
                                    </button>
                                </div>
                            </div>
                            <div class="request">
                                <div class="info">
                                    <div class="profile-photo">
                                        <img src="/images/profile-14.jpg">
                                    </div>
                                    <div>
                                        <h5>Vintu</h5>
                                        <p class="text-muted">
                                            2 mutual friends
                                        </p>
                                    </div>
                                </div>
                                <div class="action">
                                    <button class="btn btn-primary">
                                        Accept
                                    </button>
                                    <button class="btn">
                                        Decline
                                    </button>
                                </div>
                            </div>
                        </div>
                
            </div>
        </div>
    </main>
    <?php
// Function to get the time difference in minutes/hours/days
function get_time_difference($timestamp) {
    $time = time() - strtotime($timestamp);
    $seconds = $time % 60;
    $minutes = floor($time / 60);
    $hours = floor($minutes / 60);
    $days = floor($hours / 24);

    if ($days > 0) {
        return $days . " days ago";
    } elseif ($hours > 0) {
        return $hours . " hours ago";
    } elseif ($minutes > 0) {
        return $minutes . " minutes ago";
    } else {
        return $seconds . " seconds ago";
    }
}
?>
    <!--------------------theme--------------->
    <Div class="customize-theme">
        <div class="card">
            <h2>Customize your view</h2>
            <p>Manage your font size, color, and background.</p>

            <!--------------font size------------>
            <div class="font-size">
                <h4>Font Size</h4>
                <div>
                    <h6>Aa</h6>
                    <div class="choose-size">
                        <span class="font-size-1"></span>
                        <span class="font-size-2 active"></span>
                        <span class="font-size-3"></span>
                        <span class="font-size-4"></span>
                        <span class="font-size-5"></span>
                    </div>
                    <h3>Aa</h3>
                </div>
            </div>

            <!-----------------primary colors------------->
            <div class="color">
                <h4>Color</h4>
                <div class="choose-color">
                    <span class="color-1 active"></span>
                    <span class="color-2"></span>
                    <span class="color-3"></span>
                    <span class="color-4"></span>
                    <span class="color-5"></span>
                </div>
            </div>

            <!----------------background colors------>
            <div class="background">
                <h4>Background</h4>
                <div class="choose-bg">
                    <div class="bg-1 active">
                        <span></span>
                        <h5 for="bg-1">Light></h5>
                    </div>
                    <div class="bg-2">
                        <span></span>
                        <h5>Dim</h5>
                    </div>
                    <div class="bg-3">
                        <span></span>
                        <h5 for="bg-3">Light Out</h5>
                    </div>
                </div>
            </div>
        </div>
    </Div>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="home.js"></script>
    
</body>
</html>