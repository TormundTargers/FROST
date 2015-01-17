(function() {

    if(window.FormData) {
        var formData = null;
        $id("postForm").onsubmit = function(e) {
            e.preventDefault();

            var file;
            $id("uploadResult").innerHTML = "Uploading...";
            file = $id("userFile").files[0];

            if ((file.type == "video/webm" || file.type == "video/mp4" || file.type == "video/ogg")) {
                formData = new FormData($id("postForm"));
            }
            if(formData != null) {

                var xhr = new XMLHttpRequest();
                xhr.onreadystatechange = function() {
                    if(xhr.readyState == 4 && xhr.status == 200) {
                        var response = JSON.parse(xhr.responseText);
                        $id("uploadResult").innerHTML = response.uploadResult;
                        $id("video").innerHTML = response.video;
                    }
                };
                xhr.open("POST", $id("postForm").action, true);
                var progressBar = $id("progress");
                xhr.upload.onprogress = function(e) {
                    if(e.lengthComputable) {
                        var progress = (e.loaded / e.total) * 100;
                        progressBar.value = progress;
                        $id("percentage").innerHTML = progress.toFixed(2) + "%";
                    }
                };
                xhr.upload.onload = function(e) {
                    /*
                        if user wanted to upload to pomf
                        wait 2 seconds then poll 'get_upload_perc.php'
                        for pomf upload percentage
                     */
                };
                xhr.send(formData);
            }
        };
    }

    function $id(id) {
        return document.getElementById(id);
    }
})();