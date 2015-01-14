(function() {

    if(window.FormData) {
        var formData = null;
        $id("postForm").onsubmit = function(e) {
            e.preventDefault();

            var file;
            $id("uploadResult").innerHTML = "Uploading...";
            file = $id("userFile").files[0];

            if (!!file.type.match(/video.*/)) {
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
                        progressBar.value = (e.loaded / e.total) * 100;
                    }
                };
                xhr.send(formData);
            }
        };
    }

    function $id(id) {
        return document.getElementById(id);
    }
})();