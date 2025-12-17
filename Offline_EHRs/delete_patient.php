<?php
$servername = "localhost";
$username = "root";
$password = "sathwik@1804";
$dbname = "ehr_system";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Delete if 'patient_id' is provided (using prepared statement)
if (isset($_GET['patient_id'])) {
    $patient_id = $_GET['patient_id'];

    $stmt = $conn->prepare("DELETE FROM patients WHERE patient_id = ?");
    $stmt->bind_param("s", $patient_id);
    $stmt->execute();
    $stmt->close();
}

// Fetch remaining patients
$sql = "SELECT * FROM patients";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Patient Records</title>
    <style>
        body {
            background-color: #0d1117;
            color: #c9d1d9;
            font-family: Arial, sans-serif;
            padding: 40px;
            text-align: center;
        }
        table {
            margin: auto;
            width: 90%;
            max-width: 1000px;
            border-collapse: collapse;
            background-color: #161b22;
        }
        th, td {
            padding: 12px;
            border: 1px solid #30363d;
        }
        th {
            background-color: #21262d;
            color: #58a6ff;
        }
        tr:hover {
            background-color: #21262d;
        }
        a {
            color: #58a6ff;
            text-decoration: none;
        }
        .btn {
            display: inline-block;
            margin-top: 30px;
            padding: 10px 20px;
            background-color: #58a6ff;
            color: white;
            border-radius: 5px;
            text-decoration: none;
        }
        .btn:hover {
            background-color: #0077cc;
        }
    </style>
</head>
<body>

<h1>Updated Patient Records</h1>

<?php
if ($result->num_rows > 0) {
    echo "<table>
            <tr>
                <th>Patient ID</th>
                <th>Name</th>
                <th>Age</th>
                <th>Gender</th>
                <th>Disease</th>
                <th>Prescription</th>
                <th>Actions</th>
            </tr>";
    while ($row = $result->fetch_assoc()) {
        echo "<tr>
                <td>" . htmlspecialchars($row['patient_id']) . "</td>
                <td>" . htmlspecialchars($row['name']) . "</td>
                <td>" . htmlspecialchars($row['age']) . "</td>
                <td>" . htmlspecialchars($row['gender']) . "</td>
                <td>" . htmlspecialchars($row['disease']) . "</td>
                <td>" . htmlspecialchars($row['prescription']) . "</td>
                <td>
                    <a href='edit_patient.php?patient_id=" . urlencode($row['patient_id']) . "'>Edit</a> |
                    <a href='delete_patient.php?patient_id=" . urlencode($row['patient_id']) . "' onclick=\"return confirm('Are you sure you want to delete this record?');\">Delete</a>
                </td>
              </tr>";
    }
    echo "</table>";
} else {
    echo "<p>No patient records found.</p>";
}
?>

<!-- Dashboard Button Always Visible -->
<a href='dashboard.php' class='btn'>Go to Dashboard</a>

</body>
</html>

<?php $conn->close(); ?>
