<?php

session_start();
$response;
$request = file_get_contents('php://input');
$data = json_decode($request, true);

if(isset($data['count'])) {
    if($data['count'] == 0 && isset($_SESSION['curlProgress'])) {
        unset($_SESSION['curlProgress']);
    }
}
if(isset($_SESSION['curlProgress'])) {
    $response = $_SESSION['curlProgress'];
    session_write_close();
}
else {
    $response = 0;
}
echo json_encode(array('curlProgress' => $response));