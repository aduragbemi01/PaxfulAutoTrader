<?php

$curl = curl_init();

curl_setopt_array($curl, array(
  CURLOPT_URL => "https://openapi.rubiesbank.io/v1/createvirtualaccount",
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => "",
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 0,
  CURLOPT_FOLLOWLOCATION => true,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => "POST",
  CURLOPT_POSTFIELDS =>"{\n    \"virtualaccountname\": \"ADEOYE GOODNESS\",\n    \"amount\": \"1\",\n    \"amountcontrol\": \"VARIABLEAMOUNT\",\n    \"daysactive\": 2,\n    \"minutesactive\": 30,\n    \"callbackurl\": \"https://swayhost.com/checkaccthook.php\",\n    \"singleuse\":\"Y\"\n}",
  CURLOPT_HTTPHEADER => array(
    "Authorization: ", //RUBIES AUTHORIZATION KEY HERE
    "Content-Type: application/json"
  ),
));

$response = curl_exec($curl);

curl_close($curl);
echo $response;

