<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Depth, User-Agent, X-File-Size, X-Requested-With, If-Modified-Since, X-File-Name, Cache-Control");
header("Access-Control-Max-Age: 18000");

// set headers to NOT cache the page
header("Cache-Control: no-cache, must-revalidate"); //HTTP 1.1
header("Pragma: no-cache"); //HTTP 1.0
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Date in the past

require('../includes/beagairbheag_functions.php');

// Uncomment the following 'require' line if you want to run the helper file in xdebug
// with the POST settings in testcode.php

//require('testcode.php');


// As directed by helper_type :
//
// 'refresh_bab_programme_tables'       -   erase the current content otf the programmes, programme_texts and text_types
//                                          tables and rcreate them with the content of the supplied jsons

$page_title = 'manager_helpers';

date_default_timezone_set('Europe/London');

// connect to the beagairbheag database

connect_to_database();

# Load the utf8 character set to enable accented characters to be stored (they go up otherwise
# as diamond characters with a central question mark). See notes at the top of text_manageent.html

if (!mysqli_set_charset($con, "utf8")) {
    echo "Error loading character set utf8: %s\n", mysqli_error($con);
    require('/home/qfgavcxt/disconnect_beagairbheagdb_old.php');
    exit(1);
}

// get helper-request

$helper_type = $_POST['helper_type'];

//////////////////////////////////////////  refresh_bab_programme_tables ////////////////////////////////////////

if ($helper_type == "refresh_bab_programme_tables") {
    $programmes_json = $_POST['programmes_json'];
    $programme_texts_json = $_POST['programme_texts_json'];
    $text_types_json = $_POST['text_types_json'];

    $programmes = json_decode($programmes_json, true); //returns an associative array
    $programme_texts = json_decode($programme_texts_json, true); //returns an associative array
    $text_types = json_decode($text_types_json, true); //returns an associative array
    $result = sql_result_for_location('START TRANSACTION', 0);

    $sql = "DELETE FROM programmes";
    $result = sql_result_for_location($sql, 1);
    $sql = "DELETE FROM programme_texts";
    $result = sql_result_for_location($sql, 2);
    $sql = "DELETE FROM text_types";
    $result = sql_result_for_location($sql, 3);

    for ($i = 0; $i < count($programmes); $i ++) {
        $series_num = $programmes[$i]["seriesnum"];
        $episode_nam = $programmes[$i]["episodenam"];
        $firston_datestring = $programmes[$i]["firstondate"];
        $finish_time = $programmes[$i]["finishtime"];
        $learner_of_the_week= $programmes[$i]["learneroftheweek"];
        $bbc_programme_url = $programmes[$i]["bbcprogrammeurl"];
        $bbc_download_filename = $programmes[$i]["bbcdownloadfilename"];
        $splash_screen_filename = $programmes[$i]["splashscreenfilename"];
        $splash_screen_title = $programmes[$i]["splashscreentitle"];

        $sql = "INSERT INTO programmes (
                series_num,
                episode_nam,
                firston_datestring,
                finish_time,
                learner_of_the_week,
                bbc_programme_url,
                bbc_download_filename,
                splash_screen_filename,
                splash_screen_title
                )
            VALUES (
                '$series_num',
                '$episode_nam',
                '$firston_datestring',
                '$finish_time',
                '$learner_of_the_week',
                '$bbc_programme_url',
                '$bbc_download_filename',
                '$splash_screen_filename',
                '$splash_screen_title'
                );";
                echo $sql;

        $result = sql_result_for_location($sql, 4);
    }

    for ($i = 0; $i < count($programme_texts); $i ++) {
        $series_num = $programme_texts[$i]["seriesnum"];
        $episode_nam = $programme_texts[$i]["episodenam"];
        $start_time_in_programme = $programme_texts[$i]["starttimeinprogramme"];
        $finish_time_in_programme = $programme_texts[$i]["finishtimeinprogramme"];
        $text_title= $programme_texts[$i]["texttitle"];
        $text_url = $programme_texts[$i]["texturl"];
        $text_type = $programme_texts[$i]["texttype"];

        $sql = "INSERT INTO programme_texts (
                series_num,
                episode_nam,
                start_time_in_programme,
                finish_time_in_programme,
                text_title,
                text_url,
                text_type
                )
            VALUES (
                '$series_num',
                '$episode_nam',
                '$start_time_in_programme',
                '$finish_time_in_programme',
                '$text_title',
                '$text_url',
                '$text_type');";

        $result = sql_result_for_location($sql, 5);
    }
    
    for ($i = 0; $i < count($text_types); $i ++) {

        $text_type = $text_types[$i]["texttype"];
        $text_color = $text_types[$i]["textcolor"];
        $text_header = $text_types[$i]["textheader"];

        $sql = "INSERT INTO text_types (
                text_type,
                text_color,
                text_header)
            VALUES (
                '$text_type',
                '$text_color',
                '$text_header');";

        $result = sql_result_for_location($sql, 6);
    }

        $result = sql_result_for_location('COMMIT', 7);

        echo ("success");

}

disconnect_from_database();
