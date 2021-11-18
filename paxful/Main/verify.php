<?php

$curl = curl_init();

curl_setopt_array($curl, array(
  CURLOPT_URL => "https://openapi.rubiesbank.io/v1/verifymobilenumber",
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => "",
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 0,
  CURLOPT_FOLLOWLOCATION => true,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => "POST",
  CURLOPT_POSTFIELDS =>"{\n\t\"mobilenumber\":\"07056940715\",\n\t\"reference\":\"w8urLtmv24Z-4\"\n}",
  CURLOPT_HTTPHEADER => array(
    "Authorization: "// RUBIES AUTHORIZATION KEY HERE
  ),
));

$response = curl_exec($curl);

curl_close($curl);
echo $response;


