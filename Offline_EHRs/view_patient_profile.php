<?php
session_start();

$host = "localhost";
$db_username = "root";
$db_password = "sathwik@1804";
$db_name = "ehr_system";

$conn = new mysqli($host, $db_username, $db_password, $db_name);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (!isset($_GET['patient_id'])) {
    echo "Invalid access. No patient selected.";
    exit();
}

$patient_id = $_GET['patient_id'];

$query = "SELECT full_name FROM patient WHERE patient_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $patient_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    echo "❌ Patient not found.";
    exit();
}

$patient = $result->fetch_assoc();
$patient_name = $patient['full_name'];

$file_query = "SELECT file_name, file_name_original, uploaded_at FROM medical_records WHERE patient_id = ?";
$stmt_files = $conn->prepare($file_query);
$stmt_files->bind_param("s", $patient_id);
$stmt_files->execute();
$file_result = $stmt_files->get_result();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Patient Profile</title>
    <style>
        body { font-family: Arial; background-color: #f0f8ff; padding: 20px; }
        .profile, .records { background: #fff; padding: 20px; margin-bottom: 20px; border-radius: 10px; box-shadow: 0 0 10px #ccc; }
        h2 { color: #004aad; }
        a { color: #0066cc; text-decoration: none; }
        a:hover { text-decoration: underline; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 10px; border-bottom: 1px solid #ddd; }
    </style>
</head>
<body>

<div class="profile">
    <h2>👤 Patient Profile</h2>
    <p><strong>Patient ID:</strong> <?= htmlspecialchars($patient_id) ?></p>
    <p><strong>Name:</strong> <?= htmlspecialchars($patient_name) ?></p>
</div>

<div class="records">
    <h2>📂 Uploaded Records</h2>
    <?php if ($file_result->num_rows > 0): ?>
        <table>
            <tr><th>File Name</th><th>Uploaded At</th><th>View</th></tr>
            <?php while ($file = $file_result->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($file['file_name_original']) ?></td>
                    <td><?= htmlspecialchars($file['uploaded_at']) ?></td>
                    <td><a href="view_file.php?file=<?= urlencode($file['file_name']) ?>" target="_blank">View</a></td>
                </tr>
            <?php endwhile; ?>
        </table>
    <?php else: ?>
        <p>No records uploaded yet.</p>
    <?php endif; ?>
</div>

</body>
</html>
