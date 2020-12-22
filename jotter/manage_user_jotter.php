<?php

# Retrieve/Update a user's entry in the user_jotters table

$page_title = 'manage_user_jotter';

date_default_timezone_set('Europe/London');

# set headers to NOT cache the page
header("Cache-Control: no-cache, must-revalidate"); //HTTP 1.1
header("Pragma: no-cache"); //HTTP 1.0
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Date in the past

# Connect to the database

require ('/home/qfgavcxt/connect_beagairbheagdb_old.php');

# Load the utf8 character set to enable accented characters to be stored (they go up otherwise
# as diamond characters with a central question mark). See notes at the top of text_manageent.html

if (!mysqli_set_charset($con, "utf8")) {
    echo "Error loading character set utf8: %s\n", mysqli_error($con);
    require ('/home/qfgavcxt/disconnect_beagairbheagdb_old.php');
    exit(1);
}

# This routine is hard to debug. For Select you can use the fall-back GET fields below
# to run the URL directly, but for Update, if you have a long Jotter value to test you 
# need POST. In this case, use logging (eg error_log("upload_target " . $upload_target);
# to get debugging messages in abyss_web_server/fastcgi.log

if (isset($_GET['action'])) {
    $action = $_GET['action'];
} else {
    $action = $_POST['action'];
}
if (isset($_GET['user_id'])) {
    $user_id = $_GET['user_id'];
} else {
    $user_id = $_POST['user_id'];
}

if ($action == "S") {

    $sql = "SELECT         
		user_jotter as user_jotter
            FROM user_jotters WHERE
                user_id = '$user_id'";

    $result = mysqli_query($con, $sql);

    if ($result) {

        while ($row = mysqli_fetch_array($result)) {
            $user_jotter = $row['user_jotter'];
            echo $user_jotter;
        }
    } else {
        echo "database_error";
        require ('/home/qfgavcxt/disconnect_beagairbheagdb_old.php');
        exit(1);
    }
}

if ($action == "U") {

    if (isset($_GET['user_jotter'])) {
        $user_jotter = $_GET['user_jotter'];
    } else {
        $user_jotter = $_POST['user_jotter'];
    }

# sanitise quote marks etc

    $user_jotter = mysqli_real_escape_string($con, $user_jotter);

    $sql = "UPDATE user_jotters
                    SET user_jotter = '$user_jotter'
                    WHERE 
                        user_id = '$user_id'";


    $result = mysqli_query($con, $sql);

    if (!$result) {
        echo "database_error";
        require ('/home/qfgavcxt/disconnect_beagairbheagdb_old.php');
        exit(1);
    }
}

# and now disconnect and return to beagairbheag home page

require ('/home/qfgavcxt/disconnect_beagairbheagdb_old.php');
exit(0);

