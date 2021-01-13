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
// 'build_programme_picklists'          -   return code to display the series and epsiode picklists
//                                          and initialise these with given series_num and episode_nam
//
// 'build_textline'                     -   return code to display the text bars on the textline for
//                                          given series_num and episode_nam
//
// 'get_transcript_source'              -   return the source code for given web-page
//
// 'get_programme_data'                 -   return the filename for given series_num and episode_na
//
// 'backup_data_stores'                 -   increment the backup_count field and use this to generate
//                                          a unique "transit" filename on the server in which to store
//                                          the supplied jsons representing the jotter and notes datastores.
//                                          Echo the  new backup_count value back to babindex.html
//
// 'upload_backup_file'                 -   upload the file supplied by $_FILES to the location specified
//                                          by the 'transitfilename' GET parameter
//
// 'increment_restore_count'            -   add 1 to the database field that counts the number of times
//                                          that bab backup files have been restored - used to generate
//                                          a unique "transit" file for each restore
//
// 'restore_data_stores'                -   return the fileString contents of the backup file supplied
//                                          in $_FILES
//
// 'increment_download_count'           -   add 1 to the database field that counts the number of times
//                                          that bab has been downloaded
//
// 'get_system_data'                    -   get the version number of the latest releases of bab

$page_title = 'player_helpers';

date_default_timezone_set('Europe/London');

$helper_type = $_POST['helper_type'];

// connect to the beagairbheag database

connect_to_database();

# Load the utf8 character set to enable accented characters to be stored (they go up otherwise
# as diamond characters with a central question mark). See notes at the top of text_manageent.html

if (!mysqli_set_charset($con, "utf8")) {
    echo "Error loading character set utf8: %s\n", mysqli_error($con);
    disconnect_from_database();
    exit(1);
}

// get helper-request

$helper_type = $_POST['helper_type'];

//////////////////////////////////////////  build_programme_picklists ////////////////////////////////////////

if ($helper_type == "build_programme_picklists") {
    $series_num = $_POST['series_num'];
    $episode_nam = $_POST['episode_nam'];

    // build the picklists in an associative array

    $returns = array();
    $returns['seriespicklist'] = "<select id='seriespicklist' size='1'
                                        onchange = 'changeSeries();'>";

    $sql = "SELECT DISTINCT series_num FROM programmes
            ORDER BY series_num DESC;";

    $result = sql_result_for_location($sql, 1);

    while ($row = mysqli_fetch_array($result)) {
        $series_num_entry = $row['series_num'];
        if ($series_num_entry === $series_num) {
            $returns['seriespicklist'] .= "<option selected value='$series_num_entry'>$series_num_entry</option>";
        } else {
            $returns['seriespicklist'] .= "<option value='$series_num_entry'>$series_num_entry</option>";
        }
    }

    $returns['episodepicklist'] = "<select id='episodepicklist' size='1'
                                        onchange = 'changeEpisode();'>";

    // order by episode_nam doesn't work as "episode 10" sorts before "episode 2". Use "firston_datestring" instead

    $sql = "SELECT DISTINCT episode_nam FROM programmes
            WHERE series_num = '$series_num'
            ORDER BY firston_datestring DESC;";

    $result = sql_result_for_location($sql, 2);

    while ($row = mysqli_fetch_array($result)) {
        $episode_nam_entry = $row['episode_nam'];
        if ($episode_nam_entry === $episode_nam) {
            $returns['episodepicklist'] .= "<option selected value='$episode_nam_entry'>$episode_nam_entry</option>";
        } else {
            $returns['episodepicklist'] .= "<option value='$episode_nam_entry'>$episode_nam_entry</option>";
        }
    }

    $return = json_encode($returns);

    echo $return;
}

//////////////////////////////////////////  build_textline ////////////////////////////////////////

if ($helper_type == "build_textline") {
    $series_num = $_POST['series_num'];
    $episode_nam = $_POST['episode_nam'];
    $scale_factor = $_POST['scale_factor'];
    $scale_correction_for_text = $_POST['scale_correction_for_text'];

    buildTextTypeArrays();

    $sql = "SELECT * FROM programme_texts
            WHERE 
                series_num = '$series_num' AND
                episode_nam = '$episode_nam'
            ORDER BY start_time_in_programme ASC";

    $result = sql_result_for_location($sql, 3);

    $return = '';

    while ($row = mysqli_fetch_array($result)) {
        $start_time_in_programme = $row['start_time_in_programme'];
        $finish_time_in_programme = $row['finish_time_in_programme'];
        $text_title = $row['text_title'];
        $text_url = $row['text_url'];
        $text_type = $row['text_type'];

        $text_key = $series_num . "%" . $episode_nam . "%" . $text_type . "%" . $text_title;

        if ($text_type != null) {
            $text_color = $text_colors[$text_type];
            $text_header = $text_headers[$text_type];
        } else {
            $text_color = "green";
            $text_header  = '';
        }

        $start_time_seconds = $start_time_in_programme % 100;
        $start_time_minutes = ($start_time_in_programme - $start_time_seconds) / 100;
        $start_seconds_in_programme = $start_time_minutes * 60 + $start_time_seconds;

        $finish_time_seconds = $finish_time_in_programme % 100;
        $finish_time_minutes = ($finish_time_in_programme - $finish_time_seconds) / 100;
        $finish_seconds_in_programme = $finish_time_minutes * 60 + $finish_time_seconds;

        $margin_left = ($start_seconds_in_programme * $scale_factor) + $scale_correction_for_text;
        $margin_right = ($finish_seconds_in_programme * $scale_factor) + $scale_correction_for_text;
        $width = $margin_right - $margin_left;
        $title = $text_headers[$text_type] . " : " . $text_title;

        $return .= "<a id='t$start_time_in_programme' 
                        style='position:absolute; margin-left: $margin_left" . "px;'
                        onclick = 'displayTextSource(\"$text_key\", \"$text_url\", \"$text_header\", \"\");'
                        title = '$title'>    
                        <span 
                            style = 'display: inline-block; width: $width" . "px; height: 4vh; background-color: $text_color;'>
                        </span>
                    </a>";
    }

    echo $return;
}

//////////////////////////////////////////  get_transcript_source ////////////////////////////////////////

if ($helper_type == "get_transcript_source") {
    $url = $_POST['url'];

    $raw_contents = file_get_contents($url);
    
    echo $raw_contents;
}

//////////////////////////////////////////  get_programme_data ////////////////////////////////////////

if ($helper_type === "get_programme_data") {
    $series_num = $_POST['series_num'];
    $episode_nam = $_POST['episode_nam'];

    $sql = "SELECT * FROM programmes
            WHERE 
                series_num = '$series_num' AND
                episode_nam = '$episode_nam'";

    $result = sql_result_for_location($sql, 4);
    
    $row = mysqli_fetch_assoc($result);

    // put the data fields into an associative array

    $returns = array();

    $returns['audiofilename'] = $row['bbc_download_filename'];
    $returns['currentFirstOnDate'] = $row['firston_datestring'];
    $returns['currentEpisodeFinishTime'] = $row['finish_time'];
    $returns['currentLearnerOfTheWeek'] = $row['learner_of_the_week'];
    $returns['currentBBCProgrammeUrl'] = $row['bbc_programme_url'];
    $returns['currentSplashScreenFilename'] = $row['splash_screen_filename'];
    $returns['currentSplashScreenTitle'] = $row['splash_screen_title'];

    $return = json_encode($returns);
 
    echo $return;
}

//////////////////////////////////////////  get_text_types_data ////////////////////////////////////////

if ($helper_type === "get_text_types_data") {
    $sql = "SELECT * FROM text_types";

    $result = sql_result_for_location($sql, 5);

    // put the data fields into an associative array

    $text_types_data = array();
    
    $i = 0;
    while ($row = mysqli_fetch_array($result)) {
        $text_types_data[$i]['texttype'] = $row['text_type'];
        $text_types_data[$i]['textcolor'] = $row['text_color'];
        $text_types_data[$i]['textheader'] = $row['text_header'];

        $i++;
    }

    $return = json_encode($text_types_data);

    echo $return;
}

//////////////////////////////////////////  backup_data_stores ////////////////////////////////////////

if ($helper_type === "backup_data_stores") {
    $file_string = $_POST['file_string']; // file_string now contains the jsons. Store these in a file

    // get yourself a unique filenumber to enable us to create a different file for every backup (sp
    // no chance of a user getting the notes and jotter for someone else)

    $result = sql_result_for_location('START TRANSACTION', 6);

    $sql = "SELECT * FROM system
            WHERE system_key = 'bab';";

    $result = sql_result_for_location($sql, 7);
    $row = mysqli_fetch_array($result);
    $backup_count = $row['backup_count'];

    $backup_count++ % 100000;

    $sql = "UPDATE system SET
                backup_count = '$backup_count'
            WHERE 
                system_key = 'bab';";

    $result = sql_result_for_location($sql, 8);

    $result = sql_result_for_location('COMMIT', 9);


    // save $file_string as a file called babBackupFile + backup_count

    $filename = "babbackup" . $backup_count . ".txt";

    $file = fopen($filename, "w") or die("Unable to open file!");
    fwrite($file, $file_string);
    fclose($file);

    // echo the backup_count back to babindex.html

    echo $backup_count;
}


//////////////////////////////////////////  upload_backup_file ////////////////////////////////////////

if ($helper_type === "upload_backup_file") {

    // recover the transitfilename parameter and use this to store the incoming backup file

    $transit_filename = $_GET['transitfilename'];

    // store the uploaded file in a unique location on the server and tell the
    // calling routine wht

    move_uploaded_file($_FILES['backupfilename'] ['tmp_name'], $transit_filename);
}

//////////////////////////////////////////  increment_restore_count ////////////////////////////////////////

if ($helper_type === "increment_restore_count") {
    $result = sql_result_for_location('START TRANSACTION', 10);

    $sql = "SELECT * FROM system
            WHERE system_key = 'bab';";

    $result = sql_result_for_location($sql, 11);
    $row = mysqli_fetch_array($result);
    $restore_count = $row['restore_count'];

    $restore_count++ % 100000;

    $sql = "UPDATE system SET
                restore_count = '$restore_count'
            WHERE 
                system_key = 'bab';";

    $result = sql_result_for_location($sql, 12);

    $result = sql_result_for_location('COMMIT', 13);

    echo $restore_count;
}


//////////////////////////////////////////  restore_data_stores ////////////////////////////////////////

if ($helper_type === "restore_data_stores") {

    // recover the unique transitfilename that tells you where to find the unique "transit" file allocated
    // to this restore session

    $transit_filename = $_POST['transitfilename'];

    echo file_get_contents($transit_filename);

    // and finally, delete the transit file

    unlink($transit_filename);
}
//////////////////////////////////////////  increment_view_count ////////////////////////////////////////

if ($helper_type === "increment_view_count") {
    $result = sql_result_for_location('START TRANSACTION', 14);

    $sql = "SELECT * FROM system
            WHERE system_key = 'bab';";

    $result = sql_result_for_location($sql, 15);
    $row = mysqli_fetch_array($result);
    $view_count = $row['view_count'];

    $view_count++ % 100000;

    $sql = "UPDATE system SET
                view_count = '$view_count'
            WHERE 
                system_key = 'bab';";

    $result = sql_result_for_location($sql, 16);

    $result = sql_result_for_location('COMMIT', 17);

    echo $download_count;
}

//////////////////////////////////////////  increment_download_count ////////////////////////////////////////

if ($helper_type === "increment_download_count") {
    $result = sql_result_for_location('START TRANSACTION', 18);

    $sql = "SELECT * FROM system
            WHERE system_key = 'bab';";

    $result = sql_result_for_location($sql, 19);
    $row = mysqli_fetch_array($result);
    $download_count = $row['download_count'];

    $download_count++ % 100000;

    $sql = "UPDATE system SET
                download_count = '$download_count'
            WHERE 
                system_key = 'bab';";

    $result = sql_result_for_location($sql, 20);

    $result = sql_result_for_location('COMMIT', 21);

    echo $download_count;
}

//////////////////////////////////////////  get_system_data ////////////////////////////////////////

if ($helper_type === "get_system_data") {
    $sql = "SELECT * FROM system
            WHERE system_key = 'bab';";

    $result = sql_result_for_location($sql, 22);
    $row = mysqli_fetch_array($result);

    $version_number = $row['version_number'];

    // this is a convenient place to serve the Companion with the latest "cryptography","about" and "hints" text.
    // alternatives - scripts, local code in babindex etc all suffer fro caching problems of one sort or another.

    $cryptography = "
            <p style='text-align: center;'>
            <strong>Gàidhlig and Cryptography</strong><br><br>
            The entertaining and thought-provoking novels of Neal Stephenson make frequent reference to the isles of
            Qwhglm (pronounced 'Taghum', with stress on the second syllable), mythically located off the west coast
            of Scotland. The inhabitants, naturally, speak Qwghlmian, an impenetrable language employing just 16
            consonant and no vowels. Qwghlmian is thus ideally suited to cryptographic applications. Hmm. Very funny,
            I'm sure.
        </p>";

    $about = '
            <h4 style = "text-align: center; margin-top: 1vh;"> About the Companion</h4>  
            <ul style="list-style: outside; padding: 1vh 2vw auto 2vw;">  
                <li style="display: list-item;">  
                    The Companion provides a convenient way of \'curating\' podcasts downloaded from the BBC\'s BeagAirBheag  
                    website. An audio player equipped with control buttons tailored specifically to the requirements  
                    of Gàidhlig students presents a selected podcast alongside transcripts of key sections.The transcripts are  
                    likewise drawn from BBC pages and re-formatted dynamically to display their key content. 
                    <br><br>  
                </li>  
                <li style="display: list-item;">  
                    Users of the Companion are able to place notes at points in the podcast that are giving difficulty. A range of  
                    useful tools is provided to assist comprehension.  
                    <br><br>  
                </li>  
                <li style="display: list-item;">  
                    In the absence of conversation with a native speaker, listening to BeagAirBheag brodcasts is undoubtedly one of  
                    the best ways of developing Gàidhlig skills. It is hoped that the Companion will provide serious students with a  
                    useful framework for studying programmes in detail.  
                    <br><br>  
                </li>  
                <li style="display: list-item;">  
                    The work of the BeagairBheag team in developing these excellent programmes is gratefully acknowledged.  
                </li>  
            </ul>';

    $hints = '
            <h4 style = "text-align: center; margin-top: 1vh;" > Helpful Suggestions</h4>  
            <ul style="list-style: outside; padding: 1vh 2vw auto 2vw;">  
                <li style="display: list-item;">  
                    It\'s very useful to keep a few notes on common phrases and problem words. Every BaB user has
                    access to a personal \'Jotter\', opened by a single click on the fugitive \'J\' button at  
                    the top left of the BaB display. Use this as you would a \'notepad\' file - cut and paste works and  
                    the jotter is searchable with ctrl-F.  
                    <br><br>  
                </li> 
                <li style="display: list-item;">  
                    When you are reading Gaelic text in the central panel there may be the odd word that you would like to  
                    look up in a dictionary. In such a case, the Companion can save you much time and effort. Just  
                    double-click on the word and a window will open showing its entry in the currently-selected on-line  
                    Gaelic dictionary (both the Am Faclair Beag and the BBC dictionaries are offered).  
                    <br><br>  
                </li>  
                <li style="display: list-item;">  
                    Sometime it\'s helpful to rewind a section of the audio rather further than the five seconds provided by
                    the ? button. In this case you might find it easier to use the mouse scroll wheel to rewind or advance the
                    play-position directly.
                    <br><br>  
                </li>  
                <li style="display: list-item;">  
                    From time to time you may wish to remind yourself about a word or expression you recall  
                    reading in one of the Beag air Bheag texts or recording in one of your notes. But which
                    text or note, exactly? You may find the Companion\'s \'Text Search\'" facility  
                    useful here. This lurks under the fugitive \'S\' button revealed by mousing over the top  
                    left of the screen. Enter the word you\'re looking for and then select from the list of
                    suggestions returned.  
                    <br><br>  
                </li>  
                <li style="display: list-item;">  
                    Back in 2006, Colin Mark, author of the celebrated Gaelic-English dictionary,  
                    ran a series of Translation Master Classes on the ForamNaGàidhlig website. These  
                    have been recovered from archive, consolidated and reformatted. They can be found  
                    under the fugitive \'T\' button on the top righthand side of the screen. They provide  
                    an enjoyable and instructive resource. Enjoy!  
                    <br><br>  
                </li>  
            </ul>';

    // put this lot in a json and echo it

    $returns = array();

    $returns['versionnumber'] = $version_number;
    $returns['cryptography'] = $cryptography;
    $returns['about'] = $about;
    $returns['hints'] = $hints;

    $return = json_encode($returns);

    echo $return;
}

disconnect_from_database();
