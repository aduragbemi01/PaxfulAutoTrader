<?php
	

	$root = $_SERVER['DOCUMENT_ROOT'];
	//include('databasefile.php');
	include('functionsfile.php');

	$file = './alloffers.txt';
	$fg = file_get_contents($file);
	//$ctarray = preg_split("/\r\n|\n|\r/", $fg);	
	//$ctarray = array("KycYDXQvmW9");
	
	//$ctcount = count($ctarray);
	 
			$payload = [
				'offer_hash' => ''
			];
	
			//$endpoint = '/api/payment-method/list';
			$endpoint = '/api/offer/list';
			//$endpoint = '/api/offer/delete';
			
			
			
			$data = PAXquery($endpoint, $payload); 
			
			
			
			$datas=$data->data->offers[0];
			$payload = [];
			/* $names = array('offer_type','currency_code','payment_method_slug','margin','fiat_amount_range_min','fiat_amount_range_max','payment_window','payment_method_label','payment_method_group','offer_terms','trade_details','country_limitation_type','country_limitation_list','tags'); */
			
			$names = array('offer_type','currency_code','payment_method_slug','margin','fiat_amount_range_min','fiat_amount_range_max','payment_window','payment_method_label','payment_method_group','offer_terms','trade_details','country_limitation_type','country_limitation_list','tags');
						
			
									
				foreach ($datas as $k => $v) {
					
					$index = array_search($k,$names);
					if (in_array($k, $names, TRUE)){
					//echo $k." <br/> ";
					
					if($k != "offer_type")
					{
						if($k != "currency_code")
						
						{
							if($k != "offer_terms"){
								
										if($k != "payment_method_slug")
										{
											
									if($k != "fiat_amount_range_min")
									{
										if($k != "fiat_amount_range_max")
										{
											//echo $k." <br/>";
											
											if(is_array($v) == '1'){								
											$payload[$k] = json_encode($v);
											
											}
											else {
												
													$payload[$k] = $v;
												
											
												}
										}
										
									else	{ $payload["range_max"]= $v;  }
									}
									
					else	{ $payload["range_min"]= $v;   }
										}
										
					else { $payload["payment_method"]= "international-wire-transfer-swift";  }
									
							}
							
					else { $payload["trade_details"]= $v; $payload["offer_terms"]= $v;  }
						}
						
					else {  $payload["currency"]= $v; }
					
					}
					else { $payload["offer_type_field"]= "buy"; }
					
					
					
					
/* western-union
bank-transfer
perfect-money
bitcoin-cash-bccbch
cash-deposit-to-bank
domestic-wire-transfer
tether-usdt
ethereum-eth
international-wire-transfer-swift */
					
					
					
				
				
				
				
				
				
				/* if($k == "offer_type"){ $payload["offer_type_field"] = $v;}
				else{$payload[$k] = $v;}
				if($k == "currency_code"){ $payload["currency"]= $v;}
				else{$payload[$k] = $v;}
				if($k == "offer_terms"){ $payload["trade_details"]= $v;}
				else{$payload[$k] = $v;}
				if($k == "payment_method_slug"){ $payload["payment_method"]= $v;}
				else{$payload[$k] = $v;}
				if($k == "fiat_amount_range_min"){ $payload["range_min"]= $v;}
				else{$payload[$k] = $v;}
				if($k == "fiat_amount_range_max"){ $payload["range_max"]= $v;}
				else{$payload[$k] = $v;} */
				}
			
			}
			/* 
			print_r($payload);
			exit(); */
			
			//$data = json_encode($data->data->offers[0]->country_limitation_list);
			//print_r($data);
			//print_r($data->data->offers[0]);
			//print_r($ctcount);
			
			
			/* $payload = [
				'offer_type_field' => $data->data->offers[0]->offer_type_field,
				'currency' => $data->data->offers[0]->currency,
				'payment_method' => $data->data->offers[0]->payment_method,
				'margin' => $data->data->offers[0]->margin,
				'range_min' => $data->data->offers[0]->range_min,
				'range_max' => $data->data->offers[0]->range_max,
				'payment_window' => $data->data->offers[0]->payment_window,
				'payment_method_label' => $data->data->offers[0]->payment_method_label,
				'payment_method_group' => $data->data->offers[0]->payment_method_group,
				'offer_terms' => $data->data->offers[0]->offer_terms,
				'trade_details' => $data->data->offers[0]->trade_details,
				'country_limitation_type' => $data->data->offers[0]->country_limitation_type,
				'country_limitation_list' => $data->data->offers[0]->country_limitation_list,
				'tags' => $data->data->offers[0]->tags
			]; */
	
			$endpoint = '/api/offer/create';	
			$data = PAXquery($endpoint, $payload);
			
			
			print_r($data);
			exit();
			
			
			
			
			
	
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