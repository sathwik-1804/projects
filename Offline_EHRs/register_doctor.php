<?php
$host = "localhost";
$db = "your_database";
$user = "your_user";
$pass = "your_password";

// Create DB connection
$conn = new mysqli($host, $user, $pass, $db);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get posted data
$data = json_decode(file_get_contents("php://input"), true);

$doctor_id = $data['doctorId'];
$full_name = $data['fullName'];
$hospital_name = $data['hospitalName'];
$dob = $data['dob'];
$gender = $data['gender'];
$email = $data['email'];
$specialization = $data['specialization'];
$department = $data['department'];
$designation = $data['designation'];
$experience = $data['experience'];
$password = password_hash($data['password'], PASSWORD_DEFAULT);

// SQL Insert
$sql = "INSERT INTO doctors (doctor_id, full_name, hospital_name, dob, gender, email, specialization, department, designation, experience, password)
VALUES ('$doctor_id', '$full_name', '$hospital_name', '$dob', '$gender', '$email', '$specialization', '$department', '$designation', '$experience', '$password')";

if ($conn->query($sql) === TRUE) {
    echo json_encode(["message" => "Doctor registered successfully"]);
} else {
    echo json_encode(["error" => $conn->error]);
}

$conn->close();
?>
