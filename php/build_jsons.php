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

// connect to the beagairbheag database

connect_to_database();

# Load the utf8 character set to enable accented characters to be stored (they go up otherwise
# as diamond characters with a central question mark). See notes at the top of text_manageent.html

if (!mysqli_set_charset($con, "utf8")) {
    echo "Error loading character set utf8: %s\n", mysqli_error($con);
    require('/home/qfgavcxt/disconnect_beagairbheagdb_old.php');
    exit(1);
}

$series_array = [];

$sql = 'SELECT * FROM programmes';

// get the total_number_of_programmes so that we can use it to detect when
// we've reached the last programme and can then close the json off

$result = sql_result_for_location($sql, 1);
$total_number_of_programmes = mysqli_num_rows($result);

echo "var programmes_json = '[\\<br>";

$sql = 'SELECT DISTINCT series_num FROM programmes
        ORDER BY series_num ASC;';

$result = sql_result_for_location($sql, 1);

$json_length = 0;
$i = 0;

while ($row = mysqli_fetch_array($result)) {
    $series_array[$i]= $row["series_num"];
    $i++;
}

for ($i = 0; $i < count($series_array); $i++) {
    $series_num = $series_array[$i];
    $sql = "SELECT * FROM programmes
        WHERE series_num = '$series_num'
        ORDER BY episode_nam ASC;";

    $result = sql_result_for_location($sql, 2);

    while ($row = mysqli_fetch_array($result)) {
        $json_length++;
        echo '{\\<br>';
        echo '"seriesnum" : "' . $row["series_num"] . '",\\<br>';
        echo '"episodenam" : "' . $row["episode_nam"] . '",\\<br>';
        echo '"firstondate" : "' . $row["firston_datestring"] . '",\\<br>';
        echo '"finishtime" : "' . $row["finish_time"] . '",\\<br>';
        echo '"learneroftheweek" : "' . $row["learner_of_the_week"] . '",\\<br>';
        echo '"bbcprogrammeurl" : "' . $row["bbc_programme_url"] . '",\\<br>';
        echo '"bbcdownloadfilename" : "' . $row["bbc_download_filename"] . '"\\<br>';
    
        if ($json_length === $total_number_of_programmes) {
            echo '}\\<br>';
        } else {
            echo '},\\<br>';
        }
    }
}

echo "]';<br>";

$sql = 'SELECT * FROM programme_texts';

$result = sql_result_for_location($sql, 3);
$total_number_of_programme_texts = mysqli_num_rows($result);

$json_length = 0;

echo "var programme_texts_json = '[\\<br>";

for ($i = 0; $i < count($series_array); $i++) {
    $series_num = $series_array[$i];

    $sql1 = "SELECT DISTINCT episode_nam  FROM programme_texts
            WHERE series_num = '$series_num';";

    $result1 = sql_result_for_location($sql1, 4);
    while ($row1 = mysqli_fetch_array($result1)) {
        $episode_nam = $row1["episode_nam"];

        $sql2 = "SELECT * FROM programme_texts WHERE
                series_num = '$series_num' AND
                episode_nam = '$episode_nam'
                ORDER BY start_time_in_programme;";

        $result2 = sql_result_for_location($sql2, 5);

        while ($row = mysqli_fetch_array($result2)) {
            $json_length++;
            echo '{\\<br>';
            echo '"seriesnum" : "' . $row["series_num"] . '",\\<br>';
            echo '"episodenam" : "' . $row["episode_nam"] . '",\\<br>';
            echo '"texttype" : "' . $row["text_type"] . '",\\<br>';
            echo '"texttitle" : "' . $row["text_title"] . '",\\<br>';
            echo '"starttimeinprogramme" : "' . $row["start_time_in_programme"] . '",\\<br>';
            echo '"finishtimeinprogramme" : "' . $row["finish_time_in_programme"] . '",\\<br>';
            echo '"texturl" : "' . $row["text_url"] . '"\\<br>';

            if ($json_length === $total_number_of_programme_texts) {
                echo '}\\<br>';
            } else {
                echo '},\\<br>';
            }
        }
    }
}

echo "]';";
