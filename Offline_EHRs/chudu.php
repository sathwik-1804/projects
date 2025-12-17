
<?php

session_start();

// Database connection settings

$host = "localhost";

$user = "root";

$pass = "sathwik@1804"; // your MySQL password

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

      max-width: 700px;

      margin: auto;

      background: white;

      padding: 30px;

      border-radius: 12px;

      box-shadow: 0 0 12px rgba(0,0,0,0.1);

    }

    h2 {

      color: #004aad;

      margin-bottom: 20px;

    }

    table {

      width: 100%;

      border-collapse: collapse;

      margin-top: 20px;

    }

    th, td {

      padding: 12px;

      border-bottom: 1px solid #ddd;

      text-align: left;

    }

    th {

      background-color: #004aad;

      color: white;

    }

    a.download-link {

      background-color: #004aad;

      color: white;

      padding: 6px 12px;

      border-radius: 6px;

      text-decoration: none;

    }

    a.download-link:hover {

      background-color: #00338d;

    }

    a.back-link {

      display: inline-block;

      margin-top: 25px;

      color: #004aad;

      text-decoration: none;

      font-weight: bold;

    }

    a.back-link:hover {

      text-decoration: underline;

    }

  </style>

</head>

<body>

  <div class="records-container">

    <h2>Your Uploaded Medical Records</h2>

    <?php if ($result->num_rows > 0): ?>

      <table>

        <tr>

          <th>File Name</th>

          <th>Uploaded On</th>

          <th>Download</th>

        </tr>

        <?php while($record = $result->fetch_assoc()): ?>

        <tr>

          <td><?php echo htmlspecialchars($record['file_name']); ?></td>

          <td><?php echo htmlspecialchars($record['uploaded_at']); ?></td>

          <td><a class="download-link" href="<?php echo htmlspecialchars($record['file_path']); ?>" download>Download</a></td>

        </tr>

        <?php endwhile; ?>

      </table>

    <?php else: ?>

      <p>No medical records found.</p>

    <?php endif; ?>

    <a class="back-link" href="patient_dashboard.php">&larr; Back to Dashboard</a>

  </div>

</body>

</html>

<?php

$stmt->close();

$conn->close();

?>



