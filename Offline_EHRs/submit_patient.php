<?php
$servername = "localhost";
$username = "root";
$password = "sathwik@1804";
$dbname = "ehr_system";

// Connect to MySQL
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if required fields exist
if (
    isset($_POST['patient_id'], $_POST['reg_date'], $_POST['name'], $_POST['age'],
          $_POST['gender'], $_POST['disease'], $_POST['prescription'])
) {
    // Sanitize and collect form data
    $patient_id   = $conn->real_escape_string($_POST['patient_id']);
    $reg_date     = $_POST['reg_date'];
    $name         = $_POST['name'];
    $age          = (int)$_POST['age'];
    $gender       = $_POST['gender'];
    $disease      = $_POST['disease'];
    $prescription = $_POST['prescription'];

    // Prepare SQL insert (excluding password now)
    $stmt = $conn->prepare(
        "INSERT INTO patients (patient_id, name, age, gender, disease, prescription, registration_date)
         VALUES (?, ?, ?, ?, ?, ?, ?)"
    );

    if (!$stmt) {
        die("Prepare failed: " . $conn->error);
    }

    // Bind parameters
    $stmt->bind_param("ssissss", $patient_id, $name, $age, $gender, $disease, $prescription, $reg_date);

    // Execute and handle result
    if ($stmt->execute()) {
        echo "<p>New patient record created successfully!</p>";
        echo "<script>
                setTimeout(function() {
                    window.location.href = 'dashboard.php';
                }, 2000);
              </script>";
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
} else {
    echo "<p>Error: Missing required form fields.</p>";
}

$conn->close();
?>
