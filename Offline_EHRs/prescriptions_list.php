<?php
session_start();

// DB connection details
$servername = "localhost";
$username = "root";
$password = "sathwik@1804";
$dbname = "ehr_system";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (!isset($_SESSION['doctor_id']) || !isset($_SESSION['doctor_internal_id'])) {
    header("Location: login.php");
    exit();
}
$doctor_id = $_SESSION['doctor_internal_id']; // numeric ID

// Fetch prescriptions for this doctor
$stmt = $conn->prepare("
    SELECT cp.prescription, cp.notes, cp.created_at, p.patient_id
    FROM consultancy_prescriptions cp
    JOIN patient p ON cp.patient_id = p.id
    WHERE cp.doctor_id = ?
    ORDER BY cp.created_at DESC
");
$stmt->bind_param("i", $doctor_id);
$stmt->execute();
$presc_result = $stmt->get_result(); // ✅ This line was missing
?>
<!DOCTYPE html>
<html>
<head>
    <title>Your Submitted Prescriptions</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            padding: 40px;
            background: #f4f4f4;
        }
        .section {
            background: white;
            padding: 20px;
            max-width: 700px;
            margin: 0 auto;
            border-radius: 8px;
            box-shadow: 0 2px 6px rgba(0,0,0,0.1);
        }
        .prescription {
            border-bottom: 1px solid #ddd;
            padding: 15px 0;
        }
        .prescription:last-child {
            border-bottom: none;
        }
        p {
            margin: 5px 0;
        }
        small {
            color: #666;
        }
        h3 {
            margin-bottom: 20px;
            color: #004080;
        }
    </style>
</head>
<body>

<div class="section" id="my-prescriptions">
    <h3>Your Submitted Prescriptions</h3>
    <?php if ($presc_result->num_rows > 0): ?>
        <?php while ($presc = $presc_result->fetch_assoc()): ?>
            <div class="prescription">
                <p><strong>Patient ID:</strong> <?= htmlspecialchars($presc['patient_id']) ?></p>
                <p><strong>Prescription:</strong><br><?= nl2br(htmlspecialchars($presc['prescription'])) ?></p>
                <?php if (!empty($presc['notes'])): ?>
                    <p><strong>Notes:</strong><br><?= nl2br(htmlspecialchars($presc['notes'])) ?></p>
                <?php endif; ?>
                <small>Submitted on: <?= htmlspecialchars($presc['created_at']) ?></small>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <p>No prescriptions submitted yet.</p>
    <?php endif; ?>
</div>

</body>
</html>
