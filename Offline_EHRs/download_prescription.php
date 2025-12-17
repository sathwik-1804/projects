<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $doctor = $_POST['doctor_name'] ?? 'Unknown Doctor';
    $prescription = $_POST['prescription'] ?? 'N/A';
    $notes = $_POST['notes'] ?? 'None';
    $date = $_POST['date'] ?? date("Y-m-d");

    // Prepare content
    $content = "Doctor: $doctor\n";
    $content .= "Date: $date\n\n";
    $content .= "Prescription:\n$prescription\n\n";
    if (!empty($notes)) {
        $content .= "Notes:\n$notes\n";
    }

    // Headers to force download
    header('Content-Type: text/plain');
    header('Content-Disposition: attachment; filename="prescription_' . date("Ymd_His") . '.txt"');
    header('Content-Length: ' . strlen($content));
    echo $content;
    exit();
} else {
    echo "Invalid request.";
}
