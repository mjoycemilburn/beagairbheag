<?php

// This routine creates test variables to support php debugging when
// the helper file has been called directly from vs code rather than
// with an xmlHTTP call from an html file. The settings below replace
// the missing $_POST variables required by the helper file. For
// some weird reason, $_SERVER['REMOTE_ADDR'] is not set by the xdebug
// PHP server, so this setting always needs to be present in order to
// permit the database connection routine to work properly.

$_POST['helper_type'] = "refresh_bab_programme_tables";
$_POST['programmesJson'] = '[\
{\
"seriesnum" : "1",\
"episodenam" : "Episode 1",\
"firstondate" : "2020/11/20",\
"finishtime" : "",\
"learneroftheweek" : "",\
"bbcprogrammeurl" : "httpsa://www.bbc.co.uk/programmes/m000ltr7 ",\
"bbcdownloadfilename" : ""\
},\
{\
"seriesnum" : "1",\
"episodenam" : "Episode 2",\
"firstondate" : "2020-01-01",\
"finishtime" : "1234",\
"learneroftheweek" : "asd",\
"bbcprogrammeurl" : "zxc",\
"bbcdownloadfilename" : ""\
},\
{\
"seriesnum" : "1",\
"episodenam" : "Episode 5",\
"firstondate" : "",\
"finishtime" : "123",\
"learneroftheweek" : "",\
"bbcprogrammeurl" : "",\
"bbcdownloadfilename" : ""\
},\
{\
"seriesnum" : "1",\
"episodenam" : "Episode 6",\
"firstondate" : "2020/11/03",\
"finishtime" : "123",\
"learneroftheweek" : "asd",\
"bbcprogrammeurl" : "aa",\
"bbcdownloadfilename" : "aa"\
},\
{\
"seriesnum" : "8",\
"episodenam" : "Episode 6",\
"firstondate" : "2018/12/12",\
"finishtime" : "5552",\
"learneroftheweek" : "Ceitidh Montooth",\
"bbcprogrammeurl" : "BeagAirBheag-20180429-Episode6",\
"bbcdownloadfilename" : ""\
},\
{\
"seriesnum" : "8",\
"episodenam" : "Episode 7",\
"firstondate" : "2018/05/06",\
"finishtime" : "5413",\
"learneroftheweek" : "Tim Dawson",\
"bbcprogrammeurl" : "BeagAirBheag-20180506-Episode7",\
"bbcdownloadfilename" : ""\
},\
{\
"seriesnum" : "8",\
"episodenam" : "Episode 8",\
"firstondate" : "2018/05/13",\
"finishtime" : "5642",\
"learneroftheweek" : "Maureen NicLeòid",\
"bbcprogrammeurl" : "BeagAirBheag-20180513-Episode8",\
"bbcdownloadfilename" : ""\
},\
{\
"seriesnum" : "8",\
"episodenam" : "Runrig Special",\
"firstondate" : "2018/07/01",\
"finishtime" : "5533",\
"learneroftheweek" : "Donnie Rothach",\
"bbcprogrammeurl" : "BeagAirBheag-20180624-RunrigSpecial",\
"bbcdownloadfilename" : ""\
},\
{\
"seriesnum" : "9",\
"episodenam" : "Episode 1",\
"firstondate" : "2018/10/17",\
"finishtime" : "5959",\
"learneroftheweek" : "Daibhidh Pritchard",\
"bbcprogrammeurl" : "BeagAirBheag-20181014-Episode1",\
"bbcdownloadfilename" : ""\
},\
{\
"seriesnum" : "12",\
"episodenam" : "Episode 1",\
"firstondate" : "2020/06/14",\
"finishtime" : "5421",\
"learneroftheweek" : "Kevin Leetion",\
"bbcprogrammeurl" : "https://www.bbc.co.uk/programmes/m000k423 ",\
"bbcdownloadfilename" : "BeagAirBheag-20200614-Episode1"\
},\
{\
"seriesnum" : "12",\
"episodenam" : "Episode 2",\
"firstondate" : "2020/06/21",\
"finishtime" : "5432",\
"learneroftheweek" : "Gillian Mucklow",\
"bbcprogrammeurl" : "https://www.bbc.co.uk/programmes/m000k7x4",\
"bbcdownloadfilename" : "BeagAirBheag-20200621-Episode2"\
},\
{\
"seriesnum" : "12",\
"episodenam" : "Episode 3",\
"firstondate" : "2020/06/28",\
"finishtime" : "5531",\
"learneroftheweek" : "Cailean MacCoinnich",\
"bbcprogrammeurl" : "https://www.bbc.co.uk/programmes/m000kfnm",\
"bbcdownloadfilename" : "BeagAirBheag-20200628-Episode3"\
},\
{\
"seriesnum" : "12",\
"episodenam" : "Episode 4",\
"firstondate" : "2020/07/05",\
"finishtime" : "5531",\
"learneroftheweek" : "Michelle Otto",\
"bbcprogrammeurl" : "https://www.bbc.co.uk/programmes/m000knbw",\
"bbcdownloadfilename" : "BeagAirBheag-20200705-Episode4"\
}\
]';

$_POST['programmeTextsJson'] = "Episode 6";
$_POST['textTypesJson'] = "smj";


$_SERVER = array();
$_SERVER['REMOTE_ADDR'] = '127.0.0.1';