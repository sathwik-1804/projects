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

// Check patient logged in
if (!isset($_SESSION['patient_id'])) {
    header("Location: patient_login.php");
    exit();
}

$patient_id = $_SESSION['patient_id'];

if (isset($_FILES['record_file']) && $_FILES['record_file']['error'] === UPLOAD_ERR_OK) {
    $fileTmpPath = $_FILES['record_file']['tmp_name'];
    $fileNameOriginal = $_FILES['record_file']['name'];

    // Sanitize original filename (remove special chars, spaces)
    $fileNameOriginalClean = preg_replace("/[^a-zA-Z0-9.\-_]/", "_", $fileNameOriginal);

    $fileNameCmps = explode(".", $fileNameOriginalClean);
    $fileExtension = strtolower(end($fileNameCmps));

    // Create a hashed filename for saving on server to avoid conflicts
    $hashedFileName = md5(time() . $fileNameOriginalClean) . '.' . $fileExtension;

    $uploadFileDir = __DIR__ . '/uploads/';

    // Create uploads folder if it doesn't exist
    if (!is_dir($uploadFileDir)) {
        mkdir($uploadFileDir, 0755, true);
    }

    $dest_path = $uploadFileDir . $hashedFileName;

    // Move the uploaded file to the uploads folder
    if (move_uploaded_file($fileTmpPath, $dest_path)) {
        // Store relative path for portability (used for accessing file)
        $relativePath = 'uploads/' . $hashedFileName;

        // Insert file info into the database
        $stmt = $conn->prepare("INSERT INTO medical_records (patient_id, file_name, file_name_original, file_path, uploaded_at) VALUES (?, ?, ?, ?, NOW())");
        $stmt->bind_param("ssss", $patient_id, $hashedFileName, $fileNameOriginalClean, $relativePath);
        $stmt->execute();
        $stmt->close();

        // Redirect on success
        header("Location: patient_profile.php?upload=success");
        exit();
    } else {
        // Redirect if moving file failed
        header("Location: patient_profile.php?upload=error");
        exit();
    }
} else {
    // Redirect if no file uploaded or upload error
    header("Location: patient_profile.php?upload=error");
    exit();
}

$conn->close();
?>
