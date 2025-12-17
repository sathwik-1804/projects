<?php
session_start();
if (!isset($_SESSION['doctor_id'])) {
    header("Location: login.php");
    exit();
}

$doctor_id = $_SESSION['doctor_id'];

// DB Connection
$conn = new mysqli("localhost", "root", "sathwik@1804", "ehr_system");
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

$stmt = $conn->prepare("SELECT * FROM doctors WHERE doctor_id = ?");
$stmt->bind_param("s", $doctor_id);
$stmt->execute();
$doctor = $stmt->get_result()->fetch_assoc();
$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Doctor Profile</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #eef2f7;
            padding: 40px;
            color: #333;
        }
        .container {
            max-width: 700px;
            margin: 0 auto;
            background: #fff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        h2 {
            color: #004080;
            margin-bottom: 25px;
            text-align: center;
        }
        p {
            font-size: 18px;
            margin: 12px 0;
        }
        strong {
            color: #004080;
        }
        a.back-btn {
            display: inline-block;
            margin-top: 20px;
            background-color: #004080;
            color: white;
            padding: 10px 25px;
            border-radius: 6px;
            text-decoration: none;
            transition: background-color 0.3s ease;
        }
        a.back-btn:hover {
            background-color: #0066cc;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Your Profile</h2>
        <?php if ($doctor): ?>
            <p><strong>Doctor ID:</strong> <?= htmlspecialchars($doctor['doctor_id']) ?></p>
            <p><strong>Full Name:</strong> <?= htmlspecialchars($doctor['full_name']) ?></p>
            <p><strong>Hospital Name:</strong> <?= htmlspecialchars($doctor['hospital_name']) ?></p>
            <p><strong>Date of Birth:</strong> <?= htmlspecialchars($doctor['dob']) ?></p>
            <p><strong>Gender:</strong> <?= htmlspecialchars($doctor['gender']) ?></p>
            <p><strong>Email:</strong> <?= htmlspecialchars($doctor['email']) ?></p>
            <p><strong>Specialization:</strong> <?= htmlspecialchars($doctor['specialization']) ?></p>
            <p><strong>Department:</strong> <?= htmlspecialchars($doctor['department']) ?></p>
            <p><strong>Designation:</strong> <?= htmlspecialchars($doctor['designation']) ?></p>
            <p><strong>Experience:</strong> <?= htmlspecialchars($doctor['experience']) ?> years</p>
        <?php else: ?>
            <p>Doctor profile not found.</p>
        <?php endif; ?>
        <a href="doctor_dashboard.php" class="back-btn">Back to Dashboard</a>
    </div>
</body>
</html>
