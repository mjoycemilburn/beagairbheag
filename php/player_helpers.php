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
// 'build_noteline'                     -   return code to display the note icons on the noteline for
//                                          given series_num, episode_nam and user_id
//
// 'build_textline'                     -   return code to display the text bars on the textline for
//                                          given series_num and episode_nam
//
// 'get_transcript_source'              -   return the source code for given web-page
//
// 'build_insert_note_panel'            -   return code to display an insert_note_panel. There are no input parameters
//                                          and php is used here soley to provide consistency with the
//                                          build_edit_note_panel below
//
// 'build_edit_note_panel'              -   return code to display an edit_noet_panel for given note_id for
//                                          given series_num, episode_nam and user_id
//
// 'get_programme_data'                 -   return the filename for given series_num and episode_na
//
// 'backup_data_stores'                 -   return the supplied filestring as a file in downloads
//
// 'upload_backup_file'                 -   upload the file supplied by $_FILES to the location specified
//                                          by the GET parameter in transitfilename
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
                                        onchange = 'changeSeries();'>
                                        <option value='undefined'</option>";

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
                                        onchange = 'changeEpisode();'>
                                     <option value='undefined'</option>";

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

    $returns['seriespicklist'] = prepareStringforXMLandJSONParse($returns['seriespicklist']);
    $returns['episodepicklist'] = prepareStringforXMLandJSONParse($returns['episodepicklist']);


    $return = json_encode($returns);
    header("Content-type: text/xml");
    echo "<?xml version = '1.0' encoding = 'UTF-8'
    ?>";
    echo "<returns>$return</returns>";
}

//////////////////////////////////////////  build_noteline ////////////////////////////////////////

if ($helper_type == "build_noteline") {
    $series_num = $_POST['series_num'];
    $episode_nam = $_POST['episode_nam'];
    $user_id = $_POST['user_id'];
    $scale_factor = $_POST['scale_factor'];
    $scale_correction_for_note = $_POST['scale_correction_for_note'];

    $sql = "SELECT * FROM programme_notes
            WHERE 
                series_num = '$series_num' AND
                episode_nam = '$episode_nam' AND
                note_user_id = '$user_id'
            ORDER BY seconds_into_programme ASC";


    $result = sql_result_for_location($sql, 3);

    $return = '';

    while ($row = mysqli_fetch_array($result)) {
        $seconds_into_programme = $row['seconds_into_programme'];
        $note_text = $row['note_text'];
        $note_type = $row['note_type'];

        $margin_left = ($seconds_into_programme * $scale_factor) + $scale_correction_for_note;
        $note_text_stub = substr($note_text, 0, 15) . ' ...';

        $return .= "<a id='n$seconds_into_programme'" .
                            "style='position:absolute; margin-left: $margin_left" . "px;'" .
                            "title='$note_text_stub'" .
                            "onclick = 'displayEditNotePanel(\"n$seconds_into_programme\");'>" .
                        "<img class='notemark' src='img/notemarkblack.png'>" .
                    "</a>";
    }

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

    $result = sql_result_for_location($sql, 4);

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

    $raw_contents = file_get_contents($url); // needed to set allow_url_fopen in php.ini (see options in choose PHP version)
 
    echo $raw_contents;

    /* left extraction to javascript code for the present - at least this benefits from local caching
    $extracted_contents = '';
    $out_of_paras = false;

    while (!$out_of_paras) {
        $raw_contents_length = strlen($raw_contents);
        // get position of first <p>
        $para_start = strpos($raw_contents, "<p>");

        if ($para_start === false) {
            $out_of_paras = true;
        } else {

        // ignore text before this point
            $raw_contents = substr($raw_contents, $para_start, $raw_contents_length);
            $raw_contents_length = strlen($raw_contents);
            // get position of companion  </p>
            $para_end = strpos($raw_contents, "</p>");
            // tuck away everything up to this point
            $extracted_contents = $extracted_contents . substr($raw_contents, 0, $para_end + 4);
            // ignore text before this point
            $raw_contents = substr($raw_contents, $para_end + 4, $raw_contents_length);
        }

    // change any ʼ characters to ' - they cause 503 errors on the POST!
    // $output = str_replace('/ʼ/g', "'", $output);
    // $output = str_replace('/‘/g', "'", $output);

    }

    echo $extracted_contents;

    */
}

//////////////////////////////////////////  build_insert_note_panel ////////////////////////////////////////

if ($helper_type === "build_insert_note_panel") {
    echo '
        <div style="
                display: flex;
                width: 60%;
                margin: 20px auto;
                border-style: solid;
                border-width: thin;
                padding: 5px;
                justify-content: space-around;">
            <div><button id="lBiteButton" title="Start the sound-bite one second ealier" onclick="playBiteNudgedLeft();"
                    type="button">Nudge L</button>
            </div>
            <div><button id="pBiteButton" title="Play a four-second sound-bite centred on the current time"
                    onclick="postTime=music.currentTime; playBite();" type="button">Play</button>
            </div>
            <div><button id="rBiteButton" title="Start the sound-bite one second later" onclick="playBiteNudgedRight();"
                    type="button">Nudge R</button>
            </div>
        </div>

        <p style="text-align: center">Note/Query text</p>
        <textarea id="cnotetext" rows="4" cols="50" style="margin: 5px auto; display: block;" name="cnotetext"
            title="Notes on sound - eg \'jay hoorshht e\' = dè thuirt e : what did he say?"></textarea>

        <div style="
                        display: flex;
                        width: 60%;
                        margin: 20px auto;
                        border-style: solid;
                        border-width: thin;
                        padding: 5px;
                        justify-content: space-around;">
            <div><button onclick="insertNote(\'note\')" type="button">Save as Note</button></div>
            <div><button onclick="insertNote(\'query\')" type="button">Save as Query</button>
            </div>
            <div><button onclick="activitypanel.innerHTML = \'\';" type="button">Cancel</button></div>
        </div><br>
    </div>';
}

//////////////////////////////////////////  build_edit_note_panel ////////////////////////////////////////

if ($helper_type === "build_edit_note_panel") {
    $series_num = $_POST['series_num'];
    $episode_nam = $_POST['episode_nam'];
    $note_user_id = $_POST['note_user_id'];
    $seconds_into_programme = $_POST['seconds_into_programme'];

    $note_id = "n" . floor($seconds_into_programme);

    $sql = "SELECT * FROM programme_notes
            WHERE 
                series_num = '$series_num' AND
                episode_nam = '$episode_nam' AND
                note_user_id = '$note_user_id'AND
                seconds_into_programme = '$seconds_into_programme'";

    $result = sql_result_for_location($sql, 6);

    $row = mysqli_fetch_assoc($result);

    $note_text = $row['note_text'];
    $note_type = $row['note_type'];
    
    $return = "
        <div style='margin: 20px auto; text-align: center;'>
            <button id='enBiteButton' type='button'>Play Again</button>
        </div>        
        <textarea id='enotetext' rows='4' cols='50' name='ecnotetext' 
            style='margin: 5px auto; display: block;'>$note_text
        </textarea>
        <div style='
                display: flex;
                border-style: solid;
                border-width: thin;
                margin:  20px auto;
                width: 15%;
                padding: 5px;
                justify-content: center;'>                       
            <button title='Save edited Note' type='button'
                onclick = 'editNote(\"$note_id\", \"$note_type\");'>Save</button>
        </div>
        <div style='
                display: flex;
                width: 60%;
                margin: 20px auto;
                border-style: solid;
                border-width: thin;
                padding: 5px;
                justify-content: space-around;'>";
                    
    if ($note_type === "note") {
        $return .= "<button title='Change note type to \"Query\"' type='button'
            onclick = 'editNote(\"$note_id\", \"query\");'>Re-save as Query</button>";
    } else {
        $return .= "<button title='Change note type to \"Note\"' type='button'
            onclick = 'editNote(\"$note_id\", \"note\");'>Re-save as Note</button>";
    }

    $return .=" <button title='Delete Note' type='button'
                    onclick = 'deleteNote(\"$note_id\");'>Delete</button>
                <button  type='button'
                    onclick='activitypanel.innerHTML = \"\"";
    ">Cancel</button>
            </div>";


    echo $return;
}

//////////////////////////////////////////  get_programme_data ////////////////////////////////////////

if ($helper_type === "get_programme_data") {
    $series_num = $_POST['series_num'];
    $episode_nam = $_POST['episode_nam'];

    $sql = "SELECT * FROM programmes
            WHERE 
                series_num = '$series_num' AND
                episode_nam = '$episode_nam'";

    $result = sql_result_for_location($sql, 9);
    
    $row = mysqli_fetch_assoc($result);

    // put the data fields into an associative array

    $returns = array();

    $returns['audiofilename'] = prepareStringforXMLandJSONParse($row['bbc_download_filename']);
    $returns['currentFirstOnDate'] = prepareStringforXMLandJSONParse($row['firston_datestring']);
    $returns['currentEpisodeFinishTime'] = prepareStringforXMLandJSONParse($row['finish_time']);
    $returns['currentLearnerOfTheWeek'] = prepareStringforXMLandJSONParse($row['learner_of_the_week']);
    $returns['currentBBCProgrammeUrl'] = prepareStringforXMLandJSONParse($row['bbc_programme_url']);
    $returns['currentSplashScreenFilename'] = prepareStringforXMLandJSONParse($row['splash_screen_filename']);
    $returns['currentSplashScreenTitle'] = prepareStringforXMLandJSONParse($row['splash_screen_title']);

    $return = json_encode($returns);
    header("Content-type: text/xml");
    echo "<?xml version = '1.0' encoding = 'UTF-8'
    ?>";
    echo "<returns>$return</returns>";
}

//////////////////////////////////////////  get_text_types_data ////////////////////////////////////////

if ($helper_type === "get_text_types_data") {

    $sql = "SELECT * FROM text_types";

    $result = sql_result_for_location($sql, 10);

    // put the data fields into an associative array

    $text_types_data = array();
    
    $i = 0;
    while ($row = mysqli_fetch_array($result)) {
        $text_types_data[$i]['texttype'] = prepareStringforXMLandJSONParse($row['text_type']);
        $text_types_data[$i]['textcolor'] = prepareStringforXMLandJSONParse($row['text_color']);
        $text_types_data[$i]['textheader'] = prepareStringforXMLandJSONParse($row['text_header']);

        $i++;
    }

    $return = json_encode($text_types_data);
    header("Content-type: text/xml");
    echo "<?xml version = '1.0' encoding = 'UTF-8'
    ?>";
    echo "<returns>$return</returns>";
}

//////////////////////////////////////////  backup_data_stores ////////////////////////////////////////

if ($helper_type === "backup_data_stores") {
    $file_string = $_POST['file_string']; // file_string now contains the jsons. Store these in a file

    // get yourself a unique filenumber to enable us to create a different file for every backup (sp
    // no chance of a user getting the notes and jotter for someone else)

    $result = sql_result_for_location('START TRANSACTION', 11);

    $sql = "SELECT * FROM system
            WHERE system_key = 'bab';";

    $result = sql_result_for_location($sql, 12);
    $row = mysqli_fetch_array($result);
    $backup_count = $row['backup_count'];

    $backup_count++;

    $sql = "UPDATE system SET
                backup_count = '$backup_count'
            WHERE 
                system_key = 'bab';";

    $result = sql_result_for_location($sql, 12);

    $result = sql_result_for_location('COMMIT', 13);


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

//////////////////////////////////////////  restore_data_stores ////////////////////////////////////////

if ($helper_type === "restore_data_stores") {

    // recover the unique transitfilename that tells you where to find the unique "transit" file allocated
    // to this restore session

    $transit_filename = $_GET['transitfilename'];

    echo file_get_contents($transit_filename);

    // and finally, delete the transit file

    unlink($transit_filename);
}

//////////////////////////////////////////  increment_download_count ////////////////////////////////////////

if ($helper_type === "increment_download_count") {
    $result = sql_result_for_location('START TRANSACTION', 14);

    $sql = "SELECT * FROM system
            WHERE system_key = 'bab';";

    $result = sql_result_for_location($sql, 15);
    $row = mysqli_fetch_array($result);
    $download_count = $row['download_count'];

    $download_count++;

    $sql = "UPDATE system SET
                download_count = '$download_count'
            WHERE 
                system_key = 'bab';";

    $result = sql_result_for_location($sql, 16);

    $result = sql_result_for_location('COMMIT', 17);

    echo $download_count;
}

//////////////////////////////////////////  get_system_data ////////////////////////////////////////

if ($helper_type === "get_system_data") {
    $sql = "SELECT * FROM system
            WHERE system_key = 'bab';";

    $result = sql_result_for_location($sql, 18);
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

    $returns['versionnumber'] = prepareStringforXMLandJSONParse($version_number);
    $returns['cryptography'] = prepareStringforXMLandJSONParse($cryptography);
    $returns['about'] = prepareStringforXMLandJSONParse($about);
    $returns['hints'] = prepareStringforXMLandJSONParse($hints);

    $return = json_encode($returns);
    header("Content-type: text/xml");
    echo "<?xml version = '1.0' encoding = 'UTF-8'
    ?>";
    echo "<returns>$return</returns>";
}

disconnect_from_database();
