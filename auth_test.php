<?php

$fields = array('gid' => 1, 'status' => 100, 'vid' => 1);

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "http://dev.1800approved.com.au/api/v1/genius/updateHubSpot");
curl_setopt($ch, CURLOPT_TIMEOUT, 30); //timeout after 30 seconds
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
curl_setopt($ch, CURLOPT_USERPWD, "root:r0Ot_C0n643");
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
$status_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);   //get status code
$result = curl_exec($ch);
curl_close($ch);

echo $result;
?>