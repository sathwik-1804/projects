<?php
$servername = "localhost";
$username = "root";
$password = "sathwik@1804";
$dbname = "ehr_system";

// Connect to MySQL
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Retrieve form data safely
    $patient_id   = $_POST['patient_id'];
    $name         = $_POST['name'];
    $age          = $_POST['age'];
    $gender       = $_POST['gender'];
    $disease      = $_POST['disease'];
    $prescription = $_POST['prescription'];

    // Use prepared statement to update
    $stmt = $conn->prepare("UPDATE patients SET name=?, age=?, gender=?, disease=?, prescription=? WHERE patient_id=?");
    if (!$stmt) {
        die("Prepare failed: " . $conn->error);
    }

    $stmt->bind_param("sissss", $name, $age, $gender, $disease, $prescription, $patient_id);

    if ($stmt->execute()) {
        echo "<p>Patient record updated successfully!</p>";
        echo "<script>
                setTimeout(function() {
                    window.location.href = 'view_patients.php';
                }, 2000);
              </script>";
    } else {
        echo "Error updating record: " . $stmt->error;
    }

    $stmt->close();
}

$conn->close();
?>
