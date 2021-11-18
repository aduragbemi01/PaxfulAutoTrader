<?php
	//phpinfo();
	//exit;// which should i upload? this
	//run on swayhost
	//the  curl is the problem
	// i dunno why it is not been updated
	
	

	$root = $_SERVER['DOCUMENT_ROOT'];
	//include('databasefile.php');
	include('functionsfile.php');
	
	/* if(isset($_GET['tradehash'])){
		$fgc = $_GET['tradehash'];
	} */
	

	$payload = [
	    'username' => '',
		'trade_hash' => '',
		'offer_hash' => '',
		'page' => '',
		'partner' => ''
	];
	$endpoint = '/api/trade/completed';	
	$data = PAXquery($endpoint, $payload);
	$data = $data->data->trades;
	
	//FOR FEEDBACK
	
	for($i=0; $i<3; $i++)
	{
	    $msgtypearr = array();
	    $txtarr = array();
	    
	    $tradehash = $data[$i]->trade_hash;
	    $seller = $data[$i]->seller;
	    $fdmsg = $seller.' left feedback';
	    
		$payload = [
			'trade_hash' => $tradehash
		];
		
		$endpoint = '/api/trade-chat/get';	
		$chatdata = PAXquery($endpoint, $payload);
		$chatarr = $chatdata->data->messages;
		
	
		//$count =;
		
		for($j=0; $j<count($chatarr); $j++)
		{
		    $author = ''; $msg = ''; $text = '';
		    $msg = $chatarr[$j]->type;
		    $author = $chatarr[$j]->author;
		    $text = $chatarr[$j]->text;
		    array_push($msgtypearr, $msg);
		    if($author == $owneruser){
		        array_push($txtarr, $text);
		    }else if($msg == $feedbackreceived){
		        array_push($txtarr, $text);
		    }
		    
		}
		
		if(in_array($fdmsg, $txtarr))
		{
		    
		    $postmessage = <<<EOS
Smart Trader 👍
EOS;
			if(!in_array($postmessage, $txtarr))
			{
				$payload = [
					'trade_hash' => $tradehash,
					'message' => $postmessage
				];
							
				$endpoint = '/api/trade-chat/post';	
				$data = PAXquery($endpoint, $payload);
				
				$payload = [
					'trade_hash' => $tradehash,
					'message' => $postmessage,
					'rating' => 1
				];
				
				$endpoint = '/api/feedback/give';	
				$data = PAXquery($endpoint, $payload);
				
			}	
		}
	}
	
	
	/*
	
	if(file_exists('./'.$offerhash1.'.txt')){
		unlink('./'.$offerhash1.'.txt');
		$fpc = file_put_contents('./'.$offerhash2.'.txt', "");
		$offerhash = $offerhash1;
	}
	else if(file_exists('./'.$offerhash2.'.txt')){
		unlink('./'.$offerhash2.'.txt');
		$fpc = file_put_contents('./'.$offerhash3.'.txt', "");
		$offerhash = $offerhash2;
	}
	else if(file_exists('./'.$offerhash3.'.txt')){
		unlink('./'.$offerhash3.'.txt');
		$fpc = file_put_contents('./'.$offerhash4.'.txt', "");
		$offerhash = $offerhash3;
	}
	else if(file_exists('./'.$offerhash4.'.txt')){
		unlink('./'.$offerhash4.'.txt');
		$fpc = file_put_contents('./'.$offerhash5.'.txt', "");
		$offerhash = $offerhash4;
	}
	else if(file_exists('./'.$offerhash5.'.txt')){
		unlink('./'.$offerhash5.'.txt');
		$fpc = file_put_contents('./'.$offerhash6.'.txt', "");
		$offerhash = $offerhash5;
	}
	else if(file_exists('./'.$offerhash6.'.txt')){
		unlink('./'.$offerhash6.'.txt');
		$fpc = file_put_contents('./'.$offerhash1.'.txt', "");
		$offerhash = $offerhash6;
	}
	else{
		$fpc = file_put_contents('./'.$offerhash2.'.txt', "");
		$offerhash = $offerhash1;
	}
	*/
	
	//UPDATE LAST SEEN	
	$payload = [
		'trade_hash' => ''
	];
	
	$endpoint = '/api/user/touch';	
	$data1 = PAXquery($endpoint, $payload);
	$update = '';
	
	/* 
	if(!$fgc){
		$fgc = file_get_contents('./rateoffer.txt');
	}
	
	if($fgc)
	{
	
		//GET RATE
		$payload = [
			'trade_hash' => ''
		];
		
		$endpoint = '/api/currency/rates';	
		$data = PAXquery($endpoint, $payload);	
	
		$offercode = $offertradecodearr[$fgc];
		$offercur = $offertradecurarr[$fgc];
		$btsellrate = $offertraderatearr[$fgc];
		
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
			$update = 'success ';
			$index = array_search($fgc, $offerarr);		
			if($index < count($offerarr)){				
				if($index == count($offerarr) - 1){
					$fpc = file_put_contents('./rateoffer.txt', $offerarr[0]);				
				}else{
					$fpc = file_put_contents('./rateoffer.txt', $offerarr[$index + 1]);
				}
			}
		}
	}
	
	*/
	
	//GET OPEN TRADES HASH			
	$payload = [
		'trade_hash' => ''
	];

	$endpoint = '/api/trade/list';	
	$data = PAXquery($endpoint, $payload);
	$tradeArr = $data->data->trades;	
	$countTrade = count($tradeArr);	
	$allArr = array();
	$allArr2 = array();
	
	
				
	if($countTrade > 0)
	{
	
		for($i=0; $i<$countTrade; $i++)
		{
			
			$ownerMsgArr = array();
			$sellerArr = array();
			$msgtypeArr = array();
			$seen = '0';
			$canceltrade = '0';
			$incompleteacctname = '0';
			$tradestatus = $tradeArr[$i]->trade_status;
			$tradehash = $tradeArr[$i]->trade_hash; //tradehash to contact				
			$owneruser = $tradeArr[$i]->owner_username; //tradeuser to contact
			$tradeuser = $tradeArr[$i]->responder_username; //tradeuser to contact
			$offertype = $tradeArr[$i]->offer_type; //buy [when someoneselling to me]
			$offerhash = $tradeArr[$i]->offer_hash;
			$offerlink = 'https://paxful.com/offer/'.$offerhash;
			$fiatamount = $tradeArr[$i]->fiat_amount_requested;
			$offercur = $tradeArr[$i]->fiat_currency_code;
			$offerpaymethod = $tradeArr[$i]->payment_method_slug;
			$requestedamount = $tradeArr[$i]->crypto_amount_requested;
			$escrowfee = $tradeArr[$i]->fee_crypto_amount;
			$escrowamt = ''.round((($requestedamount + $escrowfee) / 100000000), 8);
			
			
			if($offertype && $tradestatus !== 'Dispute open'){
			
				if($offertype == 'buy' && $owneruser == $owneruser2)
				{		
					array_push($allArr, $tradehash);
				}
				if($offertype == 'sell' && $owneruser == $owneruser2)
				{		
					array_push($allArr2, $tradehash);
				}
			}
		}
	}
	
	$availtradecount = count($allArr);
		
	if($availtradecount > 0)
	{		
		for($z=0; $z<$availtradecount; $z++)
		{				
			$tradehash = $allArr[$z];
			if(trim($tradehash) !== '' && $offercur == 'NGN')
			{	
				$exec = getURL($tradehash, 'buy');
			}
		}			
	}
	
	$availtradecount = count($allArr2);
		
	if($availtradecount > 0)
	{
		for($z=0; $z<$availtradecount; $z++)
		{				
			$tradehash = $allArr2[$z];
			if(trim($tradehash) !== '' && $offercur == 'NGN')
			{	
				$exec = getURL($tradehash, 'sell');
			}
		}			
	}
		
	
	//echo $update.$fgc." ".$ngnusd." ".$ngnbtc." ".$margin;
	
	include('inflateoffer.php');
		
	

?>