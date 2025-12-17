<?php
session_start();

$host = "localhost";
$user = "root";
$pass = "sathwik@1804";
$dbname = "ehr_system";

$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (!isset($_SESSION['patient_id'])) {
    header("Location: patient_login.php");
    exit();
}

$patient_id = $_SESSION['patient_id'];

// Ensure record ID is provided
if (isset($_GET['id'])) {
    $record_id = $_GET['id'];

    // Verify ownership and get file path
    $stmt = $conn->prepare("SELECT file_path FROM medical_records WHERE id = ? AND patient_id = ?");
    $stmt->bind_param("is", $record_id, $patient_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        $file_path = $row['file_path'];

        // Delete file from server
        if (file_exists($file_path)) {
            unlink($file_path);
        }

        // Delete from database
        $delete_stmt = $conn->prepare("DELETE FROM medical_records WHERE id = ? AND patient_id = ?");
        $delete_stmt->bind_param("is", $record_id, $patient_id);
        $delete_stmt->execute();
        $delete_stmt->close();
    }

    $stmt->close();
}

$conn->close();

// Redirect back to view page
header("Location: view_records.php");
exit();
?>
