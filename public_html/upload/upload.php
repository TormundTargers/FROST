<!DOCTYPE html>
<html>
<head>
	<meta charset="UTF-8">
	<title>FROST - Upload video</title>
    <link rel="stylesheet" type="text/css" href="../styles/master.css">
    <link rel="stylesheet" type="text/css" href="../styles/upload.css">
</head>

<body>
	<?php
		include 'banner.html';
	?>
    <div class="main">
        <div class="float-left">
            <h2>Upload</h2>
            <form enctype="multipart/form-data" action="upload_to_server.php" method="POST" id="postForm">
                <!-- Video information -->
                <table>
                    <tr>
                        <td><label for="title">Title: </label></td>
                        <td><input type="text" name="title" id="title"></td>
                    </tr>
                    <tr>
                        <td><label for="description">Description: </label></td>
                        <td><textarea maxlength="500" name="description" placeholder="Enter a description of the video here..." id="description"></textarea></td>
                    </tr>
                    <tr>
                        <td><label for="uploader_name">Name: </label></td>
                        <td><input type="text" name="uploader_name" id="uploader_name"></td>
                    </tr>
                    <tr>
                        <td><label for="uploadtopomf">Upload to pomf.se?</label></td>
                        <td><input type="checkbox" name="uploadtopomf" checked="checked" id="uploadtopomf"></td>
                    </tr>
                    <tr>
                        <td><label for="userFile">Upload:</label></td>
                        <td><input type="file" name="userFile" data-max-size="50MiB" id="userFile"></td>
                    </tr>
                    <tr>
                        <td><input type="submit" value="Send File"></td>
                        <td><div class="progress-bar">
                                <progress max="100" value="0" id="progress">0%</progress>
                                <div id="percentage"></div>
                            </div>
                        </td>
                    </tr>
                </table>
            </form>
            <div id="uploadResult"></div>
        </div>
        <div id="video"></div>
    </div>
    <script src="../scripts/upload_ajax.js"></script>
</body>
</html>
