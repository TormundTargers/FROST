(function() {

    if(window.FormData) {
        var formData = null;
        $id("postForm").onsubmit = function(e) {
            e.preventDefault();

            var file;
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
                        $id("percentage").innerHTML = "Uploading to frost: " + progress.toFixed(2) + "%";
                    }
                };
                xhr.upload.onload = function(e) {
                    if($id("uploadtopomf").checked) {
                        progressBar.value = 0;
                        $id("percentage").innerHTML = "Uploading to pomf...";
                        poll(0, 0, $id("progress"));
                    }
                    else {
                        $id("percentage").innerHTML = "Upload complete";
                    }
                };
                xhr.send(formData);
            }
        };
    }

    function $id(id) {
        return document.getElementById(id);
    }

    function poll(percentage, count, progressBar) {
        setTimeout(function() {
            if (percentage == 100) {
                $id("percentage").innerHTML = "Upload complete";
                return;
            }
            var curl = new XMLHttpRequest();
            curl.onreadystatechange = function () {
                if (curl.readyState == 4 && curl.status == 200) {
                    var response = JSON.parse(curl.responseText);
                    var curlProgress = response.curlProgress;
                    percentage = curlProgress;
                    progressBar.value = curlProgress;
                    curlProgress = curlProgress == 0 ? "Waiting..." : curlProgress.toFixed(2) + "%";
                    $id("percentage").innerHTML = "Uploading to pomf: " + curlProgress;
                    count++;

                    // Let's go recursive on this bitch!
                    poll(percentage, count, progressBar);
                }
            };
            curl.open("POST", "../upload/get_upload_perc.php", true);
            curl.setRequestHeader("Content-type", "application/json");
            curl.send(JSON.stringify({"count": count}));
        }, 500);
    }
})();