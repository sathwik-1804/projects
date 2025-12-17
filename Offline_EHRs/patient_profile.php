<?php
session_start();

// Database connection details
$host = "localhost";
$user = "root";
$pass = "sathwik@1804";  // change this to your MySQL password
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

// Fetch patient details
$patient_id = $_SESSION['patient_id'];
$stmt = $conn->prepare("SELECT * FROM patient WHERE patient_id = ?");
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
    <title>Patient Profile</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #eef6ff;
            padding: 30px;
        }
        .profile-container {
            background-color: white;
            padding: 30px;
            max-width: 600px;
            margin: auto;
            border-radius: 12px;
            box-shadow: 0 0 12px rgba(0,0,0,0.1);
        }
        h2 {
            color: #004aad;
        }
        .details p {
            margin: 10px 0;
        }
        .upload-section {
            margin-top: 30px;
        }
        input[type="file"] {
            margin-bottom: 10px;
        }
        .btn {
            background-color: #004aad;
            color: white;
            padding: 10px 16px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            margin-right: 10px;
            text-decoration: none;
            display: inline-block;
        }
        .btn:hover {
            background-color: #00338d;
        }
        .logout-btn {
            background-color: #888;
        }
        .logout-btn:hover {
            background-color: #555;
        }
    </style>
</head>
<body>
    <div class="profile-container">
        <h2>Welcome, <?php echo htmlspecialchars($patient['full_name']); ?>!</h2>

        <div class="details">
            <p><strong>Patient ID:</strong> <?php echo htmlspecialchars($patient['patient_id']); ?></p>
            <p><strong>Email:</strong> <?php echo htmlspecialchars($patient['email']); ?></p>
            <p><strong>Date of Birth:</strong> <?php echo htmlspecialchars($patient['dob']); ?></p>
            <p><strong>Gender:</strong> <?php echo htmlspecialchars($patient['gender']); ?></p>
            <p><strong>Blood Group:</strong> <?php echo htmlspecialchars($patient['blood_group']); ?></p>
            <p><strong>Phone:</strong> <?php echo htmlspecialchars($patient['phone']); ?></p>
            <p><strong>Address:</strong> <?php echo nl2br(htmlspecialchars($patient['address'])); ?></p>
            <p><strong>Registered On:</strong> <?php echo htmlspecialchars($patient['created_at']); ?></p>
        </div>

        <div class="upload-section">
            <h3>Upload Medical Record</h3>
            <form action="upload_process.php" method="POST" enctype="multipart/form-data">
                <input type="file" name="record_file" required>
                <button type="submit" class="btn">Upload</button>
            </form>
        </div>

        <div class="view-section" style="margin-top: 20px;">
            <h3>View Your Records</h3>
            <a href="view_records.php" class="btn">View Records</a>
        </div>

        <div style="margin-top: 20px;">
            <a href="logout.php" class="btn logout-btn">Logout</a>
        </div>
    </div>
</body>
</html>
