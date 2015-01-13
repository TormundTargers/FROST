<!DOCTYPE html>
<html>
<head>
	<meta charset="UTF-8">
	<title>FROST - Upload video</title>
    <link rel="stylesheet" type="text/css" href="../styles/master.css">
</head>

<body>
	<?php
		include '../banner.html';
	?>
    <div class="main">
        <h2>Upload</h2>
        <form enctype="multipart/form-data" action="upload_file.php" method="POST">
            <!-- Video information -->
            <table>
                <tr>
                    <td><label for="title">Title: </label></td>
                    <td><input type="text" name="title"></td>
                </tr>
                <tr>
                    <td><label for="description">Description: </label></td>
                    <td><textarea maxlength="500" name="description" placeholder="Enter a description of the video here..."></textarea></td>
                </tr>
                <tr>
                    <td><label for="uploader_name">Name: </label></td>
                    <td><input type="text" name="uploader_name"></td>
                </tr>
                <tr>
                    <td><label for="uploadtopomf">Upload to pomf.se?</label></td>
                    <td><input type="checkbox" name="uploadtopomf" value="true"></td>
                </tr>
                <tr>
                    <td><label for="userfile">Upload:</label></td>
                    <td><input type="file" name="userfile" data-max-size="50MiB"></td>
                </tr>
            </table>

            <input type="submit" value="Send File">
        </form>
    </div>

</body>
</html>
