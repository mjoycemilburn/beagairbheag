<?php

function connect_to_database()
{
    global $con, $url_root;

    if (($_SERVER['REMOTE_ADDR'] == '127.0.0.1' or $_SERVER['REMOTE_ADDR'] == '::1')) {
        $url_root = '../../';

    } else {
        $current_directory_root = $_SERVER['DOCUMENT_ROOT']; // one level above current directory
        // remove everything after and including "public_html"

        $pieces = explode('public_html', $current_directory_root);
        $url_root = $pieces[0];
    }

    require($url_root . "connect_beagairbheagdb.php");
}

function disconnect_from_database()
{
    global $con, $url_root;

    require($url_root . "disconnect_beagairbheagdb.php");
}

function sql_result_for_location($sql, $location)
{
    global $con, $page_title;

    $result = mysqli_query($con, $sql);

    if (!$result) {
        echo "Oops - database access %failed%. in $page_title location $location. Error details follow : " . mysqli_error($con);

        $sql = "ROLLBACK";
        $result = mysqli_query($con, $sql);

        disconnect_from_database();
        exit(1);
    }

    return $result;
}




function prepareStringforXMLandJSONParse($input)
{

    # < , > and & must be turned into &lt; , &gt; and &amp; to get them through an XML return
    # " and line feeds (\n) must be turned into \\" and \\n to make them acceptable to JSON.Parse
    # &nbsp; must be turned in " "
    # &quot; must be turned into "'"
    #
    # maybe should consider encodeURIComponent  see https://stackoverflow.com/questions/20960582/html-string-nbsp-breaking-json
    # For JSON syntax see https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Global_Objects/JSON
    # Not clear from this why &nbsp; is breaking the return of the JSON - basically empty  - for
    # further info on URL encoding see https://www.urlencoder.io/learn/
    #
    # You might have thought we would do this at the outset before these characters reached "helpers"
    # but the problem is that escaped strings get unescaped when they're stored on the database. The
    # "tag" characters < and > could probably have been dealt with at the outset, but it seems better
    # to keep things together

    $output = $input;

    $output = str_replace('&', '&amp;', $output); ## haha - best do this first eh!!
    $output = str_replace('<', '&lt;', $output);
    $output = str_replace('>', '&gt;', $output);


    $output = str_replace('"', '\\"', $output);
    $output = str_replace('&nbsp;', ' ', $output);
    $output = str_replace('&quot;', "'", $output);

    return $output;
}

function buildTextTypeArrays()
{
    global $text_colors, $text_headers;

    $text_colors = array();
    $text_headers = array();

    $sql = "SELECT * FROM text_types";

    $result = sql_result_for_location($sql, 0);

    while ($row = mysqli_fetch_array($result)) {
        $text_type = $row['text_type'];
        $text_colors[$text_type] = $row['text_color'];
        $text_headers[$text_type] = $row['text_header'];
    }
}
