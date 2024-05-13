<?php
session_start();

$response = array();

if (isset($_SESSION['loggedin'])) {
    unset($_SESSION['loggedin']);
    $response['loggedout'] = true;
} else {
    $response['loggedout'] = false;
}

header('Content-Type: application/json');
echo json_encode($response);
?>