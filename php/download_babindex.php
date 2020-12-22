<?php
// set headers to NOT cache the page
header("Cache-Control: no-cache, must-revalidate"); //HTTP 1.1
header("Pragma: no-cache"); //HTTP 1.0
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Date in the past
header('Content-Type: text/html; charset=UTF-8');
header("Content-Transfer-Encoding: Binary");
header('Content-Disposition: attachment; filename="babindex.html"');
readfile('https://ngatesystems.com/beagairbheag/babindex.html');
exit;