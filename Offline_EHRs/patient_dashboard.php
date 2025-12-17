<?php
session_start();

// Database connection settings (put your actual credentials here)
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

if (!isset($_SESSION['patient_id'])) {
    header("Location: patient_login.php");
    exit();
}

$patient_id = $_SESSION['patient_id'];

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
    <title>Patient Dashboard</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f0f8ff;
            padding: 50px;
            text-align: center;
        }
        .container {
            background-color: white;
            max-width: 500px;
            margin: auto;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 0 12px rgba(0,0,0,0.1);
        }
        h2 {
            color: #004aad;
            margin-bottom: 30px;
        }
        .btn {
            display: block;
            background-color: #004aad;
            color: white;
            padding: 14px;
            margin: 10px 0;
            text-decoration: none;
            border-radius: 8px;
            font-size: 16px;
        }
        .btn:hover {
            background-color: #00338d;
        }
        .logout {
            margin-top: 30px;
            color: #888;
            text-decoration: none;
        }
        .logout:hover {
            color: red;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Welcome, <?php echo htmlspecialchars($patient['full_name']); ?> 👋</h2>

        <a href="patient_profile.php" class="btn">View Profile</a>
        <a href="upload_record.php" class="btn">Upload Medical Record</a>
        <a href="view_records.php" class="btn">View Uploaded Records</a>
        <a href="grant_permission.php" class="btn">Grant Record Access</a>
        <a href="patient_prescriptions.php" class="btn">View Consultancy Prescriptions</a>

        <a href="logout.php" class="logout">Logout</a>
    </div>
</body>
</html>
