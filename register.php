<?php
// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Get form data
    $username = $_POST["username"];
    $password = $_POST["password"];
    $phone = $_POST["phone"];
    $dob = $_POST["dob"];
    $gender = $_POST["gender"];
    $email = $_POST["email"];
    $fullname = $_POST["fullname"];
    $level = $_POST["level"];
    $faculty = $_POST["faculty"];
    $department = $_POST["department"];
    $title = $_POST["title"];
    $status = $_POST["status"];

    // Database connection
    $servername = "localhost";
    $dbUsername = "root";
    $dbPassword = "";
    $dbname = "nimisocials";

    $conn = new mysqli($servername, $dbUsername, $dbPassword, $dbname);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Check if the username, password, or email already exists
    $checkQuery = "SELECT * FROM persons WHERE username = '$username' OR password = '$password' OR email = '$email'";
    $result = $conn->query($checkQuery);
    if ($result->num_rows > 0) {
        // Duplicate username, password, or email found
        echo "Username, password, or email already exists. Please choose a different one.";
    } else {
        // Insert data into the "persons" table
        $sql_persons = "INSERT INTO persons (username, full_name, date_of_birth, phone, email, gender, password, levels, title_in_school, status)
                VALUES ('$username', '$fullname', '$dob', '$phone', '$email', '$gender', '$password', '$level', '$title', '$status')";

        if ($conn->query($sql_persons) === TRUE) {
            echo "Data inserted into persons table successfully.";

            // Get the inserted person ID
            $person_id = $conn->insert_id;

            // Insert data into the "faculty" table
            $sql_faculty = "INSERT INTO faculty (faculty_name, person_id)
                    VALUES ('$faculty', $person_id)";

            if ($conn->query($sql_faculty) === TRUE) {
                echo "Data inserted into faculty table successfully.";

                // Insert data into the "department" table
                $sql_department = "INSERT INTO department (department_name, person_id)
                        VALUES ('$department', $person_id)";

                if ($conn->query($sql_department) === TRUE) {
                    // Redirect to home page
                    header("Location: home.php");
                    exit(); // Stop script execution after the redirect
                } else {
                    echo "Error: " . $sql_department . "<br>" . $conn->error;
                }

            } else {
                echo "Error: " . $sql_faculty . "<br>" . $conn->error;
            }
        } else {
            echo "Error: " . $sql_persons . "<br>" . $conn->error;
        }
    }

    // Close the database connection
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nimisocial signup page</title>
    <link rel="stylesheet" href="register.css">
</head>
<body>
    <nav>
        <div class="container">
            <h2 class="log">UniChat</h2>
        </div>
    </nav>
    <div class="wrapper">
        <form id="signup-form" method="POST">
            <label for="username">Username:</label>
            <input type="text" id="username" name="username" required>
            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required>
            <label for="phone">Phone Number:</label>
            <input type="tel" id="phone" name="phone" required>
            <label for="dob">Date of Birth</label>
            <input type="date" id="dob" name="dob" required>
            <label>Gender:</label>
            <input type="radio" id="male" name="gender" value="male" required>
            <label for="male">Male</label>
            <input type="radio" id="female" name="gender" value="female" required>
            <label for="female">Female</label>
            <input type="radio" id="other" name="gender" value="other" required>
            <label for="other">Other</label>
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" required>
            <label for="fullname">Full Name:</label>
            <input type="text" id="fullname" name="fullname" required>
            <label for="level">Level:</label>
            <select id="level" name="level" required>
                <option value="">Select Level</option>
                <option value="100">100</option>
                <option value="200">200</option>
                <option value="300">300</option>
                <option value="400">400</option>
            </select>
            <label for="faculty">Faculty:</label>
            <select id="faculty" name="faculty" required>
                <option value="">Select Faculty</option>
                <option value="Applied Sciences">Applied Sciences</option>
                <option value="Business School">Business School</option>
                <option value="Health Sciences">Health Sciences</option>
                <option value="School of Graduate Studies &amp; Research">School of Graduate Studies &amp; Research</option>
            </select>
            <label for="department">Department:</label>
            <select id="department" name="department" required>
                <option value="">Select Department</option>
            </select>
            <label for="title">Title/Postition in School:</label>
            <input type="text" name="title" id="title" required>
            <label>Student Status</label>
            <div>
                <input type="radio" name="status" id="weekend" value="weekend" required>
                <label for="weekend">Weekend</label>
            </div>
            <div>
                <input type="radio" name="status" id="regular" value="regular" required>
                <label for="regular">Regular</label>
            </div>
            <button type="submit" name="submit">Sign Up</button>
        </form>
    </div>
    <div id="success-message"></div>
    <script src="register.js"></script>
</body>
</html>
