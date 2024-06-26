<?php
// server_a.php

// Data to be sent
$data = array(
    'phone_number' => 'value1',
    'message' => 'value2',
    'account' => 'value3'
);

// URL of the receiving server (Server B)
$target_url = 'http://localhost/LPMS/sms.test.php';

// Initialize cURL session
$api_key = 'PASSWORD';
$ch = curl_init($target_url);

curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json', 'API-Key: ' . $api_key));
$response = curl_exec($ch);

// Check for errors
if (curl_errno($ch)) {
    echo 'Curl error: ' . curl_error($ch);
}

// Close cURL session
curl_close($ch);

// Print the response from Server B
echo $response;
?>