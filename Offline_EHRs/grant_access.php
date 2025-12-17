<?php
session_start();

// DB connection (replace with your credentials)
$servername = "localhost";
$username = "root";
$password = "sathwik@1804";
$dbname = "ehr_system";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (!isset($_SESSION['patient_id'])) {
    header("Location: patient_login.php");
    exit();
}

$patient_id = $_SESSION['patient_id'];
$doctor_id = $_POST['doctor_id'] ?? '';
$hospital_name = $_POST['hospital_name'] ?? '';

if (empty($doctor_id) || empty($hospital_name)) {
    die("Doctor ID and Hospital Name are required.");
}

// Check if patient exists
$query = "SELECT full_name FROM patient WHERE patient_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $patient_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    die("Patient not found.");
}

$patient = $result->fetch_assoc();

// Check if doctor exists before proceeding
$doctor_check = "SELECT doctor_id FROM doctors WHERE doctor_id = ?";
$stmt2 = $conn->prepare($doctor_check);
$stmt2->bind_param("s", $doctor_id);
$stmt2->execute();
$res_doctor = $stmt2->get_result();

if ($res_doctor->num_rows == 0) {
    die("Doctor not found. Cannot grant access.");
}

// Insert access permission
$insert_permission = "INSERT INTO record_permissions (patient_id, doctor_id, hospital_name) VALUES (?, ?, ?)";
$stmt3 = $conn->prepare($insert_permission);
$stmt3->bind_param("sss", $patient_id, $doctor_id, $hospital_name);

if (!$stmt3->execute()) {
    die("Failed to insert permission: " . $stmt3->error);
}

// Compose message with link to view patient
$link = "view_patient_profile.php?patient_id=" . urlencode($patient_id);
$message = "You have been granted access to patient records by Patient ID: <a href='$link'>$patient_id</a>.";

// Insert message to doctor_messages
$insert_msg = "INSERT INTO doctor_messages (doctor_id, message) VALUES (?, ?)";
$stmt4 = $conn->prepare($insert_msg);
$stmt4->bind_param("ss", $doctor_id, $message);

if (!$stmt4->execute()) {
    die("Failed to send message to doctor: " . $stmt4->error);
}

echo "Access granted and message sent to doctor.";
?>
