<?php
session_start();
if (!isset($_SESSION['doctor_id'])) {
    header("Location: login.php");
    exit();
}

$doctor_id = $_SESSION['doctor_id'];

$conn = new mysqli("localhost", "root", "sathwik@1804", "ehr_system");
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

$stmt = $conn->prepare("SELECT message, created_at FROM doctor_messages WHERE doctor_id = ? ORDER BY created_at DESC");
$stmt->bind_param("s", $doctor_id);
$stmt->execute();
$result = $stmt->get_result();
$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Patient Messages</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f9fafb;
            padding: 40px;
            color: #444;
        }
        .container {
            max-width: 800px;
            margin: auto;
        }
        h2 {
            color: #004080;
            margin-bottom: 25px;
            text-align: center;
        }
        .message {
            background: #fff;
            padding: 20px;
            margin-bottom: 18px;
            border-radius: 8px;
            box-shadow: 0 1px 6px rgba(0,0,0,0.08);
            line-height: 1.5;
        }
        .message small {
            color: #888;
            font-size: 14px;
        }
        a.back-btn {
            display: inline-block;
            margin-top: 15px;
            background-color: #004080;
            color: white;
            padding: 10px 20px;
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
        <h2>Patient Access Messages</h2>
        <?php if ($result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
                <div class="message">
                    <?php 
                    $allowed_tags = '<a><br>';
                    echo strip_tags($row['message'], $allowed_tags);
                    ?>
                    <br>
                    <small>Received on: <?= htmlspecialchars($row['created_at']) ?></small>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p>No messages yet.</p>
        <?php endif; ?>
        <a href="doctor_dashboard.php" class="back-btn">Back to Dashboard</a>
    </div>
</body>
</html>
