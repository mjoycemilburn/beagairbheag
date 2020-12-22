<?php

# Connect to the Parish Councils database

$con = mysqli_connect('localhost','root','chickpeas','qfgavcxt_beagairbheagdb');
if (!$con) {
    die('Could not connect: ' . mysqli_error($con));
    mysqli_set_charset($con, 'utf-8');
}

?>