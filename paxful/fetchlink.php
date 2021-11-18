<?php
$urllist = array(
				"https://swayhost.com/paxful/MY/C4qSvy5wNwp/checkopentrade.php", 
				"https://swayhost.com/paxful/MY/CZMNA3bhHdu/checkopentrade.php", 
				"https://swayhost.com/paxful/MY/KXuEbYz9HnE/checkopentrade.php"
				);
						
$linknum = file_get_contents('./no.txt'); //must be integer
$url = $urllist["$linknum"]; //Fetch the url from Array

	$ch = curl_init($url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
	$fetch = curl_exec($ch);
	
	echo $fetch;

//Write back next Link ID to execute
if($linknum < count($urllist)) {
								$linknum++;
								unlink("./no.txt");// 
								if($linknum == count($urllist)) { 
								$file = fopen("./no.txt","w");
								fwrite($file,0);
								fclose($file);
								}		
		else
			{	$file = fopen("./no.txt", "w");
				fwrite($file,$linknum);
				fclose($file);
				exit();
				}
}
?>