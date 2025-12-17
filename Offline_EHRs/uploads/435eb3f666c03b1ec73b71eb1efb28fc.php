<?php
$servername = "localhost";
$username = "root";
$password = "2580";
$dbname = "ehr_system";

$conn = new mysqli($servername, $username, $password, $dbname);

$id = $_POST['id'];
$name = $_POST['name'];
$age = $_POST['age'];
$gender = $_POST['gender'];
$disease = $_POST['disease'];
$prescription = $_POST['prescription'];

$sql = "UPDATE patients SET 
    name='$name', age='$age', gender='$gender', 
    disease='$disease', prescription='$prescription' 
    WHERE id=$id";

if ($conn->query($sql) === TRUE) {
    echo "Record updated successfully. <a href='view_patients.php'>Go back</a>";
} else {
    echo "Error updating record: " . $conn->error;
}

$conn->close();
?>
