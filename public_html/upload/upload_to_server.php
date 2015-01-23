<?php
/*  The below error reporting must be turned off for release!
    Otherwise this script will return warnings as well as the JSON.
    This causes upload_ajax.js to fail when showing the video
    and its metadata
*/
//ini_set('display_errors', 1);
//error_reporting(E_ALL);

$uploadResult = null;
$video = null;

if ((($_FILES["userFile"]["type"] == "video/webm")  /* <-- This is naive since the type can be faked */
    || ($_FILES["userFile"]["type"] == "video/mp4")     /* We should try using finfo_open */
    || ($_FILES["userFile"]["type"] == "video/ogg")
    && ($_FILES["userFile"]["size"] < 50000000)))
{
    if ($_FILES["userFile"]["error"] > 0) {
        // Return an error if file does not meet requirements
        $uploadResult = "Error! Return Code: " . $_FILES["userFile"]["error"] . "<br>";
    }
    else {
        $addToDb;
        require_once '../libs/avipedia_tripcode.php';
        date_default_timezone_set("UTC");

        // Create/initialise fields for insertion into table
        $title = trim(htmlentities(strip_tags($_POST["title"]), ENT_QUOTES));
        $description = trim(htmlentities(strip_tags($_POST["description"]), ENT_QUOTES));
        $filetype = substr($_FILES["userFile"]["type"], 6);
        $url;
        $host_code;
        $uploader_ip = $_SERVER["REMOTE_ADDR"];

        // Seperate uploader_name and tripcode and legalise the characters.
        $uploader_info = explode("#", $_POST["uploader_name"]);
        $uploader_info[0] == "" ? $uploader_name = null : $uploader_name = $uploader_info[0];
        $uploader_name = trim(htmlentities(strip_tags($uploader_name), ENT_QUOTES));
        $tripcode = count($uploader_info) == 2 ? (mktripcode($uploader_info[1])) : null;
        $pomf = isset($_POST['uploadtopomf']);

        $upload_date = date("Y-m-d H:i:s");
        $filename = trim(htmlentities(strip_tags($_FILES["userFile"]["name"]), ENT_QUOTES));

        // Display information
        $uploadResult = "File name on upload: " . $filename . "<br>";
        $uploadResult .= "Title: " . $title . "<br>";
        $uploadResult .= "Description: " . $description . "<br>";
        $uploadResult .= "Type: " . $filetype . "<br>";
        $uploadResult .= "Size: " . ($_FILES["userFile"]["size"] / 1024) . " kB<br>";
        $uploadResult .= "Uploader name: " . $uploader_name . "<br>";
        $uploadResult .= "Temp file: " . $_FILES["userFile"]["tmp_name"] . "<br>";
        $uploadResult .= "Time video completed upload: " . $upload_date . "<br>";
        $uploadResult .= "Trip code of uploader: " . $tripcode . "<br>";
        $uploadResult .= "Uploader ip address: " . $uploader_ip . "<br>";
        $uploadResult .= "Upload to pomf: " . $pomf . "<br>";

        /*
         * Check if user wanted to upload to pomf
         * if so, trigger the upload to pomf
         */

        if($pomf) {
            function curl_progress_callback($resource, $download_size, $downloaded, $upload_size, $uploaded)
            {
                if($upload_size > 0) {
                    session_start();
                    $_SESSION['curlProgress'] = $uploaded / $upload_size  * 100;
                    session_write_close();
                }
                ob_flush();
                flush();
            }

            // Increase max execution time to a day
            set_time_limit(86400);

            ob_flush();
            flush();

            // initialise the curl request
            $request = curl_init('http://pomf.se/upload.php');

            // Set options to get progress bar
            curl_setopt($request, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($request, CURLOPT_PROGRESSFUNCTION, 'curl_progress_callback');
            curl_setopt($request, CURLOPT_NOPROGRESS, false);
            curl_setopt($request, CURLOPT_HEADER, 0);
            curl_setopt($request, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);

            // send a file
            $cFile = new CURLFile(
                $_FILES['userFile']['tmp_name'],
                $_FILES['userFile']['type'],
                $_FILES['userFile']['name']
            );
            $postData = array("files[]" => $cFile);
            curl_setopt($request, CURLOPT_POST, true);
            curl_setopt($request, CURLOPT_POSTFIELDS, $postData);

            // Execute the uploaded_files and decode the url it was stored at
            $jsonArray = json_decode(curl_exec($request), true);
            $url = $jsonArray['files'][0]['url'];

            // close the session
            curl_close($request);

            // Check if video was correctly uploaded to pomf
            if($jsonArray['success']){
                $addToDb = true;
                $host_code = 2;
            }
            ob_flush();
            flush();
        }
        else {
            if (file_exists("../uploaded_files/$filename")) {
                $uploadResult .= $filename . " already exists. ";
            }
            else {
                // If requirements of the file are met, move the file from temp to permanent location
                move_uploaded_file($_FILES["userFile"]["tmp_name"],
                    "../uploaded_files/$filename");
                $uploadResult .= "Stored in: " . "../uploaded_files/$filename";
                // Sleep for two seconds.
                sleep(2);
            }
            $addToDb = true;
            $url = $filename;
            $host_code = 1;
        }
        // Display video
        switch ($host_code) {
            case 1:
                $video_url = "../uploaded_files/" . $url; break;
            case 2:
                $video_url = "http://a.pomf.se/" . $url; break;
            default:
                $video_url = "../uploaded_files/" . $url; break;
        }
        $video = "<br><video controls><source src='" . $video_url . "' type='" . $_FILES["userFile"]["type"] . "'>Your browser does not support the video tag.</video>";
        // do database stuff
    }
}
else {
    $uploadResult = "Invalid file - Exceeds file size limits or bad file type";
}

echo json_encode(array('uploadResult' => $uploadResult, 'video' => $video));