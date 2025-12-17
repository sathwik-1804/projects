<?php
session_start();
$host = "localhost";
$user = "root";
$pass = "sathwik@1804";  // your actual MySQL password
$dbname = "ehr_system";

$conn = new mysqli($host, $user, $pass, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$identifier = $conn->real_escape_string($_POST['identifier']);
$password = $_POST['password'];

$sql = "SELECT * FROM patient WHERE patient_id = ? OR email = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $identifier, $identifier);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $user = $result->fetch_assoc();
    if (password_verify($password, $user['password'])) {
        // Password correct - create session
        $_SESSION['patient_id'] = $user['patient_id'];
        $_SESSION['patient_name'] = $user['full_name'];
        header("Location: patient_dashboard.php"); // redirect to patient dashboard
        exit();
    } else {
        echo "Incorrect password.";
    }
} else {
    echo "No user found with that ID or email.";
}

$stmt->close();
$conn->close();
?>
