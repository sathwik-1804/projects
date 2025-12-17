<?php
$servername = "localhost";
$username = "root";
$password = "sathwik@1804";
$dbname = "ehr_system";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Initialize variable
$row = null;

if (isset($_GET['patient_id'])) {
    $patient_id = $conn->real_escape_string($_GET['patient_id']);
    
    $sql = "SELECT * FROM patients WHERE patient_id = '$patient_id'";
    $result = $conn->query($sql);

    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
    } else {
        die("Patient not found.");
    }
} else {
    die("No patient ID specified.");
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Patient</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #0d1117;
            color: white;
        }
        h1 {
            text-align: center;
            color: #58a6ff;
        }
        .form-container {
            width: 50%;
            margin: auto;
            background-color: #161b22;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.3);
        }
        input, textarea, select {
            width: 100%;
            padding: 10px;
            margin: 12px 0;
            border: none;
            border-radius: 6px;
            background-color: #21262d;
            color: white;
        }
        input[type="submit"] {
            background-color: #2ea043;
            cursor: pointer;
            font-weight: bold;
        }
        input[type="submit"]:hover {
            background-color: #238636;
        }
    </style>
</head>
<body>
    <h1>Edit Patient</h1>
    <div class="form-container">
        <form method="POST" action="update_patient.php">
            <!-- Use patient_id as hidden input -->
            <input type="hidden" name="patient_id" value="<?= htmlspecialchars($row['patient_id']) ?>">

            Name:
            <input type="text" name="name" value="<?= htmlspecialchars($row['name']) ?>" required>

            Age:
            <input type="number" name="age" value="<?= htmlspecialchars($row['age']) ?>" required>

            Gender:
            <select name="gender" required>
                <option value="Male" <?= $row['gender'] == 'Male' ? 'selected' : '' ?>>Male</option>
                <option value="Female" <?= $row['gender'] == 'Female' ? 'selected' : '' ?>>Female</option>
                <option value="Other" <?= $row['gender'] == 'Other' ? 'selected' : '' ?>>Other</option>
            </select>

            Disease:
            <input type="text" name="disease" value="<?= htmlspecialchars($row['disease']) ?>" required>

            Prescription:
            <textarea name="prescription" rows="4" required><?= htmlspecialchars($row['prescription']) ?></textarea>

            <input type="submit" value="Update">
        </form>
    </div>
</body>
</html>
