<?php
function isAuthorized()
{
    $headers = apache_request_headers();
    $validApiKey = "123456789";
    return isset($headers['Authorization']) && $headers['Authorization'] === "Bearer $validApiKey";
}
