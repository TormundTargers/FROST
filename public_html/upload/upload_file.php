<?php //ini_set('display_errors',1); error_reporting(E_ALL); // Display errors

// Including PHP files
require_once '../getwd.php';
// Get working directory
$cwd = ("http://" . $_SERVER['HTTP_HOST'] . getwd() . "/");

if ((($_FILES["userfile"]["type"] == "video/webm")  /* <-- This is naive since the type can be faked */
|| ($_FILES["userfile"]["type"] == "video/mp4")     /* We should try using finfo_open */
|| ($_FILES["userfile"]["type"] == "video/ogg")
&& ($_FILES["userfile"]["size"] < 50000000)))
{
	if ($_FILES["userfile"]["error"] > 0) {
		// Return an error if file does not meet requirements
		echo "Return Code: " . $_FILES["userfile"]["error"] . "<br>";
	}
    else {
        $addToDb = false;
		require_once '../libs/avipedia_tripcode.php';

        // Create/initialise fields for insertion into table
        $title = trim(htmlentities(strip_tags($_POST["title"]), ENT_QUOTES));
        $description = trim(htmlentities(strip_tags($_POST["description"]), ENT_QUOTES));
        $filetype = substr($_FILES["userfile"]["type"], 6);
        $url;
        $host_code;
        $uploader_ip = $_SERVER["REMOTE_ADDR"];

        // Seperate uploader_name and tripcode and legalise the characters.
        $uploader_info = explode("#", $_POST["uploader_name"]);
        $uploader_info[0] == "" ? $uploader_name = null : $uploader_name = $uploader_info[0];
        $uploader_name = trim(htmlentities(strip_tags($uploader_name), ENT_QUOTES));
        $tripcode = mktripcode($uploader_info[1]);

        $upload_date = date("Y-m-d H:i:s");
		$filename = trim(htmlentities(strip_tags($_FILES["userfile"]["name"]), ENT_QUOTES));

		// Display information
		echo "File name on upload: " . $filename . "<br>";
		echo "Title: " . $title . "<br>";
		echo "Description: " . $description . "<br>";
		echo "Type: " . $filetype . "<br>";
		echo "Size: " . ($_FILES["userfile"]["size"] / 1024) . " kB<br>";
        echo "Uploader name: " . $uploader_name . "<br>";
		echo "Temp file: " . $_FILES["userfile"]["tmp_name"] . "<br>";
		echo "Time video completed upload: " . $upload_date . "<br>";
		echo "Trip code of uploader: " . $tripcode . "<br>";

		if (isset($_POST["uploadtopomf"])) {

			echo "<pre>";
			echo "Loading ...";

			function curl_progress_callback($resource, $download_size, $downloaded, $upload_size, $uploaded)
			{
				if($upload_size > 0) {
					echo $uploaded / $upload_size  * 100 . "%";
					echo " Average upload speed: " . curl_getinfo($resource, CURLINFO_SPEED_UPLOAD) . "B/s<br>";
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

            // Used for compatibility with PHP 5.6+
            // This allows support for uploading files in CURLOPT_POSTFIELDS using the @ prefix
            curl_setopt($request, CURLOPT_SAFE_UPLOAD, false);  // This will be changed to curlfile at a later date

			// Set options to get progress bar
			curl_setopt($request, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($request, CURLOPT_PROGRESSFUNCTION, 'curl_progress_callback');
//            curl_setopt($ch, CURLOPT_BUFFERSIZE, 128);    // Potentially altering the uploaded_files speed
			curl_setopt($request, CURLOPT_NOPROGRESS, false); // needed to make progress function work
			curl_setopt($request, CURLOPT_HEADER, 0);
			curl_setopt($request, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);

			// send a file
			curl_setopt($request, CURLOPT_POST, true);
			curl_setopt(
				$request,
				CURLOPT_POSTFIELDS,
				array(
					'files[]' =>
                        '@' 		. $_FILES["userfile"]["tmp_name"]
    					. ';filename='	. $_FILES["userfile"]["name"]
    					. ';type='	. $_FILES["userfile"]["type"]
				));

            // Execute the uploaded_files and decode the url it was stored at
            $jsonArray = json_decode(curl_exec($request), true);
            $url = $jsonArray['files'][0]['url'];

			// close the session
			curl_close($request);

			echo "Done<br>";

            // Check if video was correctly uploaded to pomf
            if($jsonArray['success']){
                $addToDb = true;
                $host_code = 2;
            }
			ob_flush();
			flush();

		}
        else {
			if (file_exists("uploaded_files/$filename")) {
				echo $filename . " already exists. ";
			}
            else {
				// If requirements of the file are met, move the file from temp to permanent location
				move_uploaded_file($_FILES["userfile"]["tmp_name"],
				"uploaded_files/$filename");
				echo "Stored in: " . "uploaded_files/$filename";
			}
            $addToDb = true;
            $url = "uploaded_files/$filename";
            $host_code = 1;
			// Display video
			echo "<br><video controls><source src='" . $cwd . "uploaded_files/" . $filename . "' type='" . $_FILES["userfile"]["type"] . "'>Your browser does not support the video tag.</video>";
		}
        if($addToDb) {
            // Connect to database and insert new video
            require_once '../dbconnect.php';
            $dbh = dbconnect();
            $sql = 'INSERT INTO videos
                    (title, description, filetype, url, host_code, uploader_ip, uploader_name, tripcode, upload_date)
                    VALUES
                    (:title, :description, :filetype, :url, :host_code, :uploader_ip, :uploader_name, :tripcode, :upload_date)';
			$insert = $dbh -> prepare($sql);
			$insert -> bindParam(':title', $title, PDO::PARAM_STR);
			$insert -> bindParam(':description', $description, PDO::PARAM_STR);
			$insert -> bindParam(':filetype', $filetype, PDO::PARAM_STR);
			$insert -> bindParam(':url', $url, PDO::PARAM_STR);
			$insert -> bindParam(':host_code', $host_code, PDO::PARAM_INT);
			$insert -> bindParam(':uploader_ip', $uploader_ip, PDO::PARAM_STR);
			$insert -> bindParam(':uploader_name', $uploader_name, PDO::PARAM_STR);
			$insert -> bindParam(':tripcode', $tripcode, PDO::PARAM_STR);
			$insert -> bindParam(':upload_date', $upload_date, PDO::PARAM_STR);

			if($insert -> execute() === false) {
				die('Error running insert: ' . implode($insert->errorInfo(), ' '));
			}

            echo "Located at: " . $url;
        }
	}
}
else {
	echo "Invalid file - Exceeds file size limits or bad file type";
}
