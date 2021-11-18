<?php
error_reporting(0);
if (isset($_GET['txRef'])) 
{

	//FLWPUBK-356774c41ed8b0a40c1df9e97d8f5c7b-X
	//FLWSECK-94328c08778a1588e6b5f2796322bd50-X
	//94328c08778ade8a89acb303
	
	function contains($haystack, $needle, $caseSensitive = false)
	{
		return $caseSensitive ?
            (strpos($haystack, $needle) === FALSE ? FALSE : TRUE):
            (stripos($haystack, $needle) === FALSE ? FALSE : TRUE);
	}
	
	function compareName($bankname, $regname)
	{	
		$n = 0;
		$bankname = trim(str_replace("  ", " ", $bankname));
		$bankname = trim(str_replace("   ", " ", $bankname));
		$bankname = trim(str_replace("    ", " ", $bankname));
		$regname = trim(str_replace("  ", " ", $regname));
		$regname = trim(str_replace("   ", " ", $regname));
		$regname = trim(str_replace("    ", " ", $regname));
		$bankname = strtoupper($bankname);
		$regname = strtoupper($regname);
		list($firstname, $secondname, $thirdname) = explode(' ', $regname);
		if ($firstname && preg_match("/$firstname/ui", $bankname))
		{
			$n++;
		}
		if ($secondname && preg_match("/$secondname/ui", $bankname))
		{
			$n++;
		}
		if ($thirdname && preg_match("/$thirdname/ui", $bankname))
		{
			$n++;
		}
		
		if($n > 1)
		{
			return true;
		}
		else
		{
			$m = 0;
			list($firstname2, $secondname2, $thirdname2) = explode(' ', $bankname);
			if ($firstname2 && preg_match("/$firstname2/ui", $regname))
			{
				$m++;
			}
			if ($secondname2 && preg_match("/$secondname2/ui", $regname))
			{
				$m++;
			}
			if ($thirdname2 && preg_match("/$thirdname2/ui", $regname))
			{
				$m++;
			}
			if($m > 1)
			{
				return true;
			}
		}
		
		return "";
	}
	
	
	$rubiesseckey = "SK-000087335-PROD-D6B45CD2ED7A4274A9E1321A647EE1BE4C2EE0D70EB74DB099DC82EF7AB102FE"; //your rubies 
	$rubiespubkey = "";
	$account = $_GET['account'];
	$ref = $_GET['txRef'];
	$acctname = trim($_GET['acctname']);
	$amountcheck = trim($_GET['amount']);
	
	$updatelog = $account.'.txt';
	$fg = file_get_contents($updatelog);
	if($fg){
		$ctarray = preg_split("/\r\n|\n|\r/", $fg);	
	}else{
		$ctarray = array();
	}
	$ctcount = count($ctarray);
	
	$data = array("virtualaccount" => "$account", "page" => 1);
	$data_string = json_encode($data);
			
	$curi = 'https://openapi.rubiesbank.io/v1/listtransactions';
	$ch = curl_init();	
	curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
	curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);	
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
	curl_setopt($ch, CURLOPT_HTTPHEADER, array(
		'Authorization: ' . $rubiesseckey,
		'Content-Type: application/json'
		)
	);		
	curl_setopt($ch, CURLOPT_URL, $curi);	

	$execResult = curl_exec($ch);
	$obj = json_decode($execResult, true);
	$txcode = $obj['responsecode'];
	$resmsg = $obj['responsemessage'];
	$transactions = $obj['transactions'];	
	$txcount = count($transactions);
	/* print '<pre>';
	print_r($obj);
	exit; */
	$n = 0;
	$amount = 0;
	if($txcode == '00' && $resmsg == 'successful' && $txcount > 0)
	{		
		for($i=0; $i<$txcount; $i++)
		{	
			$find = false;	
			$narration = $transactions[$i]['narration'];
			$originatorname = $transactions[$i]['originatorname'];
			$amount = floatval($transactions[$i]['amount']);
			$paymentreference = $transactions[$i]['paymentreference'];
			if(contains($narration, $ref) == true && round($amount, 2) == round($amountcheck, 2) && strlen($paymentreference) >= 30)
			{
				$n++;				
				$originatoraccountnumber = $transactions[$i]['originatoraccountnumber'];
				$bankcode = $transactions[$i]['bankcode'];
				$find = true;
				$i = $txcount;				
			}
			else if($acctname && $acctname !== '' && compareName($originatorname, $acctname) == true && round($amount, 2) == round($amountcheck, 2) && strlen($paymentreference) >= 30)
			{
				$n++;				
				$originatoraccountnumber = $transactions[$i]['originatoraccountnumber'];
				$bankcode = $transactions[$i]['bankcode'];
				$find = true;
				$i = $txcount;
			}
		}
		if($find == true)
		{
			//!in_array($paymentreference, $ctarray) &&
			$saved = 0;
			if(file_exists($updatelog))
			{
				$fg = file_get_contents($updatelog);
				$ctarray = preg_split("/\r\n|\n|\r/", $fg);					
				$ctcount = count($ctarray);	
				if($ctcount > 0)
				{
					for($j=0; $j<$ctcount; $j++)
					{
						$ctval = $ctarray[$j];
						if($ctval !== ''){
							if($ctval == $paymentreference){
								$saved = 1;
								$output = '{"amount": "'.$amount.'","clientaccount": "'.$originatoraccountnumber.'","clientbank": "'.$bankcode.'","clientname": "'.$originatorname.'","ref": "'.$ref.'","status": "0"}';
								$j = $ctcount;
							}
						}
					}
				}		
				if($saved == 0){					
					$fp = file_put_contents($updatelog, $fg."$paymentreference\n");
					$output = '{"amount": "'.$amount.'","clientaccount": "'.$originatoraccountnumber.'","clientbank": "'.$bankcode.'","clientname": "'.$originatorname.'","ref": "'.$ref.'","status": "1"}';
				}
			}
			else
			{
				$fp = file_put_contents($updatelog, "$paymentreference\n");
				$output = '{"amount": "'.$amount.'","clientaccount": "'.$originatoraccountnumber.'","clientbank": "'.$bankcode.'","clientname": "'.$originatorname.'","ref": "'.$ref.'","status": "1"}';
			}
			
		}
		else
		{
			$output = '';
		}
	}else{
		$output = '';
	}
	

	echo $output; exit;	

} else {
	die('No reference supplied');
}

?>