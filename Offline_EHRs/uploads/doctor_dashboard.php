<?php
session_start();

// Check if doctor is logged in
if (!isset($_SESSION['doctor_id'])) {
    header("Location: login.php");
    exit;
}

$doctor_id = $_SESSION['doctor_id'];

// DB connection
$servername = "localhost";
$username = "root";
$password = "2580";
$dbname = "ehr_system";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch doctor profile
$doctorQuery = "SELECT * FROM doctors WHERE doctor_id = ?";
$doctorStmt = $conn->prepare($doctorQuery);
$doctorStmt->bind_param("s", $doctor_id);
$doctorStmt->execute();
$doctorResult = $doctorStmt->get_result();
$doctor = $doctorResult->fetch_assoc();

// Fetch messages
$query = "SELECT message, created_at FROM doctor_messages WHERE doctor_id = ? ORDER BY created_at DESC";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $doctor_id);
$stmt->execute();
$result = $stmt->get_result();

// Initialize messages
$success = '';
$error = '';

// Handle prescription submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit_prescription'])) {
    $patient_id = $_POST['patient_id'];
    $prescription = $_POST['prescription'];
    $notes = $_POST['notes'];

    if (!empty($patient_id) && !empty($prescription)) {
        $insertQuery = "INSERT INTO prescriptions (doctor_id, patient_id, prescription, notes, created_at) VALUES (?, ?, ?, ?, NOW())";
        $insertStmt = $conn->prepare($insertQuery);
        $insertStmt->bind_param("ssss", $doctor_id, $patient_id, $prescription, $notes);

        if ($insertStmt->execute()) {
            $success = "Prescription submitted successfully.";
        } else {
            $error = "Failed to submit prescription.";
        }

        $insertStmt->close();
    } else {
        $error = "Patient ID and Prescription are required.";
    }
}

// Fetch submitted prescriptions
$prescQuery = "SELECT * FROM prescriptions WHERE doctor_id = ? ORDER BY created_at DESC";
$prescStmt = $conn->prepare($prescQuery);
$prescStmt->bind_param("s", $doctor_id);
$prescStmt->execute();
$presc_result = $prescStmt->get_result();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Doctor Dashboard</title>
    <style>
        body { font-family: Arial; padding: 40px; background: #f4f4f4; }
        .message, .profile, .form-section, .prescription { background: white; margin: 10px auto; padding: 20px; width: 60%; border-radius: 8px; box-shadow: 0 2px 6px rgba(0,0,0,0.1); }
        .message p, .profile p, .prescription p { margin: 0 0 10px; }
        .message small, .prescription small { color: #666; }
        h2, h3 { color: #004080; }
        a { color: #004080; text-decoration: none; }
        a:hover { text-decoration: underline; }
        .success { color: green; }
        .error { color: red; }
        input, textarea {
            width: 100%;
            padding: 8px;
            margin-top: 6px;
            margin-bottom: 12px;
            border-radius: 4px;
            border: 1px solid #ccc;
        }
        input[type="submit"] {
            background-color: #004080;
            color: white;
            border: none;
            cursor: pointer;
        }
        input[type="submit"]:hover {
            background-color: #003060;
        }
    </style>
</head>
<body>

    <h2>Doctor Dashboard</h2>

    <h3>Your Profile</h3>
    <div class="profile">
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
    </div>

    <h3>Patient Access Messages</h3>
    <?php if ($result->num_rows > 0): ?>
        <?php while ($row = $result->fetch_assoc()): ?>
            <div class="message">
                <?php 
                $allowed_tags = '<a><br>';
                $safe_message = strip_tags($row['message'], $allowed_tags);
                echo $safe_message;
                ?>
                <br>
                <small>Received on: <?= htmlspecialchars($row['created_at']) ?></small>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <p>No messages yet.</p>
    <?php endif; ?>

    <!-- Add Prescription Form -->
    <section class="form-section">
        <h3>Add Consultancy Prescription</h3>

        <?php if (!empty($success)): ?>
            <p class="success"><?= htmlspecialchars($success) ?></p>
        <?php endif; ?>

        <?php if (!empty($error)): ?>
            <p class="error"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>

        <form method="post" action="">
            <label for="patient_id">Patient ID:</label>
            <input type="text" id="patient_id" name="patient_id" required>

            <label for="prescription">Prescription:</label>
            <textarea id="prescription" name="prescription" rows="6" required></textarea>

            <label for="notes">Additional Notes (optional):</label>
            <textarea id="notes" name="notes" rows="4"></textarea>

            <input type="submit" name="submit_prescription" value="Submit Prescription">
        </form>
    </section>

    <!-- Display Doctor's Prescriptions -->
    <section class="prescriptions">
        <h3>Your Submitted Prescriptions</h3>
        <?php if ($presc_result->num_rows > 0): ?>
            <?php while ($presc = $presc_result->fetch_assoc()): ?>
                <div class="prescription">
                    <p><strong>Patient ID:</strong> <?= htmlspecialchars($presc['patient_id']) ?></p>
                    <p><strong>Prescription:</strong> <?= nl2br(htmlspecialchars($presc['prescription'])) ?></p>
                    <?php if (!empty($presc['notes'])): ?>
                        <p><strong>Notes:</strong> <?= nl2br(htmlspecialchars($presc['notes'])) ?></p>
                    <?php endif; ?>
                    <small>Submitted on: <?= htmlspecialchars($presc['created_at']) ?></small>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p>No prescriptions submitted yet.</p>
        <?php endif; ?>
    </section>

</body>
</html>
