<?php
session_start();

// Database connection settings
$host = "localhost";
$user = "root";
$pass = "sathwik@1804";
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

// Fetch all medical records for this patient
$stmt = $conn->prepare("SELECT id, file_name, file_path, uploaded_at FROM medical_records WHERE patient_id = ? ORDER BY uploaded_at DESC");
$stmt->bind_param("s", $patient_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Your Uploaded Medical Records</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #eef6ff;
            padding: 40px;
            text-align: center;
        }
        .records-container {
            max-width: 750px;
            margin: auto;
            background: white;
            padding: 30px 40px;
            border-radius: 12px;
            box-shadow: 0 0 12px rgba(0,0,0,0.1);
        }
        h2 {
            color: #004aad;
            margin-bottom: 25px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            font-size: 15px;
        }
        th, td {
            padding: 12px 15px;
            border-bottom: 1px solid #ddd;
            text-align: left;
            vertical-align: middle;
        }
        th {
            background-color: #004aad;
            color: white;
        }
        /* Container for action buttons */
        .action-buttons {
            display: flex;
            gap: 8px;
        }
        a.action-btn {
            padding: 8px 14px;
            border-radius: 6px;
            text-decoration: none;
            color: white;
            font-weight: 600;
            font-size: 14px;
            transition: background-color 0.3s ease;
            white-space: nowrap;
        }
        a.download-link {
            background-color: #004aad;
        }
        a.download-link:hover {
            background-color: #00338d;
        }
        a.show-link {
            background-color: #27ae60;
        }
        a.show-link:hover {
            background-color: #1e8449;
        }
        a.delete-link {
            background-color: #e74c3c;
        }
        a.delete-link:hover {
            background-color: #c0392b;
        }
        a.back-link {
            display: inline-block;
            margin-top: 30px;
            color: #004aad;
            text-decoration: none;
            font-weight: bold;
            font-size: 16px;
        }
        a.back-link:hover {
            text-decoration: underline;
        }
        p.no-records {
            font-size: 16px;
            color: #555;
            margin-top: 30px;
        }
    </style>
</head>
<body>
    <div class="records-container">
        <h2>Your Uploaded Medical Records</h2>
        <?php if ($result->num_rows > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>File Name</th>
                        <th>Uploaded On</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php while($record = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($record['file_name']); ?></td>
                        <td><?php echo htmlspecialchars($record['uploaded_at']); ?></td>
                        <td>
                            <div class="action-buttons">
                                <a class="action-btn download-link" href="<?php echo htmlspecialchars($record['file_path']); ?>" download>Download</a>
                                <a class="action-btn show-link" href="<?php echo htmlspecialchars($record['file_path']); ?>" target="_blank" rel="noopener">Show</a>
                                <a class="action-btn delete-link" href="delete_record.php?id=<?php echo $record['id']; ?>" onclick="return confirm('Are you sure you want to delete this record?');">Delete</a>
                            </div>
                        </td>
                    </tr>
                <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p class="no-records">No medical records found.</p>
        <?php endif; ?>
        <a class="back-link" href="patient_dashboard.php">&larr; Back to Dashboard</a>
    </div>
</body>
</html>

<?php
$stmt->close();
$conn->close();
?>
