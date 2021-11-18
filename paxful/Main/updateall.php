<?php
	

	$root = $_SERVER['DOCUMENT_ROOT'];
	//include('databasefile.php');
	include('./functionsfiles.php');

	$file = './alloffers.txt';
	$fg = file_get_contents($file);
	//$ctarray = preg_split("/\r\n|\n|\r/", $fg);	
	$ctarray = array("1m72RkcbuzN","i8enLuaLiB7","izdRjmvVjGJ","tTexRdFJtkA");
	
	$ctcount = count($ctarray);
	
	for($i=0; $i<$ctcount; $i++)
	{$update = 'failed';	
	
		$fgc = $ctarray[$i];		
		if($fgc !== '')
		{		
			//GET RATE
			$payload = [
				'trade_hash' => ''
			];
			
			$endpoint = '/api/currency/rates';	
			$data = PAXquery($endpoint, $payload);
			
			

			if(in_array($fgc, $offerarr))
			{
				$offercode = $offertradecodearr[$fgc];
				$offercur = $offertradecurarr[$fgc];
				$btsellrate = $offertraderatearr[$fgc];
			}
			else if(in_array($fgc, $offerarr2))
			{
				$offercode = $offertradecodearr2[$fgc];
				$offercur = $offertradecurarr2[$fgc];
				$btsellrate = $offertraderatearr2[$fgc];
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
			
			
			//UPDATE RATES	
		
			$payload = [
				'offer_hash' => $fgc,
				'margin' => $margin
			];

			$endpoint = '/api/offer/update-margin';	
			$data = PAXquery($endpoint, $payload);	
			
			
			
			if($data->data->success == 1){
				$update = 'success';			
			}
			echo $fgc.' - '.$btsellrate.' - '.$update.'<br>';
		}
	}
	
	
?>