<?php
$body = file_get_contents('php://input');	
http_response_code(200);

$fb = json_decode($body, true);

$file = './hookrubies.txt';

if(file_exists($file)){
	$prev = file_get_contents($file);
	$try = file_put_contents($file, $prev."$body\n\n");				
}else{
	$try = file_put_contents($file, "$body\n\n");
}
?>