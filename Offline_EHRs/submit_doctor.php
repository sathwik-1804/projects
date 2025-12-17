<?php
$mysqli = new mysqli("localhost", "root", "sathwik@1804", "ehr_system");

if ($mysqli->connect_error) {
  die("Connection failed: " . $mysqli->connect_error);
}

$name = $_POST['name'];
$specialization = $_POST['specialization'];
$experience = $_POST['experience'];
$contact = $_POST['contact'];
$email = $_POST['email'];

$stmt = $mysqli->prepare("INSERT INTO doctors (name, specialization, experience, contact, email) VALUES (?, ?, ?, ?, ?)");
$stmt->bind_param("ssiss", $name, $specialization, $experience, $contact, $email);
$stmt->execute();

$stmt->close();
$mysqli->close();

header("Location: view_doctors.php");
exit;
?>
