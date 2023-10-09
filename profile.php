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

// Prepare the SQL statement to retrieve the profile picture, username, full name, level, and department
$stmt = $conn->prepare("
SELECT profile_pictures.picture, persons.username, persons.full_name, persons.bio, persons.levels, department.department_name
FROM profile_pictures 
JOIN persons ON profile_pictures.person_id = persons.id 
LEFT JOIN department ON department.person_id = persons.id
WHERE persons.id = ?");
$stmt->bind_param("i", $userID);
$stmt->execute();
$stmt->bind_result($pictureData, $username, $fullName, $bio, $level, $department);
$stmt->fetch();
$stmt->close();

// Prepare the SQL statement to retrieve the user's friends list
$stmtFriends = $conn->prepare("
SELECT persons.username, persons.full_name
FROM persons
JOIN friends ON persons.id = friends.friend_id
WHERE friends.user_id = ? AND friends.status = 'accepted'
");
$stmtFriends->bind_param("i", $userID);
$stmtFriends->execute();
$resultFriends = $stmtFriends->get_result();
$friends = $resultFriends->fetch_all(MYSQLI_ASSOC);
$stmtFriends->close();


// Prepare the sql statement to retrieve the user's course mate
$stmtCourseMates = $conn->prepare("
SELECT persons.username, persons.full_name
FROM persons
JOIN course_mate ON persons.id = course_mate.course_mate
WHERE course_mate.user_id =?");
$stmtCourseMates->bind_param("i", $userID);
$stmtCourseMates->execute();
$resultCourseMates = $stmtCourseMates->get_result();
$courseMates = $resultCourseMates->fetch_all(MYSQLI_ASSOC);
$stmtCourseMates->close();

// Check if a profile picture is found
if ($pictureData) {
    $fileUrl = 'data:image/jpeg;base64,' . base64_encode($pictureData);
} else {
    // Output a default profile picture if no picture is found
    $fileUrl = "/images/default-profile.jpg";
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>User Profile</title>
    <link rel="stylesheet" type="text/css" href="profile.css">
</head>
<body>
    
    <div class="container">
        <h2 class="page-label">User Profile Page</h2>
        <div class="profile-section">
            <div class="profile-header">
                <div class="profile-picture">
                    <img src="<?php echo $fileUrl; ?>" alt="Profile Picture">
                </div>
                <input id="fileInput" type="file" accept="image/*" style="display: none;">
            </div>
            <div class="profile-info">
                <div class="username-section">
                    <label for="username">Username:</label>
                    <input type="text" id="username" value="<?php echo $username; ?>" disabled>
                </div>
                <div class="name-section">
                    <label for="name">Full Name:</label>
                    <input type="text" id="name" value="<?php echo $fullName; ?>" disabled>
                </div>
                <div class="bio-section">
                    <label for="bio">Bio:</label>
                    <textarea id="bio" disabled><?php echo $bio; ?></textarea>
                </div>
                <div class="level">
                    <label for="level"> Level: </label>
                    <input type="text" id="level" value="<?php echo $level; ?>" disabled>
                </div>
                <div class="department">
                    <label for="department">Department:</label>
                    <input type="text" id="department" value="<?php echo $department; ?>" disabled>
                </div>
                <a href="edit_profile.php" class="edit-profile-button">Edit Profile</a>
            </div>
        </div>
        <div class="action-buttons">
            <button class="action-button" id="showfriends">Show Friends List</button>
            <button class="action-button" id="showcoursemates">Show Coursemates</button>
            <button class="action-button">Show Photos</button>
        </div>
        <div class="home-button">
            <a href="home.php">Home</a>
        </div>
    </div>
    <div id="friendsListContainer"></div>

    <script>
document.getElementById("showfriends").addEventListener("click", function() {
    var friendsList = <?php echo json_encode($friends); ?>;
    var friendsHTML = "<h3>Friends List:</h3>";
    if (friendsList.length > 0) {
        friendsHTML += "<ul>";
        friendsList.forEach(function(friend) {
            friendsHTML += "<li>" + friend.full_name + " (@" + friend.username + ")</li>";
        });
        friendsHTML += "</ul>";
    } else {
        friendsHTML += "<p>No friends found.</p>";
    }

    // Open a new window with the generated friends list HTML
    var newWindow = window.open("", "_blank", "width=400,height=400");
    newWindow.document.write(friendsHTML);
});

document.getElementById("showcoursemates").addEventListener("click", function(){
    var coursemateList = <?php echo json_encode($courseMates); ?>;
    var coursemateHTML = "<h3> Coursemate List:</h3>";
    if (coursemateList.length > 0){
        coursemateHTML += "<ul>";
        coursemateList.forEach(function(coursemate){ // <-- Changed the variable name here
            coursemateHTML += "<li>" + coursemate.full_name + "(@" + coursemate.username + ")</li>"; // <-- Changed the variable name here
        });
        coursemateHTML += "</ul>";
    }else{
        coursemateHTML += "<p>No course mates found. </p>";
    }
    //open a new window with the generated coursemates list HTML
    var newWindow = window.open("", "_blank", "width=400, height=400");
    newWindow.document.write(coursemateHTML);
});

</script>

</body>
</html>
