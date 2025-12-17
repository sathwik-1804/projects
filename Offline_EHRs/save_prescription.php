<?php
session_start();

if (!isset($_SESSION['doctor_id'])) {
    header("Location: doctor_login.php");
    exit();
}

$doctor_id = $_SESSION['doctor_id'];

$servername = "localhost";
$username = "root";
$password = "sathwik@1804";
$dbname = "ehr_system";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$patient_id = $_POST['patient_id'];
$prescription = $_POST['prescription'];

$stmt = $conn->prepare("INSERT INTO consultancy_prescriptions (doctor_id, patient_id, prescription) VALUES (?, ?, ?)");
$stmt->bind_param("iis", $doctor_id, $patient_id, $prescription);

if ($stmt->execute()) {
    echo "✅ Prescription saved successfully. <a href='doctor_dashboard.php'>Back to Dashboard</a>";
} else {
    echo "❌ Error saving prescription: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>
