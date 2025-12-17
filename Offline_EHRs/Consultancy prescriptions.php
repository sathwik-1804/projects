<?php
session_start();
if (!isset($_SESSION['doctor_id'])) {
    header("Location: login.php");
    exit();
}

if (!isset($_SESSION['doctor_internal_id'])) {
    // If doctor is not logged in properly or session expired
    header("Location: login.php");
    exit();
}

$doctor_id = $_SESSION['doctor_internal_id'];  // use numeric doctor id


$success = $error = "";

$conn = new mysqli("localhost", "root", "sathwik@1804", "ehr_system");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $patient_id = trim($_POST['patient_id']);
    $prescription = trim($_POST['prescription']);
    $notes = trim($_POST['notes'] ?? '');

    if ($patient_id === '' || $prescription === '') {
        $error = "Please fill in all required fields.";
    } elseif (!preg_match('/^[a-zA-Z0-9\-]+$/', $patient_id)) {
        $error = "Patient ID format is invalid. Only letters, numbers, and dashes allowed.";
    } else {
        // Verify patient exists using patient_id VARCHAR
        $stmtCheck = $conn->prepare("SELECT id FROM patient WHERE patient_id = ?");
        $stmtCheck->bind_param("s", $patient_id);
        $stmtCheck->execute();
        $resultCheck = $stmtCheck->get_result();

        if ($resultCheck->num_rows === 0) {
            $error = "Patient ID does not exist.";
        } else {
            // Get internal integer id for patient to insert into prescription tables if needed
            $row = $resultCheck->fetch_assoc();
            $internal_patient_id = $row['id'];
        }
        $stmtCheck->close();

        if (!$error) {
            // Insert into consultancy_prescriptions using internal_patient_id (int)
            $stmt1 = $conn->prepare("INSERT INTO consultancy_prescriptions (doctor_id, patient_id, prescription, notes) VALUES (?, ?, ?, ?)");
            $stmt1->bind_param("iiss", $doctor_id, $internal_patient_id, $prescription, $notes);

            // Insert into patients_prescriptions using internal_patient_id (int)
            $stmt2 = $conn->prepare("INSERT INTO patients_prescriptions (doctor_id, patient_id, prescription, notes) VALUES (?, ?, ?, ?)");
            $stmt2->bind_param("iiss", $doctor_id, $internal_patient_id, $prescription, $notes);

            if ($stmt1->execute() && $stmt2->execute()) {
                $success = "Prescription submitted successfully and visible in patient dashboard.";
            } else {
                $error = "Failed to submit prescription: " . ($stmt1->error ?: $stmt2->error);
            }

            $stmt1->close();
            $stmt2->close();
        }
    }
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<title>Submit Prescription</title>
<style>
    body {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        background: #f7f9fc;
        padding: 40px;
        color: #333;
    }
    .container {
        max-width: 600px;
        margin: auto;
        background: white;
        padding: 30px 40px;
        border-radius: 10px;
        box-shadow: 0 3px 20px rgba(0,0,0,0.1);
    }
    h2 {
        color: #004080;
        margin-bottom: 25px;
        text-align: center;
    }
    label {
        display: block;
        font-weight: 600;
        margin: 10px 0 6px;
    }
    input[type="text"],
    textarea {
        width: 100%;
        padding: 10px 12px;
        border-radius: 6px;
        border: 1px solid #ccc;
        font-size: 16px;
        resize: vertical;
        box-sizing: border-box;
    }
    textarea {
        min-height: 100px;
    }
    input[type="submit"] {
        background-color: #004080;
        color: white;
        border: none;
        padding: 12px 30px;
        border-radius: 8px;
        cursor: pointer;
        font-size: 16px;
        margin-top: 15px;
        transition: background-color 0.3s ease;
    }
    input[type="submit"]:hover {
        background-color: #0066cc;
    }
    .success {
        color: green;
        font-weight: 600;
        margin-bottom: 20px;
        text-align: center;
    }
    .error {
        color: red;
        font-weight: 600;
        margin-bottom: 20px;
        text-align: center;
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
        text-align: center;
    }
    a.back-btn:hover {
        background-color: #0066cc;
    }
</style>
</head>
<body>
<div class="container">
    <h2>Submit Consultancy Prescription</h2>

    <?php if ($success): ?>
        <p class="success"><?= htmlspecialchars($success) ?></p>
    <?php endif; ?>
    <?php if ($error): ?>
        <p class="error"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <form method="post" action="">
        <label for="patient_id">Patient ID:</label>
        <input type="text" id="patient_id" name="patient_id" required pattern="[a-zA-Z0-9\-]+" title="Letters, numbers, and dashes only">

        <label for="prescription">Prescription:</label>
        <textarea id="prescription" name="prescription" required></textarea>

        <label for="notes">Additional Notes (optional):</label>
        <textarea id="notes" name="notes"></textarea>

        <input type="submit" value="Submit Prescription">
    </form>

    <a href="doctor_dashboard.php" class="back-btn">Back to Dashboard</a>
    <a href="prescriptions_list.php" class="back-btn" style="margin-left: 10px;">View Your Prescriptions</a>
</div>
</body>
</html>
