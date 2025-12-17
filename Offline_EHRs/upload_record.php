<?php
session_start();

// Database connection settings
$host = "localhost";
$user = "root";
$pass = "sathwik@1804";  // your MySQL password
$dbname = "ehr_system";

// Create connection
$conn = new mysqli($host, $user, $pass, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if patient is logged in
if (!isset($_SESSION['patient_id'])) {
    header("Location: patient_login.php");
    exit();
}

$patient_id = $_SESSION['patient_id'];

// Fetch patient full name (optional, for display)
$stmt = $conn->prepare("SELECT full_name FROM patient WHERE patient_id = ?");
$stmt->bind_param("s", $patient_id);
$stmt->execute();
$result = $stmt->get_result();
$patient = $result->fetch_assoc();

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Upload Medical Record</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #eef6ff;
            padding: 40px;
            text-align: center;
        }
        .upload-container {
            background-color: white;
            max-width: 400px;
            margin: auto;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 0 12px rgba(0,0,0,0.1);
        }
        h2 {
            color: #004aad;
            margin-bottom: 20px;
        }
        input[type="file"] {
            margin: 20px 0;
        }
        button {
            background-color: #004aad;
            color: white;
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 16px;
        }
        button:hover {
            background-color: #00338d;
        }
        a {
            display: inline-block;
            margin-top: 20px;
            color: #004aad;
            text-decoration: none;
        }
        a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="upload-container">
        <h2>Upload Medical Record</h2>
        <p>Welcome, <?php echo htmlspecialchars($patient['full_name']); ?>!</p>
        <form action="upload_process.php" method="POST" enctype="multipart/form-data">
            <input type="file" name="record_file" required>
            <br>
            <button type="submit">Upload</button>
        </form>
        <a href="patient_dashboard.php">Back to Dashboard</a>
    </div>
</body>
</html>

