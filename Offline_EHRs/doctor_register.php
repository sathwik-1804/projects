<?php
// Database connection details
$servername = "localhost";
$username = "root";
$password = "sathwik@1804";
$dbname = "ehr_system";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get POST data and sanitize
$doctorId = $conn->real_escape_string($_POST['doctorId']);
$fullName = $conn->real_escape_string($_POST['fullName']);
$hospitalName = $conn->real_escape_string($_POST['hospitalName']);
$dob = $conn->real_escape_string($_POST['dob']);
$gender = $conn->real_escape_string($_POST['gender']);
$email = $conn->real_escape_string($_POST['email']);
$specialization = $conn->real_escape_string($_POST['specialization']);
$department = $conn->real_escape_string($_POST['department']);
$designation = $conn->real_escape_string($_POST['designation']);
$experience = (int)$_POST['experience'];
$password = $_POST['password'];
$confirmPassword = $_POST['confirmPassword'];

// Validate passwords
if ($password !== $confirmPassword) {
    die("Passwords do not match.");
}

// Hash password
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

// Check if doctor_id or email already exists
$sqlCheck = "SELECT * FROM doctors WHERE doctor_id = '$doctorId' OR email = '$email'";
$resultCheck = $conn->query($sqlCheck);

if ($resultCheck->num_rows > 0) {
    die("Doctor ID or Email already registered.");
}

// Insert data
$sql = "INSERT INTO doctors (doctor_id, full_name, hospital_name, dob, gender, email, specialization, department, designation, experience, password) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

$stmt = $conn->prepare($sql);
$stmt->bind_param(
    "sssssssssis", 
    $doctorId, $fullName, $hospitalName, $dob, $gender, $email, $specialization, $department, $designation, $experience, $hashedPassword
);

if ($stmt->execute()) {
    echo "Doctor registered successfully! <a href='login.php'>Login here</a>";
} else {
    echo "Error: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>