<?php
// token_generator.php
function generateBasicAuthToken() {
    $credentials = API_USERNAME . ':' . API_PASSWORD;
    $encodedCredentials = base64_encode($credentials);
    return 'Basic ' . $encodedCredentials;
}