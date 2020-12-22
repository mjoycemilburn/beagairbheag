<?php
#   this module displays the user's Jotter in a sizeable window. The text is presented in an editable Textarea
#   that can be modified (using cut and paste if required) and re-saved to database storage. A free-standing
#   windows is used to display it above the main BaB display (as for the AFB lookup facility) so that text
#   can be copied from transcripts etc displayed in BaB itself - a common requirement.

$page_title = 'index.php';

# set headers to NOT cache the page
header("Cache-Control: no-cache, must-revalidate"); //HTTP 1.1
header("Pragma: no-cache"); //HTTP 1.0
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Date in the past

session_start();

if (isset($_SESSION['user_logged_in'])) {

# the user is logged in. Give the session a stir by updating a dummy field.
    $_SESSION['last_checked_date_time'] = time();

} else {

    # send the user to bab_login so they can identify themselves, but add  a "jotter" target so that once they've
    # done this they get directed back to edit_user_jotter rather than the main beagaribheag programme

    echo "Sorry - you are not logged into the BaB system";
    die();
}
?>

<!doctype html>

<!-- this module displays the user's Jotter in a sizeable window. The text is presented in an editable Textarea
     that can be modified (using cut and paste if required) and re-saved to database storage. A free-standing
     windows is used to display it above the main BaB display (as for the AFB lookup facility) so that text
     can be copied from transcripts etc displayed in BaB itself - a common requirement.
-->

<!-- see https://developer.mozilla.org/en-US/docs/Web/Guide/HTML/Editable_content for background on
     editable content and "personal_note_editor.html" for a fully-fledged text editor -->

<html>
    <head>
        <title>Jotter</title>

    </head>
    <body>
        <div style="width:90vw; height:100vh; margin-left: auto; margin-right: auto">
            <form enctype="multipart/form-data" method="post" name = "jotterform" style = "width: 100%; height:80%;" >
                <input type = "text" name = "action" hidden value = "U">
                <?php
                echo "<input type = 'text' name = 'user_id' hidden value = '$user_id'>";
                ?>
                
            <!--Due to a misunderstanding, it was thought that to get formatting effects such as blank lines, 
                multiple spaces etc, textarea needed to be declared  with contenteditable = true. It is now known 
                that this is only partially correct. Certainly contenteditable allows text if non-input fields 
                    such as <p> or <div> to be edited, butits main purpose in a textarea (which is, of course 
                editable already) is to create the opportunity to use document.execCommand() to manipulate the
                content - eg by making selected words bold etc.
                See https://developer.mozilla.org/en-US/docs/Web/Guide/HTML/Editable_content. Provided the 
                textarea value is passed back and forth without triggering its interpretation by html (in which
                case the worst that seems to happen is that multiple spaces get reduced to a single space),
                text area handles things just fine without contenteditable
            
                To investigate further, wrote the following bit of code and performed some tests:
            
                    <form enctype="multipart/form-data" method="post" name="messageform">
                        <div id = "div1" contenteditable = "true"><p>Div content</p></div>
                        <textarea id = "textarea" rows = 20 cols = 30><p>Textarea content</p></textarea>
                    </form>

                    after input of :
            
                    "1<a>and'and"
                     2

                     3    4" to both the div and the textarea, got the following from console

                    document.getElementById("div1").innerHTML;
                    "<p>1&lt;a&gt;and 'and"</p><p>2</p><p><br></p><p>3&nbsp; &nbsp;4</p>"
                    document.getElementById("div1").value;
                    undefined
                    document.getElementById("textarea").innerHTML;
                    "&lt;p&gt;Textarea content&lt;/p&gt;"
                    document.getElementById("textarea").value;
                    "1<a>and 'and"

                    2



                    3   4"

                    Conclusion - for <div> used as input, ".value" is meaningless and the content at any
                    moment is given by .innerHTML. Quotes (both single and double) are untouched but < 
                    and > are converted to entities and multiple spaces are represented by pairs of &nbsp 
                    and space characters. Returns are converted to <p></p> pairs, so always get a blank 
                    line

                    By contrast, for <textarea>, innerhtml refers to the initial content of the textarea, 
                    while the content of the field after input is given by .value (same for any input 
                    fields on a form apparently. 
            
                    In .innerhtml , entities are used to represent < and > (don't know about quotes). 
                    In .value, everything is left as input

                    The "value" ones didn't get onto the database - probably because they weren't escaped 
                    and were upset by the quotes. manage_user_jotter which uses a textarea.value gets round this 
                    with a mysqli_real_escape_string call before trying to update the d/b. On select it just gets
                    the data and echoes the result-->
            
                <textarea id = "userjotter" name = "user_jotter"  
                          style = "width:100%; height: 100%; background:paleturquoise;" 
                          spellcheck="false"></textarea>

            </form>
            <p><button id = "savbutton" type="button">Save</button>
                <span id = "savemessage" style = "margin-left: 3vw; color: red;"></span>
            </p>
            <!-- Experimental reference links - seem useful, so have retained tho need to get them 
                 validated by someone with a better grip of grammar!
            
                The geeral arrangement with these buttons is that we display the button with a
                <button> tag, then define a hidden link for it and then a function to click it.
                The whole lot is brought together by a "setButtonState(buttonName, state, action)"
                function. This is all a bit over the top as apart from the Save button, all the 
                buttons are permanently activated, but it's a useful system. -->

            <p>
                <button id = "bbcbutton">Dictionary</button>&nbsp;&nbsp;&nbsp;&nbsp;
                <button id = "trabutton">Translator</button>&nbsp;&nbsp;&nbsp;&nbsp;
                <button id = "prebutton">Prepositions</button>&nbsp;&nbsp;&nbsp;&nbsp;
                <button id = "bebutton">Forms of &apos;Be &apos;</button>&nbsp;&nbsp;&nbsp;&nbsp;
                <button id = "vrbbutton">Verbs</button>&nbsp;&nbsp;&nbsp;&nbsp;
                <button id = "adjbutton">Adjectives</button>&nbsp;&nbsp;&nbsp;&nbsp;
                <button id = "thebutton">Forms of &apos;The &apos;</button>&nbsp;&nbsp;&nbsp;&nbsp;
            </p>

            <a id = "dictionary" hidden href = "https://learngaelic.scot/dictionary/index.jsp" target="_blank">Invisible anchor</a>
            <a id = "translator" hidden href = "https://translate.google.com/?sl#view=home&op=translate&sl=gd&tl=en" target="_blank">Invisible anchor</a>
            <a id = "prepositions" hidden href = "links/prepositions.pdf?" target="_blank">Invisible anchor</a>
            <a id = "formsofbe" hidden href = "links/be.pdf" target="_blank">Invisible anchor</a>
            <a id = "irregverbs" hidden href = "links/verbs.html" target="_blank">Invisible anchor</a>
            <a id = "irregadjectives" hidden href = "links/adjectives.pdf" target="_blank">Invisible anchor</a>
            <a id = "formsofthe" hidden href = "links/the.pdf" target="_blank">Invisible anchor</a>

            <!-- Monitor user_jotter for ctrl_S -->

            <script>

                // The following code is courtesy of https://stackoverflow.com/questions/11362106/how-do-i-capture-a-ctrl-s-without-jquery-or-any-other-library
                // It is used to save the Jotter on entry of a ctrl S sequence. 
                // It is also used to discard unsaved changes
                //  
                // The onkeydown method checks to see if it is the CTRL key being pressed (key code 17). 
                // If so, we set the isCtrl value to true to mark it as being activated and in use. We can 
                // revert this value back to false within the onkeyup function.
                //
                // We then look to see if "S" - key code 83, has been pressed and, if so, save the jotter.
                //
                // To minimise the opportunitites for confusion, should the user start another Jotter session
                // on another computer, a warning is displayed if changes remain unsaved after 5 minutes. If
                // the changes are still unsaved after 10 minutes of inactivity the Jotter is refreshed and
                // the changes are thus discarded

                var userjotter = document.querySelector('textarea[name="user_jotter"]');
                var isCtrl = false;
                var d = new Date();
                var lastActivityTime = d.getTime();
                var lastInputTime = null;
                var userJotterChanged = false;
                var timer;
                var timerRunning = false;
                var savemessage = document.getElementById("savemessage");
                var windowUnattended = false;

                // Ditch any unsaved changes after 10 min and reset the screen
                // Reset the screen on change of focus if more than 10 min inactivity

                userjotter.onkeyup = function (e) {
                    if (e.keyCode == 17) // 17 = ctrl
                        isCtrl = false;

                    // note the time of the last keyup 
                    var d = new Date();
                    lastInputTime = d.getTime();
                    lastActivityTime = lastInputTime;
                    setButtonState(savbutton, "activate", updateUserJotter);
                    savemessage.textContent = ' ';
                    userJotterChanged = true;

                    // start timer to check on lastInput every 5 seconds if not already running

                    if (!timerRunning) {

                        timer = window.setInterval(checkForUnsavedChanges, 5000);
                        timerRunning = true;
                    }
                }

                userjotter.onkeydown = function (e) {
                    if (e.keyCode == 17) //17 = ctrl
                        isCtrl = true;
                    if (e.keyCode == 83 && isCtrl == true) { // 83 = s
                        updateUserJotter();
                        userJotterChanged = false;
                        setButtonState(savbutton, "deactivate", null);
                        savemessage.textContent = ' ';
                        return false;
                    }
                }

                userjotter.onfocus = function (e) {
                    prevActivityTime = lastActivityTime;
                    var d = new Date();
                    lastActivityTime = d.getTime();

                    // usesr has come back to page after more than 10 minutes inactivity - refresh
                    // in case it has been updated elsewhere (note,if there had been changes the user 
                    // would already have been alerted to their loss)

                    if (lastActivityTime - prevActivityTime > 10 * 60000) {
                        console.log("Screen reset owing to inactivity");
                        savemessage.textContent = "Screen reset owing to inactivity";
                        selectUserJotter();
                    }
                }

                function checkForUnsavedChanges() {

                    console.log("checking for changes");
                    var d = new Date();

                    if (userJotterChanged && (d.getTime() - lastInputTime) > 5 * 60000) {
                        savemessage.textContent = "Warning - unsaved changes will be discarded in 5 minutes";
                    }

                    // unsaved changes after 10 min, reset form and remove timer

                    if (userJotterChanged && (d.getTime() - lastInputTime) > 10 * 60000) {
                        savemessage.textContent = "Sorry - unsaved changes have been discarded";
                        selectUserJotter();
                        lastActivityTime = new Date();
                        userJotterChanged = false;
                        setButtonState(savbutton, "deactivate", null);
                        clearTimeout(timer);
                        timerRunning = false;
                    }
                }

                /* code for Ajax  interfaces to Database - used to link to php server-based routines. 
                 Asynchronous calls are used everywhere as synchronous is deprecated */

                var request = null;
                function createRequest() {
                    try {
                        request = new XMLHttpRequest();
                    } catch (trymicrosoft) {
                        try {
                            request = new ActiveObject("Maxm12.XMLHTTP");
                        } catch (othermicrosoft) {
                            try {
                                request = new ActiveObject("Microsoft.XMLHTTP");
                            } catch (failed) {
                                request = null;
                            }
                        }
                    }
                    if (request == null) {
                        alert("Oops - error creating request object");
                    }
                }

                /* --------------------------------------------------------------
                 * 
                 * Introduce global variables representing the input fields (one 
                 * each for the dom element and its value), together with monitoring
                 * functions to track input to them, to perform validation, as possible,
                 * and to modify the status of the button (eg "Upload") that will 
                 * eventually be used to apply them in performing some action or other.
                 * Note that in some circumstances it will not be necessary to /have/ a button
                 * - it may sometimes be appropriate to launch the action automatically,
                 * but generally the user will want an opportunity to review the situation
                 * before launch.
                 * 
                 * -----------------------------------------------------------------------*/


                selectUserJotter();

                // Set buttons to entry states

                var savbutton = document.getElementById("savbutton");
                userJotterChanged = false;
                setButtonState(savbutton, "deactivate", null);

                var bbcbutton = document.getElementById("bbcbutton");
                var trabutton = document.getElementById("trabutton");
                var prebutton = document.getElementById("prebutton");
                var bebutton = document.getElementById("bebutton");
                var vrbbutton = document.getElementById("vrbbutton");
                var adjbutton = document.getElementById("adjbutton");
                var thebutton = document.getElementById("thebutton");

                setButtonState(bbcbutton, "activate", clickBBCLink);
                setButtonState(trabutton, "activate", clickTranslatorLink);
                setButtonState(prebutton, "activate", clickPrepositionsLink);
                setButtonState(bebutton, "activate", clickFormsOfBeLink);
                setButtonState(vrbbutton, "activate", clickVerbsLink);
                setButtonState(adjbutton, "activate", clickAdjectivesLink);
                setButtonState(thebutton, "activate", clickFormsOfTheLink);

                function setButtonState(buttonName, state, action) {

                    // special activation / deactivation states for save button

                    if (buttonName.id == "savbutton") {
                        if (state == "activate") {
                            buttonName.style.backgroundColor = "lightgreen";
                            buttonName.style.opacity = 1.0;
                            buttonName.onclick = action;
                        } else {
                            buttonName.style.backgroundColor = "white";
                            buttonName.style.opacity = 0.5;
                            buttonName.onclick = null;
                        }
                    } else {
                        if (state == "activate") {
                            buttonName.style.backgroundColor = "lightgreen";
                            buttonName.style.opacity = 1.0;
                            buttonName.onclick = action;
                        } else {
                            buttonName.style.opacity = 0.5;
                            buttonName.onclick = null;
                        }
                    }
                }

                function clickBBCLink() {
                    var link = document.getElementById("dictionary");
                    link.click();
                }

                function clickTranslatorLink() {
                    var link = document.getElementById("translator");
                    link.click();
                }
                function clickPrepositionsLink() {
                    var link = document.getElementById("prepositions");
                    link.click();
                }

                function clickFormsOfBeLink() {
                    var link = document.getElementById("formsofbe");
                    link.click();
                }

                function clickVerbsLink() {
                    var link = document.getElementById("irregverbs");
                    link.click();
                }

                function clickAdjectivesLink() {
                    var link = document.getElementById("irregadjectives");
                    link.click();
                }

                function clickFormsOfTheLink() {
                    var link = document.getElementById("formsofthe");
                    link.click();
                }


                /*------------------------------------------------------------------------
                 * Now introduce the functions that will actually perform the button actions
                 * ----------------------------------------------------------------------*/

                function selectUserJotter() {

                    // Asynchronous fetch passing short parameters. In this case
                    // we can use GET and it does not appear that the parameters need actually 
                    // be part of a form. First define your callback function as a variable. This 
                    // is the bit that actually does the work, so it makes sense to put it first

                    selectUserJotterCallback = function () {
                        if (request.readyState == 4) {
                            if (request.status == 200) {
                                var returnMessage = request.responseText;

                                // Note if we wanted an XML view of the response we would have
                                // used request.responseXML above. This of course would have required
                                // use of XML headers in the routine referenced by linkHref rather
                                // than the simple Echo response used here. See format_episode_list.php
                                // in BeagAirBheag for an example

                                userjotter.value = returnMessage;

                            }
                        }
                    };

                    // Now build the request. It's not necessary for the parameters that go
                    // into linkHref to be on a form.

                    createRequest();
<?php
echo "var linkHref = 'manage_user_jotter.php?action=S&user_id=$user_id';";
?>

                    request.open("GET", linkHref, true);
                    request.onreadystatechange = selectUserJotterCallback;
                    request.send(null);
                    console.log(linkHref);

                }

                function updateUserJotter() {

                    // Asynchronous update passing long parameters (longer than 2000 chars or so).
                    // In this case we need to use POST and it applies very much so here in 
                    // edit_user_jotter as we've got a /huge/ parameter to send. It is unclear whether
                    // the parameters need to be grouped together inside a FORM element - almost certainly
                    // so if one of them is type = FILE
                    //
                    // The big difference between the two is that there are no paramters on the url
                    // passed to request.open - these are all passed separately as the argument for
                    // request.send (for GET, this field was null). You also need to define the
                    // format of the data in this field using a setRequestHeader - there are many
                    // possibilities, apparently (eg images), but this one define the parameter as
                    // pairs of field=value characters.  All this is described in Headrush chaper 5 
                    // starting p277 - but be prepared for a half-hour read!
                    //
                    // Note that caching is a potential problem - it was solved in this case by
                    // setting "no-cache" header in managet_user_jotter - without these the 
                    // browser was ignoring the servers response and just displaying its cache copy.
                    // The effect was that updates appeared to being lost
                    //
                    // Another problems has been 403 (forbidden) errors triggered on the hostpapa
                    // server. If you get too many the url will be blocked and you need to contact
                    // the helpdesk to release it. It seemed as one point that this was something
                    // to do with long parameters - shortening the param cleared the proble, However
                    // it then became clear that the 403 would sometimes just "go away" of its own accord



                    updateUserJotterCallback = function () {
                        if (request.readyState == 4) {
                            if (request.status == 200) {
                                if (request.responseText == "database error") {
                                    userJotterChanged = false;
                                    setButtonState(savbutton, "deactivate", null);
                                    savemessage.textContent = request.responseText;
                                } else {
                                    userJotterChanged = false;
                                    setButtonState(savbutton, "deactivate", null);
                                    savemessage.textContent = ' ';
                                }
                            }
                        }
                    };

                    // Now build the request. In this instance it seems that the parameters /do/ need 
                    // be on a form. See below for comments about 403 errors from the Hostpapa server

                    createRequest();

                    var linkHref = "manage_user_jotter.php";

                    request.open("POST", linkHref, true);
                    request.onreadystatechange = updateUserJotterCallback;
                    request.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
                    dataString = "action=" + "U" +
                            "&user_id=" +
<?php
echo "\"$user_id\" +";
?>
                    "&user_jotter=" + userjotter.value;
                    console.log(linkHref + "?" + dataString);
                    request.send(dataString);

                }

                /*           function updateUserJotter() {
                 
                 //  This is an alternative version of asynchronous POST communication.
                 //  It was first used when 403 errors (forbidden) were experienced on Hostpapa
                 //  with the version above (see comments in saveBlog function in index.html of
                 //  digApplebyBlogMaint). It seemed to fix the problem but this may have been illusory
                 //  - seec omments above
                 //                                         
                 // see https://developer.mozilla.org/en-US/docs/Web/API/FormData/Using_FormData_Objects
                 
                 var form = document.forms.namedItem("jotterform");
                 var oData = new FormData(form);
                 oData.append("action", "U");
                 <php !!!!Note ? removed here
                 echo "oData.append('user_id', '$user_id');";
                 ?>
                 oData.append("user_jotter", userjotter.value);
                 
                 var oReq = new XMLHttpRequest();
                 oReq.open("POST", "manage_user_jotter.php", true);
                 oReq.onload = function (oEvent) {
                 if (oReq.status == 200) {
                 
                 if (oReq.responseText == "database error") {
                 userJotterChanged = false;
                 setButtonState(savbutton, "deactivate", null);
                 savemessage.textContent = oReq.responseText;
                 } else {
                 userJotterChanged = false;
                 setButtonState(savbutton, "deactivate", null);
                 savemessage.textContent = undefined;
                 }
                 }
                 }
                 ;
                 oReq.send(oData);
                 event.preventDefault();
                 }
                 */
            </script>

        </div>
    </body>
</html>


