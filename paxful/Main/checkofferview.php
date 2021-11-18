<?php

	//error_reporting(0);
	$root = $_SERVER['DOCUMENT_ROOT'];
	include('functionsfile.php');	
	
	$fgc = "";	
	if(isset($_GET['tradehash'])){
		$fgc = $_GET['tradehash'];  //offerhash
		$timestamp = time();
	} 
	
	$updatelog = 'updateoffer.txt';
	
	$isLow = 0;
		
	
	if(in_array($fgc, $offerarr2) && $fgc != '')
	{
		$payload = [
			'offer_hash' => $fgc
		];
		
		$endpoint = '/api/offer/get';	
		$data = PAXquery($endpoint, $payload);
		
		$ngnPrice = $data->data->fiat_price_per_btc;
		$isActive = $data->data->active;
		$offerCurrency = $data->data->fiat_currency_code;
		$rateCheck = round(($ngnPrice * $usdbtc), 2);
		$ngnPrice = round($ngnPrice, 2);
		$key = array_search($fgc, $offerarr2);
		
		/*
		if($isActive == 1 && $rateCheck <= $offercurarr[$offerCurrency]){
			//ALREADY REDUCED RATE TO INCREASE
			$btsellrate = $offertraderatearr2[$fgc];
			$isHigh = 1;
			
		}
		*/
		//die($offercurarr[$offerCurrency].' '.$rateCheck.' '.$isActive);
		if($isActive == 1 && $rateCheck > $offercurarr[$offerCurrency]){
			//HIGH RATE TO BE LOWERED	
			$i = 0;			
			foreach ($offertraderatearr as $key2 => $value)
			{				
				if($i == $key){
					$btsellrate = $value;
					$offercode = $offertradecodearr[$key2];
					$offercur = $offertradecurarr[$key2];
					$isLow = 1; //to be  increased in 2mins
				}
				$i++;
			}				
		}		
	}
	
	if($isLow == 1){
		//GET RATE
		$payload = [
			'trade_hash' => ''
		];
		
		$endpoint = '/api/currency/rates';	
		$data = PAXquery($endpoint, $payload);	
		
			
		
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
		
		
		if($margin < -10){$margin = -10;}
		
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
			if(file_exists($updatelog)){
				$fg = file_get_contents($updatelog);
				$ctarray = preg_split("/\r\n|\n|\r/", $fg);
				if(!in_array($fgc, $ctarray)){			
					$fp = file_put_contents($updatelog, $fg."$fgc-$timestamp\n");
					
				}
				
			}else{
				$fp = file_put_contents($updatelog, "$fgc-$timestamp\n");
				
			}						
		}
		echo $update;
	
	}
	echo ' isLow: '.$isLow.' '.$margin;


	/* 
	stdClass Object
	(
		[status] => success
		[timestamp] => 1605032075
		[data] => stdClass Object
			(
				[offer_hash] => sXYaEX7ntwN
				[success] => 1
			)

	)

	//ONE MOMENT PLS
	
	$payload = [
		'offer_type_field' => 'buy',
		'currency' => 'NGN',
		'payment_method' => 'bank-transfer',
		'margin' => 20,
		'range_min' => 5000,
		'range_max' => 3900000,
		'payment_window' => 90,
		'payment_method_label' => 'All Nigerian Banks',
		'payment_method_group' => 'bank-transfers',
		'offer_terms' => 'Drop Bank info /nWait online to release btc',
		'trade_details' => 'Drop Bank info /nWait online to release btc',
		'country_limitation_type' => 'allowed',		
		'country_limitation_list' => 'US,AU,CA,GB,CY,DE,GH,IN,ID,JP,KE,KR,MY,MX,NG,PH,ZA,TR,AE',
		'tags' => 'no-negotiation,online-payments,no-verification-needed'
	];
	$endpoint = '/api/offer/create';	
	$data = PAXquery($endpoint, $payload);
	print '<pre>';
	//print $offerCurrency.' '.$ngnPrice.' '.$isActive.' <br/>';
	print_r($data);
	exit;
	
	*/
	
?>