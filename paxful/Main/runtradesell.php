<?php

	error_reporting(0);
	
	if(isset($_GET['tradehash'])){
		$tradehash = $_GET['tradehash'];
	}else{
		die('Trade Hash is required');
	}
	
	include('functionsfile.php');
	
	
	//GET OPEN TRADES HASH	
	$payload = [
		'trade_hash' => $tradehash
	];
	
	$endpoint = '/api/trade/get';	
	$data = PAXquery($endpoint, $payload);
	
	$allArr = array();	
	$ownerMsgArr = array();
	$buyerArr = array();
	$msgtypeArr = array();
	$seen = '0';
	$canceltrade = '0';
	$disputetrade = '0';
	$incompleteacctname = '0';
	$bvalid = '0';
	$bquest = '0';
	$tarr = $data;
	$tradearr = $data->data->trade; 
	$tradestatus = $tradearr->trade_status; 
	$tradeuser = $tradearr->buyer_name; //tradeuser to contact 
	$tradeuserpaxname = $tradearr->buyer_full_name->first_name.' '.$tradearr->buyer_full_name->last_name; 
	$owneruser = $tradearr->seller_name; //offer owner
	$offertype = $tradearr->offer_type; //sell [when someonebuying from me]
	$offerhash = $tradearr->offer_hash;
	$offerlink = 'https://paxful.com/offer/'.$offerhash;
	$fiatamount = $tradearr->fiat_amount_requested;
	$requestedamount = $tradearr->crypto_amount_requested;
	$requestedtotal = $tradearr->crypto_amount_total;
	$requestedfee = $tradearr->fee_crypto_amount;
	$offercur = $tradearr->fiat_currency_code;
	$offerpaymethod = $tradearr->payment_method_slug;	
	$allowed = 60 * (floatval($tradearr->payment_window) - 6);	
	
	
	if(!$offertype)
	{
		$dstatus = $tarr->status;
		$dcode = $tarr->error->code;
		$dmessage = $tarr->error->message;
		
		if($dstatus == 'error' && $dcode == '429' && $dmessage == 'Rate limit exceeded')
		{
			$logfile = $root.'/'.$logfail1;
			if(file_exists($logfile)){
				$fgc = file_get_contents($logfile);
				$fpc = file_put_contents($logfile, $fgc.gmdate("d.m.Y h:i:s a", time()+$offset)."; $tradehash $dmessage\n");
			}else{
				$fpc = file_put_contents($logfile, gmdate("d.m.Y h:i:s a", time()+$offset)."; $tradehash $dmessage\n");
			}
			die($dmessage);
		}
	}
	
	
	if($offertype == 'sell' && $owneruser == $owneruser2)
	{
		$prevsaved = 0;	
		$logfile = './successsell.txt';
		$destlogfile = './successsell'.time().'.txt';
		$fgc = file_get_contents($logfile);
		$tradedarr = preg_split("/\r\n|\n|\r/", $fgc);
		$tradedarrct = count($tradedarr);
		
		//RESAVE SUCCESS TXT
		if($tradedarrct >= 101)
		{
			rename($logfile, $destlogfile);
			file_put_contents($logfile, "");
			die('Renamed Successtxt');
		}
		
		$hit = 0;
		for($i=0; $i<$tradedarrct; $i++){
			$thisline = $tradedarr[$i];
			list($thishash, $thishash1, $thishash2) = explode(' ', $thisline);
			$pstatus = $thishash1.' '.$thishash2;
			if($pstatus == 'NOT PAID' && $thishash == $tradehash)
			{
				$prevsaved = 0;
				$hit = 1;
			}
			else if($thishash == $tradehash)
			{
				$prevsaved = 1;
				$i = $tradedarrct;
			}
		}
		
		
		if($prevsaved == 0)
		{
			$requestedamountbtc = round(($requestedamount / 100000000), 8);	
			$requestedamountusd = round(($requestedamountbtc / $usdbtc), 2);
			$traderate = round(($fiatamount / $requestedamountusd), 2);
			
			if($traderate < 520)
			{
				//STOP TRADE WHEN TRADE RATE IS LOWER
				array_push($paxblockArr, $tradeuser);			
				$sumlog = $tradehash.' NOT PAID $'.$requestedamountusd.' @'.$traderate.' = '.$offercur.$fiatamount.' '.$requestedamountbtc.' BTC - '.$offerpaymethod.' [$'.round((1/$usdbtc), 2).']| '.gmdate("d.m.Y h:i:s a", time()+3600);				
			}
			else
			{
				$sumlog = $tradehash.' $'.$requestedamountusd.' @'.$traderate.' = '.$offercur.$fiatamount.' '.$requestedamountbtc.' BTC - '.$offerpaymethod.' [$'.round((1/$usdbtc), 2).']| '.gmdate("d.m.Y h:i:s a", time()+3600);				
			}
			
			if($hit == 0)
			{				
				if(file_exists($logfile)){
					$fgc = file_get_contents($logfile);
					$fpc = file_put_contents($logfile, $fgc."$sumlog\n");
				}else{
					$fpc = file_put_contents($logfile, "$sumlog\n");
				}
				
			}			
		}
		
		
		if($tradestatus == 'Released')
		{
			$rating = preg_replace('#[^0-1-]#i', '', $_GET['rating']);
			if(trim($tradehash) !== '')
			{			
				
				$payload = [
					'trade_hash' => $tradehash
				];
				
				$endpoint = '/api/trade-chat/get';	
				$chatdata = PAXquery($endpoint, $payload);
				
				$tradechatArr = $chatdata->data->messages;					
				$countTradechat = count($tradechatArr);
				
				for($j=0; $j<$countTradechat; $j++)
				{
					$thismsgtype = $tradechatArr[$j]->type;	
					$thisauthor = $tradechatArr[$j]->author;
					if($thisauthor == $owneruser && $thismsgtype == 'msg')
					{
						$text = $tradechatArr[$j]->text;
						array_push($ownerMsgArr, $text);
					}
					array_push($msgtypeArr, trim($thismsgtype));
				}			
				if(in_array($released, $msgtypeArr) && in_array($feedbackreceived, $msgtypeArr))
				{
		
$postmessage = <<<EOS
👍
EOS;
				
					$payload = [
						'trade_hash' => $tradehash,
						'message' => $postmessage,
						'rating' => $rating
					];
					
					$endpoint = '/api/feedback/give';	
					$data = PAXquery($endpoint, $payload);
					
				}
			
			}
			die('Success');
			
		}
		
		
		if($tradestatus !== 'Released')
		{
			/// RESPOND TO CHAT	

			$payload = [
				'trade_hash' => $tradehash
			];
			
			$endpoint = '/api/trade-chat/get';	
			$chatdata = PAXquery($endpoint, $payload);			
			
			$tradechatArr = $chatdata->data->messages;
			$countTradechat = count($tradechatArr);
			
			$tradebegin = $tradechatArr[0]->timestamp;						
			$thistime = time();	
			$tradeperiod = $thistime - $tradebegin;
			if($tradeperiod > $allowed){
				$canceltrade = '1';
			}
			
			for($j=0; $j<$countTradechat; $j++)
			{				
				$thisauthor = $tradechatArr[$j]->author;						
				$thismsgtype = $tradechatArr[$j]->type;					
				array_push($msgtypeArr, trim($thismsgtype));						
				
				if($tradeuser == $thisauthor && $thismsgtype == 'msg')
				{
					$text = $tradechatArr[$j]->text;
					$text = trim(str_replace(["\r", "\n", "\t", ":", "\\", "/", "*", ",", ".", "[", "]", "(", ")", "{", "}", "#", "!", "`", "'", ";", "<", ">", "|", "^", "&", "%", "@", "$"], " ", $text));							
					array_push($buyerArr, trim($text));					
				}
				else if($owneruser == $thisauthor && $thismsgtype == 'msg')
				{
					$text = $tradechatArr[$j]->text;
					array_push($ownerMsgArr, $text);
				}
				else if($tradeuser == $thisauthor && $thismsgtype == 'bank-account')
				{
					$pcurrency = $tradechatArr[$j]->text->$thismsgtype->currency;
					if($pcurrency == 'NGN')
					{
						$accountnumber = $tradechatArr[$j]->text->$thismsgtype->accountNumber;	
						$acctcode = $tradechatArr[$j]->text->$thismsgtype->bankName;
						$acctname = $tradechatArr[$j]->text->$thismsgtype->holderName;
						$text = $accountnumber.' '.$acctcode.' '.$acctname;
						array_push($buyerArr, trim($text));
						$bquest = '1';										
					}				
				}
				else if(trim($thismsgtype) == $paid)
				{
					$trademarktime = $tradechatArr[$j]->timestamp;
					$tradedispute = $thistime - $trademarktime;
					if($tradedispute > 1800){
						$disputetrade = '1';
					}
				}			
			}			
		}
		
		
		if($tradestatus !== 'Released' && !in_array($paid, $msgtypeArr) && trim($tradeuserpaxname) == '')
		{
			$postmessage = <<<EOS
Boss I don't sell to unverified users on paxful
EOS;
			
			if(!in_array($postmessage, $ownerMsgArr))
			{
				$payload = [
					'trade_hash' => $tradehash,
					'message' => $postmessage
				];
				
				$endpoint = '/api/trade-chat/post';	
				$data = PAXquery($endpoint, $payload);				
			}
			die('Not Qualified');
		}
		
		if($tradestatus !== 'Released' && !in_array($paid, $msgtypeArr) && trim($tradeuserpaxname) !== '')
		{	
			//currently not in use
			$postmessage1 = <<<EOS
Please don't send payment if your bank account name is NOT [$tradeuserpaxname]
EOS;

			//USSD WILL NOT CAPTURE REMARK
			$postmessage = <<<EOS
Which bank account will you be sending from?
Pay to the Bank details below [Bank Transfer ONLY]. DO NOT use USSD Phone Transfer.
Provide screenshot once done.
EOS;
			$postmessage2 = <<<EOS
Bank Name: RUBIES MICROFINANCE BANK
Bank Account Name: RUBYPAY-ROTIMI STEPHEN AKANMU
Bank Account Number: $rubiespaxacct
Amount: ₦$fiatamount 
Remark/Narration: $tradehash

NOTE: PUT ONLY THE REMARK "$tradehash" WHEN YOU'RE SENDING
EOS;

			if(!in_array($postmessage, $ownerMsgArr))
			{
				$payload = [
					'trade_hash' => $tradehash,
					'message' => $postmessage
				];
				
				$endpoint = '/api/trade-chat/post';	
				$data = PAXquery($endpoint, $payload);

				$payload = [
					'trade_hash' => $tradehash,
					'message' => $postmessage2
				];
				
				$endpoint = '/api/trade-chat/post';	
				$data = PAXquery($endpoint, $payload);
				
			}
			die('Bank Details Sent');
		}
		
		if($tradestatus !== 'Released' && in_array($paid, $msgtypeArr))
		{
			//NOT IN USE
			$postmessage1 = <<<EOS
Please don't send payment if your bank account name is NOT [$tradeuserpaxname]
EOS;

			$postmessage = <<<EOS
Which bank account will you be sending from?
Pay to the Bank details below [Bank Transfer ONLY]. DO NOT use USSD Phone Transfer.
Provide screenshot once done.
EOS;

			//START DISPUTE MSG reupload
			$postmessage2 = <<<EOS
Locking coin for no reason, even when no account was issued?
EOS;
			
			if(!in_array($postmessage, $ownerMsgArr) && $disputetrade == '1' && trim($tradestatus) !== 'Dispute open')
			{
				$payload = [
					'trade_hash' => $tradehash,
					'reason' => $postmessage2,
					'reason_type' => 'vendor_coinlocker'
				];
				
				$endpoint = '/api/trade/dispute';	
				$data = PAXquery($endpoint, $payload);			
				die('Coin Locker');
			}
			else if(trim($tradestatus) == 'Dispute open')
			{
				die('Dispute Open');
			}	
			else if($tradeperiod > 1800)
			{
				//die('Time Out');
			}	
			
			
			// This runs only when mark paid
			if(in_array($postmessage, $ownerMsgArr))
			{
				$regname = preg_replace('/\s+/','+',$tradeuserpaxname);				
				$curl = 'http://139.162.89.151/paxful/Main2/checkrubiespayment.php?txRef=' . $tradehash . '&account=' . $rubiespaxacct . '&acctname=' . $regname . '&amount=' . $fiatamount;
				//upload this now
				
				$ch = curl_init();
				curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
				curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
				curl_setopt($ch, CURLOPT_URL, $curl);
				$ccc = trim(curl_exec($ch));
				curl_close($ch);
				$json = json_decode($ccc, true);
				$ch = curl_init();
				curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
				curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
				curl_setopt($ch, CURLOPT_URL, $curl);
				$ccc = trim(curl_exec($ch));
				curl_close($ch);
				$json = json_decode($ccc, true);			
				
				$fetch_clientbank = $json['clientbank']; 
				$fetch_clientaccount = $json['clientaccount']; 
				$fetch_clientname = ucwords(strtolower(trim($json['clientname']))); 
				$fetch_amount = floatval($json['amount']);
				$fetch_tranxref = $json['ref']; 
				$fetch_status = $json['status']; 
				$fiatamount = floatval($fiatamount);
				
				if($fetch_amount > 0)
				{	
					$compareName = compareName($fetch_clientname, $tradeuserpaxname);
					//if($compareName == true && $fetch_amount >= $fiatamount)
					if($fetch_amount >= $fiatamount)
					{
						$postmessage = <<<EOS
Leave positive feedback, I will do the same 
EOS;

						if(!in_array($postmessage, $ownerMsgArr))
						{
							$payload = [
								'trade_hash' => $tradehash
							];
							
							$endpoint = '/api/trade/release';	
							$data = PAXquery($endpoint, $payload);	
							
							$payload = [
								'trade_hash' => $tradehash,
								'message' => $postmessage
							];
							
							$endpoint = '/api/trade-chat/post';	
							$data = PAXquery($endpoint, $payload);					
						}
						die('Feedback Request');
					}
					
					if($compareName == true && $fetch_amount < $fiatamount)
					{
						$postmessage = <<<EOS
₦$fetch_amount received, instead of ₦$fiatamount
EOS;

						if(!in_array($postmessage, $ownerMsgArr))
						{
							$payload = [
								'trade_hash' => $tradehash,
								'message' => $postmessage
							];
							
							$endpoint = '/api/trade-chat/post';	
							$data = PAXquery($endpoint, $payload);					
						}
						die('Incomplete Payment');
					}
					
					if($compareName == false && $fetch_amount >= $fiatamount)
					{
						$postmessage = <<<EOS
Payment received, but bank account name does not match paxful registered account name
EOS;

						if(!in_array($postmessage, $ownerMsgArr))
						{
							$payload = [
								'trade_hash' => $tradehash,
								'message' => $postmessage
							];
							
							$endpoint = '/api/trade-chat/post';	
							$data = PAXquery($endpoint, $payload);					
						}
						die('Full Payment, Name Mismatch');
					}
					
					if($compareName == false && $fetch_amount < $fiatamount)
					{
						$postmessage = <<<EOS
₦$fetch_amount received, and bank account name does not match paxful registered account name
EOS;

						if(!in_array($postmessage, $ownerMsgArr))
						{
							$payload = [
								'trade_hash' => $tradehash,
								'message' => $postmessage
							];
							
							$endpoint = '/api/trade-chat/post';	
							$data = PAXquery($endpoint, $payload);					
						}
						die('Incomplete Payment, Name Mismatch');
					}
					else
					{
						die('Unknown Response');
					}	
				}
			}
			else
			{
				die('Unknown Response2');
			}
		}		
	}

?>