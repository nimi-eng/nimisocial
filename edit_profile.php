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

// Check if the form is submitted for updating the profile
if (isset($_POST['save_changes'])) {
    // Retrieve the updated values from the form
    $updatedUsername = $_POST['username'];
    $updatedFullName = $_POST['full_name'];
    $updatedBio = $_POST['bio'];

    // Handle profile picture upload
    if ($_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
        $fileData = file_get_contents($_FILES['profile_picture']['tmp_name']);

        // Check if a profile picture record exists for the user
        $stmt = $conn->prepare("SELECT person_id FROM profile_pictures WHERE person_id = ?");
        $stmt->bind_param("i", $userID);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            // Update the existing profile picture record
            $stmt->close();
            $stmt = $conn->prepare("UPDATE profile_pictures SET picture = ? WHERE person_id = ?");
            $stmt->bind_param("si", $fileData, $userID);
        } else {
            // Insert a new profile picture record
            $stmt->close();
            $stmt = $conn->prepare("INSERT INTO profile_pictures (person_id, picture) VALUES (?, ?)");
            $stmt->bind_param("is", $userID, $fileData);
        }

        $stmt->execute();
        $stmt->close();
    }

    // Prepare and execute the SQL statement to update the profile info
    $stmt = $conn->prepare("UPDATE persons SET username = ?, full_name = ?, bio = ? WHERE id = ?");
    $stmt->bind_param("sssi", $updatedUsername, $updatedFullName, $updatedBio, $userID);
    $stmt->execute();
    $stmt->close();

    // Redirect back to the profile page after updating
    header("Location: profile.php");
    exit();
}

// Check if the user wants to delete the profile picture
if (isset($_POST['delete_picture'])) {
    // Delete the profile picture record
    $stmt = $conn->prepare("DELETE FROM profile_pictures WHERE person_id = ?");
    $stmt->bind_param("i", $userID);
    $stmt->execute();
    $stmt->close();

    // Redirect back to the edit profile page after deleting
    header("Location: edit_profile.php");
    exit();
}

// Prepare the SQL statement to retrieve the profile picture, username, full name, level, and department
$stmt = $conn->prepare("
SELECT profile_pictures.picture, persons.username, persons.full_name, persons.bio, persons.levels, department.department_name
FROM persons
LEFT JOIN profile_pictures ON profile_pictures.person_id = persons.id
LEFT JOIN department ON department.person_id = persons.id
WHERE persons.id = ?");
$stmt->bind_param("i", $userID);
$stmt->execute();
$stmt->bind_result($pictureData, $username, $fullName, $bio, $level, $department);
$stmt->fetch();
$stmt->close();
$conn->close();

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
    <title>Edit Profile</title>
    <link rel="stylesheet" type="text/css" href="profile.css">
</head>
<body>
    
    <div class="container">
        <h2 class="page-label">Edit Profile</h2>
        <div class="profile-section">
            <div class="profile-header">
                <form method="post" enctype="multipart/form-data">
                    <div class="profile-picture">
                        <img src="<?php echo $fileUrl; ?>" alt="Profile Picture">
                    </div>
                    <input id="fileInput" type="file" accept="image/*" name="profile_picture">
                    <button type="submit" class="delete-profile-button" name="delete_picture">Delete Picture</button>
                </div>
            </div>
            <div class="profile-info">
                <div class="username-section">
                    <label for="username">Username:</label>
                    <input type="text" id="username" name="username" value="<?php echo $username; ?>" required>
                </div>
                <div class="name-section">
                    <label for="full_name">Full Name:</label>
                    <input type="text" id="full_name" name="full_name" value="<?php echo $fullName; ?>" required>
                </div>
                <div class="bio-section">
                    <label for="bio">Bio:</label>
                    <textarea id="bio" name="bio" required><?php echo $bio; ?></textarea>
                </div>
                <button type="submit" class="edit-profile-button" name="save_changes">Save Changes</button>
                <a href="profile.php" class="edit-profile-button">Cancel</a>
            </form>
        </div>
    </div>
    
    <script src="profile.js"></script>
</body>
</html>
