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
$patientId = $conn->real_escape_string($_POST['patientId']);
$fullName = $conn->real_escape_string($_POST['patientName']);
$dob = $conn->real_escape_string($_POST['patientDob']);
$gender = $conn->real_escape_string($_POST['patientGender']);
$bloodGroup = $conn->real_escape_string($_POST['bloodGroup']);
$email = $conn->real_escape_string($_POST['emailPatient']);
$address = $conn->real_escape_string($_POST['address']);
$phone = $conn->real_escape_string($_POST['contactNumber']);
$password = $_POST['passwordPatient'];
$confirmPassword = $_POST['confirmPasswordPatient'];

// Validate passwords
if ($password !== $confirmPassword) {
    die("Passwords do not match.");
}

// Hash password
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

// Check if patient_id or email already exists
$sqlCheck = "SELECT * FROM patient WHERE patient_id = '$patientId' OR email = '$email'";
$resultCheck = $conn->query($sqlCheck);

if ($resultCheck->num_rows > 0) {
    die("Patient ID or Email already registered.");
}

// Insert data
$sql = "INSERT INTO patient (patient_id, full_name, dob, gender, blood_group, email, address, phone, password)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";

$stmt = $conn->prepare($sql);
$stmt->bind_param(
    "sssssssss", 
    $patientId, $fullName, $dob, $gender, $bloodGroup, $email, $address, $phone, $hashedPassword
);

if ($stmt->execute()) {
    echo "Patient registered successfully! <a href='login.php'>Login here</a>";
} else {
    echo "Error: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>
