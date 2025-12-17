<?php
// doctor_login_process.php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

$host = "localhost";
$user = "root";
$pass = "sathwik@1804";
$dbname = "ehr_system";

$conn = new mysqli($host, $user, $pass, $dbname);
if ($conn->connect_error) die("DB error: " . $conn->connect_error);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $identifier = trim($_POST['identifier'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($identifier === '' || $password === '') die("Enter both ID/email and password.");

    $sql = "SELECT * FROM doctors WHERE doctor_id = ? OR email = ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) die("Prepare error: " . $conn->error);

    $stmt->bind_param("ss", $identifier, $identifier);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        if (password_verify($password, $user['password'])) {
            $_SESSION['doctor_internal_id'] = $user['id'];         // Optional internal id
            $_SESSION['doctor_id'] = $user['doctor_id'];           // ✅ Required for dashboard auth check
            $_SESSION['doctor_name'] = $user['full_name'];         // Optional display

            header("Location: doctor_dashboard.php");
            exit();
        } else {
            die("Incorrect password.");
        }
    } else {
        die("User not found.");
    }

    $stmt->close();
} else {
    header("Location: doctor_login.php");
    exit();
}

$conn->close();
?>
