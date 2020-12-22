<?php
// set headers to NOT cache the page
header("Cache-Control: no-cache, must-revalidate"); //HTTP 1.1
header("Pragma: no-cache"); //HTTP 1.0
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Date in the past
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="babbackup.txt"');
// get the name of the "transit"" file and download it to the standard babbackup.txt
// in the user's donwload folder
$transit_filename = $_GET['transitfilename'];
readfile($transit_filename);
// delete the file
unlink($transit_filename);
exit;

