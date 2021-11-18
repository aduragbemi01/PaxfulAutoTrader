<?php

$curl = curl_init();

curl_setopt_array($curl, array(
  CURLOPT_URL => "https://openapi.rubiesbank.io/v1/balanceenquiry",
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => "",
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 0,
  CURLOPT_FOLLOWLOCATION => true,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => "POST",
  CURLOPT_POSTFIELDS =>"{\n\t\t\"accountnumber\":\"0005032369\"\n}",
  CURLOPT_HTTPHEADER => array(
    "Authorization: ", //RUBIES AUTHORIZATION KEY HERE
    "Content-Type: application/json"
  ),
));

$response = curl_exec($curl);

curl_close($curl);
//echo $response;
$resp = json_decode($response, true);

if($resp['accountname'] && $resp['responsemessage'] == "success" && $resp['balance'] !== ''){
		$balance= floatval($resp['balance']) * 100; //[In kobo]
	}else{
		echo "";
	}
	
	


?>