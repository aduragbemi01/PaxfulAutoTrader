<?php
	/* error_reporting(0);
	$root = $_SERVER['DOCUMENT_ROOT'];
	include('functionsfile.php'); */

	$file = './updateoffer.txt';
	$fg = file_get_contents($file);
	$ctarray = preg_split("/\r\n|\n|\r/", $fg);	
	$ctcount = count($ctarray);
	
	
	
	if($ctcount > 0){
		list($fgc, $previoustime) = explode('-', $ctarray[0]);
		$timestamp = time();
		$timerange = $timestamp - $previoustime;
		if($timerange < 300){
			die('Time never reach');
		}
		
		$btsellrate = $offertraderatearr2[$fgc];
		
		//GET RATE
		$payload = [
			'trade_hash' => ''
		];
		
		$endpoint = '/api/currency/rates';	
		$data = PAXquery($endpoint, $payload);	

		$key = array_search($fgc, $offerarr2);
		$i = 0;			
		foreach ($offertraderatearr as $key2 => $value)
		{				
			if($i == $key){
				$offercode = $offertradecodearr[$key2];
				$offercur = $offertradecurarr[$key2];
			}
			$i++;
		}
					
		
		$xcode = (string) $data->data->$offercode->code;
		if($xcode == $offercur){
			$ngnusd = $data->data->$offercode->rate_USD;
			$ngnbtc = $data->data->$offercode->rate_BTC;
		}else{
			$x = $data->data;
			$xcount = count($x);
			for($i=0; $i<$xcount; $i++)
			{
				if(trim($x[$i]->code) == $offercur)
				{
					$xcode = $offercur;
					$ngnusd = trim($x[$i]->rate_USD);
					$ngnbtc = trim($x[$i]->rate_BTC);
					$i = $xcount;
				}
			}
		}
		
		$margin = round(((($btsellrate - $ngnusd) / $ngnusd) * 100), 2);
		
		
		if($margin < -10){$margin=-10;}
		
		/* 
		print_r($margin);
		exit(); */
		
		//UPDATE RATES	
		
		$payload = [
			'offer_hash' => $fgc,
			'margin' => $margin
		];

		$endpoint = '/api/offer/update-margin';	
		$data = PAXquery($endpoint, $payload);	
		
		$update = 'failed';	
		if($data->data->success == 1)
		{
			$update = 'success';
			$chatarray = preg_split("/\r\n|\n|\r/", $fg);
			$chatcount = count($chatarray);
			$line = "";
			for($i=0; $i<$chatcount; $i++){
				$dvalue = $fgc.'-'.$previoustime;
				if($chatarray[$i] == $dvalue || trim($chatarray[$i]) == ''){
					$line .= '';
				}else{
					$line .= "$chatarray[$i]\n";
				}			
			}
			file_put_contents($file, "$line");					
		}
		echo $fgc.' '.$update;
	
	}
	

?>
