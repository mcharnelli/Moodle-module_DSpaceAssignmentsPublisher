<?php

$ch = curl_init($_POST["url"] . "/rest/collections/"); 

curl_setopt($ch, CURLOPT_HEADER, 0); 
$output = curl_exec($ch); 
curl_close($ch); 
json_encode($output);