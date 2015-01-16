<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

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
        $url = "../uploaded_files/$filename";
        $host_code = 1;
        // Display video
        $video = "<br><video controls><source src='../uploaded_files/" . $filename . "' type='" . $_FILES["userFile"]["type"] . "'>Your browser does not support the video tag.</video>";
    }
}
else {
    $uploadResult = "Invalid file - Exceeds file size limits or bad file type";
}

echo json_encode(array('uploadResult' => $uploadResult, 'video' => $video));