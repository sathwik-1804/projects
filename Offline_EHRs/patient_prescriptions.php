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
$session_patient_id = $_SESSION['patient_id'];

$id_query = $conn->prepare("SELECT id FROM patient WHERE patient_id = ?");
$id_query->bind_param("s", $session_patient_id);
$id_query->execute();
$id_result = $id_query->get_result();

if ($id_result->num_rows === 0) {
    echo "Patient not found.";
    exit();
}

$patient_row = $id_result->fetch_assoc();
$patient_id = $patient_row['id']; // Now this is the correct numeric ID to use

$stmt = $conn->prepare("
    SELECT p.prescription, p.notes, p.created_at, d.full_name AS doctor_name
    FROM consultancy_prescriptions p
    JOIN doctors d ON p.doctor_id = d.id
    WHERE p.patient_id = ?
    ORDER BY p.created_at DESC
");
$stmt->bind_param("i", $patient_id);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html>
<head>
    <title>My Consultancy Prescriptions</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #eef5ff;
            padding: 30px;
        }
        .container {
            max-width: 700px;
            margin: auto;
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
        }
        h2 {
            color: #004aad;
            margin-bottom: 20px;
            text-align: center;
        }
        .prescription {
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 15px 20px;
            margin-bottom: 15px;
            background-color: #f9faff;
        }
        .prescription h3 {
            margin-top: 0;
            color: #003366;
        }
        .prescription p {
            margin: 8px 0;
        }
        .prescription small {
            color: #666;
        }
        a.back-btn {
            display: inline-block;
            margin-bottom: 20px;
            background-color: #004aad;
            color: white;
            padding: 10px 15px;
            text-decoration: none;
            border-radius: 6px;
        }
        a.back-btn:hover {
            background-color: #00338d;
        }
    </style>
</head>
<body>
    <div class="container">
        <a href="patient_dashboard.php" class="back-btn">&larr; Back to Dashboard</a>
        <h2>Consultancy Prescriptions</h2>

        <?php if ($result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
                <div class="prescription">
                    <h3>From Dr. <?= htmlspecialchars($row['doctor_name']) ?></h3>
                    <p><strong>Prescription:</strong><br><?= nl2br(htmlspecialchars($row['prescription'])) ?></p>
                    <?php if (!empty($row['notes'])): ?>
                        <p><strong>Notes:</strong><br><?= nl2br(htmlspecialchars($row['notes'])) ?></p>
                    <?php endif; ?>
                  <small>Submitted on: <?= htmlspecialchars($row['created_at']) ?></small><br>
<form action="download_prescription.php" method="post" style="margin-top: 8px;">
    <input type="hidden" name="doctor_name" value="<?= htmlspecialchars($row['doctor_name']) ?>">
    <input type="hidden" name="prescription" value="<?= htmlspecialchars($row['prescription']) ?>">
    <input type="hidden" name="notes" value="<?= htmlspecialchars($row['notes']) ?>">
    <input type="hidden" name="date" value="<?= htmlspecialchars($row['created_at']) ?>">
    <button type="submit">Download</button>
</form>

                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p>No consultancy prescriptions found.</p>
        <?php endif; ?>

    </div>
</body>
</html>

<?php
$stmt->close();
$conn->close();
?>
