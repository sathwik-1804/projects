<?php
if (!isset($_GET['file'])) {
    die("Invalid request: file missing.");
}

$file = basename($_GET['file']); // sanitize input

$filepath = __DIR__ . "/uploads/" . $file;

if (!file_exists($filepath)) {
    die("File not found.");
}

$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mime_type = finfo_file($finfo, $filepath);
finfo_close($finfo);

header("Content-Type: $mime_type");
header("Content-Disposition: inline; filename=\"" . addcslashes($file, '"\\') . "\"");
header('Content-Length: ' . filesize($filepath));

readfile($filepath);
exit;
?>
