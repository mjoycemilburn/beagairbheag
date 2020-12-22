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
// 'build_programme_selector'           -   return code to display the select element to display
//                                          the save page for a programme
//
// 'build_programme_update_div'         -   return code to display a save display for given programme
//
// 'get_bbc_programme_url_address'   -   return the url for the bbc index page for a programme
//
// 'insert_programme'                   -   create a new programme
//
// 'save_programme'                     -   save an existing programme
//
// 'delete_programme'                   -   delete an existing text_type
//
// 'build_text_type_selector'           -   return code to display the select element to display
//                                          the save page for a text_type
//
// 'build_text_type_update_div'         -   return code to display a save display for given text_type
//
// 'get_text_type_parameters'           -   return the url for the bbc index page for a text_type and its
//                                          text_header
//
// 'insert_text_type'                   -   create a new text_type
//
// 'save_text_type'                     -   save an existing text_type
//
// 'delete_text_type'                   -   delete an existing text_type
//
// 'build_programme_text_update_divs'   -   return code to display the save/delete programme_text divs for given
//                                          seriesNum, episodeNam and textType  (there may be more than
//                                          one as they're keyed on startTimeInProgramme)                      



$page_title = 'manager_helpers';

date_default_timezone_set('Europe/London');

// check logged_in

session_start();
$helper_type = $_POST['helper_type'];
if ($helper_type === null) {
    echo "%not_logged_in%";
    exit(0);
}

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

//////////////////////////////////////////  build_programme_selector ////////////////////////////////////////

if ($helper_type == "build_programme_selector") {
    $sql = "SELECT DISTINCT series_num FROM programmes";

    $result = sql_result_for_location($sql, 1);

    $return = "
        Ser&nbsp;
        <select id='seriespicklist'
            onchange = 'setEpisodesPickListForSeries(seriespicklist.options[seriespicklist .selectedIndex].value);'>
            <option selected value = 'new'>new</option>";

    while ($row = mysqli_fetch_array($result)) {
        $series_num = $row['series_num'];
        $return .= "<option value = '$series_num'>$series_num</option>";
    }

    $return .= "</select>";

    $return .= "
    <div id='episodespicklistdiv' style='display: inline;'>
        Epi&nbsp;
        <select id='episodespicklist'>
            <option selected value = 'new'>new</option>
        </select>
    </div>";

    echo $return;
}

//////////////////////////////////////////  build_episode_selector ////////////////////////////////////////

if ($helper_type == "build_episode_selector") {
    $series_num = $_POST['series_num'];

    $sql = "SELECT episode_nam FROM programmes
            WHERE series_num = '$series_num';";

    $result = sql_result_for_location($sql, 2);

    $return = "
        <select id = 'episodespicklist'
            onchange = 'setProgrammeMaintenanceDivs(
                seriespicklist.options[seriespicklist.selectedIndex].value,
                episodespicklist.options[episodespicklist.selectedIndex].value);'>
            <option selected value = 'new'>new</option>";

    while ($row = mysqli_fetch_array($result)) {
        $episode_nam = $row['episode_nam'];
        $return .= "<option value = '$episode_nam'>$episode_nam</option>";
    }

    $return .= "</select>";

    echo $return;
}


//////////////////////////////////////////  build_programme_update_div ////////////////////////////////////////

if ($helper_type == "build_programme_update_div") {
    $series_num = $_POST['series_num'];
    $episode_nam = $_POST['episode_nam'];

    $sql = "SELECT * FROM programmes
            WHERE 
                series_num = '$series_num' AND
                episode_nam = '$episode_nam';";

    $result = sql_result_for_location($sql, 3);
    $row = mysqli_fetch_array($result);

    $firston_datestring = $row['firston_datestring'];
    $finish_time = $row['finish_time'];
    $learner_of_the_week = $row['learner_of_the_week'];
    $bbc_programme_url = $row['bbc_programme_url'];
    $bbc_download_filename = $row['bbc_download_filename'];


    echo "
        <label for='eseriesnum'>&nbsp;&nbsp;Ser&nbsp;</label>
        <input id='eseriesnum' type='text' maxlength='2' size='2' name='eseriesnum'
             value='$series_num' placeholder='$series_num' autocomplete='off'
             title='Enter the series number'>
        <label for='eepisodenam'>&nbsp;&nbsp;Epi&nbsp;</label>
        <input id='eepisodenam' type='text' maxlength='100' size='5' name='eepisodenam'
             value='$episode_nam' placeholder='$episode_nam' autocomplete='off'
             title='Enter the name of the Episode - eg Episode 4'>
        <label for='efirstondatestring'>&nbsp;&nbsp;First Broadcast&nbsp;</label>
        <input id='efirstondatestring' type='text' maxlength='10' size='8' name='efirstondatestring'
             value='$firston_datestring' placeholder='$firston_datestring' autocomplete='off'
             title='Enter date on which the programme was first broadcast'
             onmousedown = 'applyDatepicker(\"efirstondatestring\");'>
        <label for='efinishtime'>&nbsp;&nbsp;Finish&nbsp;</label>
        <input id='efinishtime' type='text' maxlength='4' size='4' name='efinishtime'
             value='$finish_time' placeholder='$finish_time' autocomplete='off'
             title='Enter the finish time of the programme as mmss'>
         <label for='elearneroftheweek'>&nbsp;&nbsp;LotW&nbsp;</label>
         <input id='elearneroftheweek' type='text' maxlength='30' size='10' name='elearneroftheweek'
             value='$learner_of_the_week' placeholder='$learner_of_the_week' autocomplete='off'
             title='Enter the name of the LotW'>
        <label for='ebbcprogrammeurl'>&nbsp;&nbsp;Url&nbsp;</label>
        <input id='ebbcprogrammeurl' type='text' maxlength='100' size='10' name='ebbcprogrammeurl'
             value='$bbc_programme_url' placeholder='$bbc_programme_url' autocomplete='off'
             title='Enter the url for the BBC web page for this programme'>
        <label for='ebbcdownloadfilename'>&nbsp;&nbsp;Filename&nbsp;</label>
        <input id='ebbcdownloadfilename' type='text' maxlength='100' size='10' name='ebbcdownloadfilename'
             value='$bbc_download_filename' placeholder='$bbc_download_filename' autocomplete='off'
             title='Enter the filename for the BBC download (without the extension) for this programme'>
        <button type='button' style='margin-left: 1vw;'
            title='Save this programme'
            onclick='saveProgramme(\"$series_num\", \"$episode_nam\");'>Save
        </button>
        <button type='button' style='margin-left: 1vw;'
            title='Delete this programme'
            onclick='deleteProgramme(\"$series_num\", \"$episode_nam\");'>Del
        </button>
        <button id = 'eprogrammevisitbutton' type='button' style='margin-left: 1vw;'
            title='Visit the BBC page for this epiode'>Visit
        </button>";
}

//////////////////////////////////////////  get_bbc_programme_url_address ////////////////////////////////////////

if ($helper_type == "get_bbc_programme_url_address") {
    $series_num = $_POST['series_num'];
    $episode_nam = $_POST['episode_nam'];

    $sql = "SELECT * FROM programmes
            WHERE 
                series_num = '$series_num' AND
                episode_nam = '$episode_nam';";

    $result = sql_result_for_location($sql, 4);
    $row = mysqli_fetch_array($result);

    echo $row['bbc_programme_url'];
}

//////////////////////////////////////////  insert_programmee ////////////////////////////////////////


if ($helper_type == "insert_programme") {
    $series_num = $_POST['series_num'];
    $episode_nam = $_POST['episode_nam'];
    $firston_datestring = $_POST['firston_datestring'];
    $finish_time = $_POST['finish_time'];
    $learner_of_the_week = $_POST['learner_of_the_week'];
    $bbc_programme_url = $_POST['bbc_programme_url'];
    $bbc_download_filename = $_POST['bbc_download_filename'];
    echo "epi $episode_nam";

    // check that the progamme doesn't already exist

    $sql = "SELECT * FROM programmes
            WHERE
                series_num = '$series_num' AND
                episode_nam = '$episode_nam';";

    $result = sql_result_for_location($sql, 5);

    if (mysqli_num_rows($result) >= 1) {
        echo "Oops - insert %failed% - a programme already exists for these keys";
        exit(0);
    }

    $sql = "INSERT INTO programmes (
                series_num,
                episode_nam,
                firston_datestring,
                finish_time,
                learner_of_the_week,
                bbc_programme_url,
                bbc_download_filename)
            VALUES (
                '$series_num',
                '$episode_nam',
                '$firston_datestring',
                '$finish_time',
                '$learner_of_the_week',
                '$bbc_programme_url',
                '$bbc_download_filename');";

    $result = sql_result_for_location($sql, 6);
}

//////////////////////////////////////////  save_programme ////////////////////////////////////////

if ($helper_type == "save_programme") {
    $series_num_new = $_POST['series_num_new'];
    $episode_nam_new = $_POST['episode_nam_new'];
    $series_num_old = $_POST['series_num_old'];
    $episode_nam_old = $_POST['episode_nam_old'];

    $firston_datestring = $_POST['firston_datestring'];
    $finish_time = $_POST['finish_time'];
    $learner_of_the_week = $_POST['learner_of_the_week'];
    $bbc_programme_url = $_POST['bbc_programme_url'];
    $bbc_download_filename = $_POST['bbc_download_filename'];

    // check that the progamme doesn't already exist

    if ($series_num_new != $series_num_old || $episode_nam_new != $episode_nam_old) {
        $sql = "SELECT * FROM programmes
            WHERE
                series_num = '$series_num_new' AND
                episode_nam = '$episode_nam_new';";

        $result = sql_result_for_location($sql, 7);

        if (mysqli_num_rows($result) >= 1) {
            echo "Oops - insert %failed% - a programme already exists for these keys";
            exit(0);
        }
    }

    $sql = "UPDATE programmes SET
                series_num = '$series_num_new',
                episode_nam = '$episode_nam_new',
                firston_datestring  = '$firston_datestring',
                finish_time  = '$finish_time',
                learner_of_the_week  = '$learner_of_the_week',
                bbc_programme_url  = '$bbc_programme_url',
                bbc_download_filename  = '$bbc_download_filename'
            WHERE
                series_num  = '$series_num_old' AND
                episode_nam = '$episode_nam_old';";

    $result = sql_result_for_location($sql, 8);
}

//////////////////////////////////////////  delete_programme ////////////////////////////////////////

if ($helper_type == "delete_programme") {
    $series_num = $_POST['series_num'];
    $episode_nam = $_POST['episode_nam'];


    $sql = "DELETE FROM programmes 
            WHERE
                series_num  = '$series_num' AND
                episode_nam = '$episode_nam';";

    $result = sql_result_for_location($sql, 9);
}

//////////////////////////////////////////  build_text_type_selector ////////////////////////////////////////

if ($helper_type == "build_text_type_selector") {
    $sql = "SELECT * FROM text_types";

    $result = sql_result_for_location($sql, 11);

    $return = "
        <select id='texttypesspicklist'
            onchange = 'setTextTypeMaintenanceDivs(texttypesspicklist.options[texttypesspicklist.selectedIndex].value);'>
            <option selected value = 'new'>new</option>";

    while ($row = mysqli_fetch_array($result)) {
        $text_type = $row['text_type'];
        $return .= "<option value = '$text_type'>$text_type</option>";
    }

    $return .= "</select>";

    echo $return;
}

//////////////////////////////////////////  build_text_type_update_div ////////////////////////////////////////

if ($helper_type == "build_text_type_update_div") {
    $text_type = $_POST['text_type'];

    $sql = "SELECT * FROM text_types
            WHERE text_type = '$text_type';";

    $result = sql_result_for_location($sql, 11);
    $row = mysqli_fetch_array($result);

    $text_color = $row['text_color'];
    $text_title_header = $row['text_title_header'];
    $text_bbc_index_page = $row['text_bbc_index_page'];


    echo "
        <label for = 'etexttype'>&nbsp;&nbsp;Text Type :&nbsp;</label>
        <input id = 'etexttype'  type='text' maxlength='10' size='8' name = 'etexttype'
            value = '$text_type' placeholder = '$text_type' autocomplete='off' 
            title='Enter a short tag for the text_type - eg blasadbeag'>
        <label for = 'etextcolor'>&nbsp;&nbsp;Text Color :&nbsp;</label>
        <input id = 'etextcolor'  type='text' maxlength='10' size='8' name = 'etextcolor'
            value = '$text_color' placeholder = '$text_color' autocomplete='off' 
            title='Enter a short tag for the text_color - eg fuchsia'>
        <label for = 'etexttitleheader'>&nbsp;&nbsp;Header :&nbsp;</label>
        <input id = 'etexttitleheader'  type='text' maxlength='30' size='15' name = 'etexttitleheader'
            value = '$text_title_header' placeholder = '$text_title_header' autocomplete='off' 
            title='Enter a header to precede text titles - eg Blasad Beag'>
        <label for = 'etextbbcindexpage'>&nbsp;&nbsp;Index Page :&nbsp;</label>
        <input id = 'etextbbcindexpage'  type='text' maxlength='100' size='15' name = 'etextbbcindexpage'
            value = '$text_bbc_index_page' placeholder = '$text_bbc_index_page' autocomplete='off' 
            title='Enter the url for the BBC's index pge for this text type'>
        <button type='button' style='margin-left: 1vw;'
            title='Save this text type'
            onclick='saveTextType(\"$text_type\");'>Save
        </button>
        <button type='button' style='margin-left: 1vw;'
            title='Delete this text type'
            onclick='deleteTextType(\"$text_type\");'>Delete
        </button>
        <button id = 'etextvisitbutton' type='button' style='margin-left: 1vw;'
            title='View the BBC index page this text type'>Index
        </button>";
}

//////////////////////////////////////////  get_text_type_parameters ////////////////////////////////////////

if ($helper_type == "get_text_type_parameters") {
    $text_type = $_POST['text_type'];

    $sql = "SELECT * FROM text_types
            WHERE text_type = '$text_type';";

    $result = sql_result_for_location($sql, 12);
    $row = mysqli_fetch_array($result);

    // put the data fields into an associative array

    $returns = array();
    $returns['textTitleHeader'] = prepareStringforXMLandJSONParse($row['text_title_header']);
    $returns['textBbcIndexPage'] = prepareStringforXMLandJSONParse($row['text_bbc_index_page']);

    $return = json_encode($returns);
    header("Content-type: text/xml");
    echo "<?xml version = '1.0' encoding = 'UTF-8'
    ?>";
    echo "<returns>$return</returns>";
}

//////////////////////////////////////////  insert_text_type ////////////////////////////////////////


if ($helper_type == "insert_text_type") {
    $text_type = $_POST['text_type'];
    $text_color = $_POST['text_color'];
    $text_title_header = $_POST['text_title_header'];
    $text_bbc_index_page = $_POST['text_bbc_index_page'];

    // check that the text_type doesn't already exist

    $sql = "SELECT * FROM text_types
            WHERE
                text_type = '$text_type';";

    $result = sql_result_for_location($sql,13);

    if (mysqli_num_rows($result) >= 1) {
        echo "Oops - insert %failed% - a text_type already exists for this value";
        exit(0);
    }

    $sql = "INSERT INTO text_types (
                text_type,
                text_color,
                text_title_header,
                text_bbc_index_page)
            VALUES (
                '$text_type',
                '$text_color',
                '$text_title_header',
                '$text_bbc_index_page');";

    $result = sql_result_for_location($sql, 14);
}

//////////////////////////////////////////  save_text_type ////////////////////////////////////////

if ($helper_type == "save_text_type") {
    $text_type_old = $_POST['text_type_old'];
    $text_type_new = $_POST['text_type_new'];
    $text_color = $_POST['text_color'];
    $text_title_header = $_POST['text_title_header'];
    $text_bbc_index_page = $_POST['text_bbc_index_page'];

    // if the text_type is changing, check that new text_type is unique

    if ($text_type_old != $text_type_new) {
        $sql = "SELECT * FROM text_types
            WHERE
                text_type = '$text_type_new';";

        $result = sql_result_for_location($sql, 15);

        if (mysqli_num_rows($result) >= 1) {
            echo "Oops - save %failed% - a text_type already exists with this value";
            exit(0);
        }
    }

    // OK  - update the database

    $sql = "UPDATE text_types SET
                text_type  = '$text_type_new',
                text_color  = '$text_color',
                text_title_header  = '$text_title_header',
                text_bbc_index_page  = '$text_bbc_index_page'
            WHERE
                text_type = '$text_type_old';";

    $result = sql_result_for_location($sql, 16);
}

//////////////////////////////////////////  delete_text_type ////////////////////////////////////////

if ($helper_type == "delete_text_type") {
    $text_type = $_POST['text_type'];

    $sql = "DELETE FROM text_types WHERE
                text_type = '$text_type';";

    $result = sql_result_for_location($sql, 17);
}

//////////////////////////////////////////  build_programme_text_update_divs ////////////////////////////////////////

if ($helper_type == "build_programme_text_update_divs") {
    $series_num = $_POST['series_num'];
    $episode_nam = $_POST['episode_nam'];
    $text_type = $_POST['text_type'];

    $sql = "SELECT * FROM programme_texts
            WHERE 
                series_num = '$series_num' AND
                episode_nam = '$episode_nam'AND
                text_type = '$text_type';";

    $result = sql_result_for_location($sql, 18);

    $i = 0;
    while ($row = mysqli_fetch_array($result)) {

    $start_time_in_programme = $row['start_time_in_programme'];
    $finish_time_in_programme = $row['finish_time_in_programme'];
    $text_title = $row['text_title'];
    $text_url = $row['text_url'];


    echo "
    <div style='margin-top: 2vh; border: 1px solid black; padding: 1vh;
                display: flex; justify-content: center; padding: 1vh;'>
        <label for='eprogrammetextstarttimeinprogramme$i'>&nbsp;&nbsp;Start Time in Programme :&nbsp;</label>
        <input id='eprogrammetextstarttimeinprogramme$i' type='text' maxlength='4' size='4' name='eprogrammetextstarttimeinprogramme$i'
            value='$start_time_in_programme' placeholder='$start_time_in_programme' autocomplete='off'
            title='Enter the start time for this Text in the programme as mmss'>
        <label for='eprogrammetextfinishtimeinprogramme$i'>&nbsp;&nbsp;Finish time in Programme :&nbsp;</label>
        <input id='eprogrammetextfinishtimeinprogramme$i' type='text' maxlength='4' size='4' name='eprogrammetextfinishtimeinprogramme$i'
            value='$finish_time_in_programme' placeholder='$finish_time_in_programme' autocomplete='off'
            title='Enter the finish time for this Text in the programme as mmss'>
        <label for='eprogrammetexttexttitle$i'>&nbsp;&nbsp;Text Title :&nbsp;</label>
        <input id='eprogrammetexttexttitle$i' type='text' maxlength='30' size='15' name='eprogrammetexttexttitle$i'
            value='$text_title' placeholder='$text_title' autocomplete='off'
            title='Enter a title for the tex - eg Pàirc Hampden'>
        <label for='eprogrammetexttexturl$i'>&nbsp;&nbsp;Url :&nbsp;</label>
        <input id='eprogrammetexttexturl$i' type='text' maxlength='100' size='15' name='eprogrammetexttexturl$i'
            value='$text_url' placeholder='$text_url' autocomplete='off'
            title='Enter the url for the BBC page for this text'>
        <button type='button' style='margin-left: 1vw;'
            title='Save this text type'
            onclick='saveProgrammeText($i);'>Save
        </button>
        <button type='button' style='margin-left: 1vw;'
            title='Delete this text type'
            onclick='deleteProgrammeText($i);'>Delete
        </button>
 </div>";

        $i++;

    }
}

//////////////////////////////////////////  insert_programme_text ////////////////////////////////////////


if ($helper_type == "insert_programme_text") {
    $series_num = $_POST['series_num'];
    $episode_nam = $_POST['episode_nam'];
    $start_time_in_programme = $_POST['start_time_in_programme'];
    $finish_time_in_programme = $_POST['finish_time_in_programme'];
    $text_title = $_POST['text_title'];
    $text_url = $_POST['text_url'];
    $text_type = $_POST['text_type'];

    // check that the text_type doesn't already exist

    $sql = "SELECT * FROM programme_texts
            WHERE
                series_num = '$series_num' AND
                episode_nam = '$episode_nam' AND
                start_time_in_programme = '$start_time_in_programme';";

    $result = sql_result_for_location($sql, 19);

    if (mysqli_num_rows($result) >= 1) {
        echo "Oops - insert %failed% - a programme_text already exists at this start time";
        exit(0);
    }

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

    $result = sql_result_for_location($sql, 20);
}

//////////////////////////////////////////  save_programme_text ////////////////////////////////////////

if ($helper_type == "save_programme_text") {
    $series_num = $_POST['series_num'];
    $episode_nam = $_POST['episode_nam'];
    $start_time_in_programme_new = $_POST['start_time_in_programme_new'];
    $start_time_in_programme_old = $_POST['start_time_in_programme_old'];
    $finish_time_in_programme = $_POST['finish_time_in_programme'];
    $text_title = $_POST['text_title'];
    $text_url = $_POST['text_url'];
    $text_type = $_POST['text_type'];

    // if the start_time_in_programme is changing, check that new start_time_in_programme is unique

    if ($start_time_in_programme_new != $start_time_in_programme_old) {
        $sql = "SELECT * FROM programme_texts
            WHERE
                series_num = '$series_num' AND
                episode_nam = '$episode_nam'AND
                start_time_in_programme = '$start_time_in_programme_new';";

        $result = sql_result_for_location($sql, 21);

        if (mysqli_num_rows($result) >= 1) {
            echo "Oops - save %failed% - a text_type already exists with this value";
            exit(0);
        }
    }

    // OK  - update the database

    $sql = "UPDATE programme_texts SET
                start_time_in_programme  = '$start_time_in_programme_new',
                finish_time_in_programme  = '$finish_time_in_programme',
                text_title  = '$text_title',
                text_url  = '$text_url'
            WHERE
                series_num = '$series_num' AND
                episode_nam = '$episode_nam' AND
                start_time_in_programme = '$start_time_in_programme_old';";

    $result = sql_result_for_location($sql, 22);
}

//////////////////////////////////////////  delete_programme_text ////////////////////////////////////////

if ($helper_type == "delete_programme_text") {
    $series_num = $_POST['series_num'];
    $episode_nam = $_POST['episode_nam'];
    $start_time_in_programme = $_POST['start_time_in_programme'];


    $sql = "DELETE FROM programme_texts WHERE
                series_num = '$series_num' AND
                episode_nam = '$episode_nam' AND
                start_time_in_programme = '$start_time_in_programme';";

    $result = sql_result_for_location($sql, 23);
}


disconnect_from_database();