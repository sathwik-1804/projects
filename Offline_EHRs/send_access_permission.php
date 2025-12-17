<?php
session_start();

// 🔗 DB Connection (embedded)
$host = "localhost";
$db_username = "root";
$db_password = "sathwik@1804";
$db_name = "ehr_system";

$conn = new mysqli($host, $db_username, $db_password, $db_name);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (!isset($_SESSION['patient_id'])) {
    header("Location: patient_login.php");
    exit();
}

$patient_id = $_SESSION['patient_id'];
$doctor_id = $_POST['doctor_id'];
$hospital_name = $_POST['hospital_name'];

// Get patient name and disease
$query = "SELECT name, disease FROM patients WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $patient_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $patient = $result->fetch_assoc();
    $patient_name = $patient['name'];
    $disease = $patient['disease'];

    // Patient profile URL (same page view)
    $profile_url = "view_patient_profile.php?patient_id=" . urlencode($patient_id);

    // Doctor message with link
    $message = "A patient has granted you access.\n"
             . "<a href='$profile_url'>View Patient ID: $patient_id</a>\n"
             . "Hospital: $hospital_name";

    $insert = "INSERT INTO doctor_messages (doctor_id, message, created_at) VALUES (?, ?, NOW())";
    $stmt2 = $conn->prepare($insert);
    $stmt2->bind_param("is", $doctor_id, $message);

    if ($stmt2->execute()) {
        echo "✅ Access granted. Doctor notified with link to profile.";
    } else {
        echo "❌ Error: Unable to send message.";
    }
} else {
    echo "❌ Error: Patient not found.";
}
?>
