<?php
	
	error_reporting(0);
	
	if(isset($_GET['tradehash'])){
		$tradehash = $_GET['tradehash'];
	}else{
		die('Trade Hash is required');
	}
	
	$root = $_SERVER['DOCUMENT_ROOT'];
	//include($root.'/databasefile.php');
	include('functionsfile.php');
	
	
	
	//GET OPEN TRADES HASH	
	$payload = [
		'trade_hash' => $tradehash
	];
	
	$endpoint = '/api/trade/get';	
	$data = PAXquery($endpoint, $payload);
	
	$allArr = array();	
	$ownerMsgArr = array();
	$sellerArr = array();
	$msgtypeArr = array();
	$seen = '0';
	$canceltrade = '0';
	$incompleteacctname = '0';
	$bvalid = '0';
	$bquest = '0';
	$tarr = $data;
	$tradearr = $data->data->trade; 
	$tradestatus = $tradearr->trade_status; 
	$owneruser = $tradearr->buyer_name; //offer owner 
	$tradeuser = $tradearr->seller_name; //tradeuser to contact
	$offertype = $tradearr->offer_type; //buy [when someoneselling to me]
	$offerhash = $tradearr->offer_hash;
	$offerlink = 'https://paxful.com/offer/'.$offerhash;
	$fiatamount = $tradearr->fiat_amount_requested;
	$requestedamount = $tradearr->crypto_amount_requested;
	$offercur = $tradearr->fiat_currency_code;
	$offerpaymethod = $tradearr->payment_method_slug;
	$escrowfee = $tradearr->fee_crypto_amount;
	$escrowamt = ''.round((($requestedamount + $escrowfee) / 100000000), 8);
	
	
	if(!$offertype){			
		$dstatus = $tarr->status;
		$dcode = $tarr->error->code;
		$dmessage = $tarr->error->message;
		
		if($dstatus == 'error' && $dcode == '429' && $dmessage == 'Rate limit exceeded')
		{			
			die($dmessage);
		}
	}
	
	if($offertype == 'buy' && $owneruser == $owneruser2)
	{
		$prevsaved = 0;	
		$logfile = './././success.txt';
		$destlogfile = './././success'.time().'.txt';
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
			if($pstatus ==  'NOT PAID' && $thishash == $tradehash)
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
			
			
			/* $diffrate = $traderate-$offercurarr['NGN2'];
			echo $diffrate." - ".$traderate." - ".$offercurarr['NGN2'];
			exit(); */
			
			if($traderate > $offercurarr['NGN2'])
			{
				//STOP TRADE WHEN TRADE RATE IS HIGHER
				array_push($paxblockArr, $tradeuser);				
				$sumlog = $tradehash.' NOT PAID $'.$requestedamountusd.' @'.$traderate.' = '.$offercur.$fiatamount.' '.$requestedamountbtc.' BTC - '.$offerpaymethod.' | '.gmdate("d.m.Y h:i:s a", time()+3600);
				
				//$correctrate = $traderate-110;
				$correctrate= $offercurarr['NGN2'];
				//total amount for the same coin will be about
				$manualpayoutrate = $requestedamountusd*$correctrate;
				
				
				$msglogs = 'I wont be able to honor this trade cos the rate no correct, so i have cancelled it. The rate wey i dey buy now is NGN '.$correctrate.' per $ NOT NGN '.$traderate.'. Open a new trade thru this offer link https://paxful.com/offer/1m72RkcbuzN to complete this transaction.';
				
				file_put_contents("msg.txt", "$msglogs\n");
			}
			else
			{
				$sumlog = $tradehash.' $'.$requestedamountusd.' @'.$traderate.' = '.$offercur.$fiatamount.' '.$requestedamountbtc.' BTC - '.$offerpaymethod.' | '.gmdate("d.m.Y h:i:s a", time()+3600);				
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
			$acctcode = ''; 
			$acctname = '';
			for($j=0; $j<$countTradechat; $j++)
			{
				$tradebegin = $tradechatArr[0]->timestamp;						
				$thistime = time();	
				$tradeperiod = $thistime - $tradebegin;
				if($tradeperiod > $allowed){
					$canceltrade = '1';
				}
				
				$thisauthor = $tradechatArr[$j]->author;						
				$thismsgtype = $tradechatArr[$j]->type;	
				array_push($msgtypeArr, trim($thismsgtype));						
				
				if(($tradeuser == $thisauthor || $tradeuser == $tradechatArr[$j]->text->author->username) && $thismsgtype == 'msg')
				{
					
					$text = $tradechatArr[$j]->text;
					$text = trim(str_replace(["\r", "\n", "\t", ":", "\\", "/", "*", ",", ".", "[", "]", "(", ")", "{", "}", "#", "!", "`", "'", ";", "<", ">", "|", "^", "&", "%", "@", "$"], " ", $text));							
					array_push($sellerArr, trim($text));
					
				}
				else if($owneruser == $thisauthor && $thismsgtype == 'msg')
				{
					$text = $tradechatArr[$j]->text;
					array_push($ownerMsgArr, $text);
				}
				else if(($tradeuser == $thisauthor || $tradeuser == $tradechatArr[$j]->text->author->username) && $thismsgtype == 'bank-account')
				{							
					$pcurrency = $tradechatArr[$j]->text->$thismsgtype->currency;
					if($pcurrency == 'NGN')
					{
						$accountnumber = $tradechatArr[$j]->text->$thismsgtype->accountNumber;	
						$acctcode = $tradechatArr[$j]->text->$thismsgtype->bankName;
						$acctname = $tradechatArr[$j]->text->$thismsgtype->holderName;
						$text = $accountnumber.' '.$acctcode.' '.$acctname;
						array_push($sellerArr, trim($text));
						$bquest = '1';										
					}
					else
					{						
						$bvalid = '1';													
					}
					
				}
				
				if(!$ncode && ($j == $countTradechat - 1) && $compare !== '1')
				{					
					if($bvalid == '1'){
						
						$postmessage = <<<EOS
Please drop a valid Nigerian Bank Account
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
						die('Invalid Naira Account');
					}
					
					
					$text = strtolower(implode(' ', $sellerArr));		
					$text = trim(str_replace("first city", "fcmb", $text));						
					$textArr = accountNumberPax($text);						
					list($accountnumber, $ncode) = explode('|', $textArr[0]);							
					$acctname = trim(str_replace($accountnumber, "", $text));					
					
					if($ncode)
					{
						$thisnacctno = $accountnumber;
						$thisncode = $ncode;
						$initbankacctname = trim(str_replace(["*", ",", ".", "[", "]", "(", ")", "{", "}", "#", ";", "<", ">", "|",  "&", "%", "@", "$"], " ", getBankName($ncode, $accountnumber)));
						$bankacctname = preg_replace('#[^a-zA-Z- ]#i', '', $initbankacctname);
						$thisnbankname = $bankacctname;
						$nbankacctname = $bankacctname;
						$bankacctname = strtolower($bankacctname);
						if($bankacctname !== '' && $bankacctname !== 'failed')
						{
							$acctname = strtolower($acctname);
							$expbankname = explode(' ', $bankacctname);
							$expacctname = explode(' ', $acctname);
							$n = 0;
							$input = '';
							for($m=0; $m<count($expbankname); $m++)
							{
								$chkname = $expbankname[$m]; 
								if(in_array($chkname, $expacctname))
								{
									$n++;
								}
								else
								{
									$input .= $chkname.' ';
								}
							}
							if($n >= 2)
							{
								$compare = '1';
								$seen = '1'; 
								$author =  $thisauthor;
								$rbankacctname = $nbankacctname;
								$rncode = $ncode;
								$raccountnumber = $accountnumber;
							}
							else if($n == 1 || $n == 0)
							{
								foreach ($expacctname as $word) 
								{
									if(strlen($word) >= 4 && trim($word) !== '')
									{
										if(strpos($input, $word) !== false){$closest = $word; $n++;}					
									}
								}

								if ($n > 1) 
								{
									$compare = '1';
									$seen = '1'; 
									$author =  $thisauthor;
									$rbankacctname = $nbankacctname;
									$rncode = $ncode;
									$raccountnumber = $accountnumber;
								}else{
									$g = 1;
								}
							}
							
							if(($g && $g == 1) || $n < 1)
							{
								foreach ($expbankname as $word) 
								{
									if(strlen($word) >= 4 && trim($word) !== '')
									{
										if(strpos($text, $word) !== false){$closest = $word; $n++;}
									}
								}

								if ($n >= 2) 
								{
									$compare = '1';
									$seen = '1'; 
									$author =  $thisauthor;
									$rbankacctname = $nbankacctname;
									$rncode = $ncode;
									$raccountnumber = $accountnumber;
								}
								else
								{
									$incompleteacctname = '1';
								}
							}
						}						
					}				
				}
				//die($rbankacctname.' '.$rncode.' '. $raccountnumber.' '.$compare.' '.$seen);	
				//die($nbankacctname.' '.$ncode.' '. $accountnumber.' '.$compare.' '.$seen);
			}
				
			
			
			if((strpos($text, 'paycom') !== false) && !$rncode && !$raccountnumber && !$rbankacctname){
				$getdirect = true;
			}
			if((strpos($text, 'opay') !== false) && !$rncode && !$raccountnumber && !$rbankacctname){
				$getdirect = true;
			}
			if((strpos($text, '0pay') !== false) && !$rncode && !$raccountnumber && !$rbankacctname){
				$getdirect = true;
			}
			if((strpos($text, 'providus') !== false) && !$rncode && !$raccountnumber && !$rbankacctname){
				$getdirect = true;
				$ucode = '101';
			}
			if((strpos($text, 'jaiz') !== false) && !$rncode && !$raccountnumber && !$rbankacctname){
				$getdirect = true;
				$ucode = '301';
			}
			if((strpos($text, 'suntrust') !== false) && !$rncode && !$raccountnumber && !$rbankacctname){
				$getdirect = true;
				$ucode = '100';
			}
			if((strpos($text, 'vfd') !== false) && !$rncode && !$raccountnumber && !$rbankacctname){
				$getdirect = true;
				$ucode = '090110';
			}
			if((strpos($text, 'kuda') !== false) && !$rncode && !$raccountnumber && !$rbankacctname){
				$getdirect = true;
				$ucode = '090267';
			}
			if((strpos($text, 'rubies') !== false || strpos($text, 'rubbies') !== false) && !$rncode && !$raccountnumber && !$rbankacctname){
				$getdirect = true;
				$ucode = '090175';
			}
			if((strpos($text, 'wema') !== false) && !$rncode && !$raccountnumber && !$rbankacctname){
				$getdirect = true;
				$ucode = '035';
			}		
			
			
			if($getdirect == true)
			{	
				
				if($ucode){
					// FOR NON COMMERCIAL
					$textArr2 = accountNumberPax3($text, $ucode);			
					list($accountnumber, $ncode) = explode('|', $textArr2[0]);							
					$acctname = trim(str_replace($accountnumber, "", $text));
				}else{
					//FOR OPAY PAYCOM
					$textArr2 = accountNumberPax2($text);			
					list($accountnumber, $ncode) = explode('|', $textArr2[0]);							
					$acctname = trim(str_replace($accountnumber, "", $text));			
				}
				
				
				if($ncode)
				{			
					$thisnacctno = $accountnumber;
					$thisncode = $ncode;
					$initbankacctname = trim(str_replace(["*", ",", ".", "[", "]", "(", ")", "{", "}", "#", ";", "<", ">", "|",  "&", "%", "@", "$"], " ", getBankName($ncode, $accountnumber)));
					$bankacctname = preg_replace('#[^a-zA-Z- ]#i', '', $initbankacctname);
					$thisnbankname = $bankacctname;
					$nbankacctname = $bankacctname;
					$bankacctname = strtolower($bankacctname);
					if($bankacctname !== '' && $bankacctname !== 'failed')
					{
						$acctname = strtolower($acctname);
						$expbankname = explode(' ', $bankacctname);
						$expacctname = explode(' ', $acctname);
						if(trim($bankacctname) == '')
						{
							//echo 'Unable to get accountname';
							die('Unable to BankAccount Name');							
						}
						$n = 0;
						$input = '';
						for($m=0; $m<count($expbankname); $m++)
						{
							$chkname = $expbankname[$m]; 
							if(in_array($chkname, $expacctname))
							{
								$n++;
							}
							else
							{
								$input .= $chkname.' ';
							}
						}
						if($n >= 2)
						{
							$compare = '1';
							$seen = '1'; 
							$author =  $thisauthor;
							$rbankacctname = $nbankacctname;
							$rncode = $ncode;
							$raccountnumber = $accountnumber;
						}
						else if($n == 1 || $n == 0)
						{
							foreach ($expacctname as $word) 
							{
								if(strlen($word) >= 4 && trim($word) !== '')
								{
									if(strpos($input, $word) !== false){$closest = $word; $n++;}					
								}
							}

							if ($n > 1) 
							{
								$compare = '1';
								$seen = '1'; 
								$author =  $thisauthor;
								$rbankacctname = $nbankacctname;
								$rncode = $ncode;
								$raccountnumber = $accountnumber;
							}else{
								$g = 1;
							}
						}
						
						if(($g && $g == 1) || $n < 1)
						{
							foreach ($expbankname as $word) 
							{
								if(strlen($word) >= 4 && trim($word) !== '')
								{
									if(strpos($text, $word) !== false){$closest = $word; $n++;}
								}
							}

							if ($n >= 2) 
							{
								$compare = '1';
								$seen = '1'; 
								$author =  $thisauthor;
								$rbankacctname = $nbankacctname;
								$rncode = $ncode;
								$raccountnumber = $accountnumber;
							}
							else
							{
								$incompleteacctname = '1';
							}
						}
					}					
				}	
			}
			
			
			$postmessage = <<<EOS
$msglogs
EOS;
			if(in_array($postmessage, $ownerMsgArr) || in_array($tradecancelled, $msgtypeArr) || in_array($tradeexpired, $msgtypeArr))
			{			
				//INCLUDE MESSAGE TO TREAT MANUALLY //Save and upload
				$payload = [
					'trade_hash' => $tradehash
				];
				
				$endpoint = '/api/trade/cancel';	
				$data = PAXquery($endpoint, $payload);
				die('User Re-cancelled');				
			}
			
			if($rncode && $raccountnumber && $rbankacctname && !in_array($tradecancelled, $msgtypeArr) && !in_array($tradeexpired, $msgtypeArr))
			{				
				$thispaxArr = array('trade_hash' => $tradehash,'owner_username' => $owneruser,'responder_username' => $tradeuser,'offer_type' => $offertype,'offer_hash' => $offerhash,'offer_link' => $offerlink,'fiat_amount_requested' => $fiatamount,'crypto_amount_requested' => $requestedamount,'is_cancel' => $canceltrade,'is_compare' => $compare,'is_seen' => $seen,'is_author' => $author,'is_completename'=>$incompleteacctname,'bank_account' => $raccountnumber,'bank_code' => $rncode,'bank_accountname' => $rbankacctname,'type_msg' => $msgtypeArr,'owner_msg' => $ownerMsgArr,'seller_msg' => $sellerArr);
				
				array_push($allArr, $thispaxArr);
			}
			else if($thisnacctno !== '' && $thisncode !== '' && !$rbankacctname)
			{
				$postmessage = <<<EOS
Please send full account names
EOS;
			
				if(!in_array($postmessage, $ownerMsgArr) && !in_array($paid, $msgtypeArr) && !in_array($tradecancelled, $msgtypeArr) && !in_array($tradeexpired, $msgtypeArr))
				{
					
					$payload = [
						'trade_hash' => $tradehash,
						'message' => $postmessage
					];
					
					$endpoint = '/api/trade-chat/post';	
					$data = PAXquery($endpoint, $payload);					
				}
				die('Expecting BankAccount Name');
				
			}
			else if($getdirect == false && $accountnumber !== '' && $ncode == '')
			{
				$postmessage = <<<EOS
Please send bank name
EOS;
				if(!in_array($postmessage, $ownerMsgArr) && !in_array($paid, $msgtypeArr) && !in_array($tradecancelled, $msgtypeArr) && !in_array($tradeexpired, $msgtypeArr))
				{
					
					$payload = [
						'trade_hash' => $tradehash,
						'message' => $postmessage
					];
					
					$endpoint = '/api/trade-chat/post';	
					$data = PAXquery($endpoint, $payload);	
					
				}
				die('Expecting Bank Name');
			}
			else if($bankacctname == 'failed' && $accountnumber !== '' && $ncode !== '')
			{/// NEED TO UPDATE acctname output when unable to fetch name
				$postmessage = <<<EOS
Unable to verify bank account name
EOS;
				if(!in_array($postmessage, $ownerMsgArr) && !in_array($paid, $msgtypeArr) && !in_array($tradecancelled, $msgtypeArr) && !in_array($tradeexpired, $msgtypeArr))
				{
					
					$payload = [
						'trade_hash' => $tradehash,
						'message' => $postmessage
					];
					
					$endpoint = '/api/trade-chat/post';	
					$data = PAXquery($endpoint, $payload);	
					
				}
				die('Unable to Validate Bank AccountName');
			}
					

			$getplatform = "";
			$pdlist = file_get_contents('paymentrecord.txt');
			$pdlistarr = preg_split("/\r\n|\n|\r/", $pdlist);
			$pdlistcount = count($pdlistarr);
			$npdlistarr = array();

			for($m=0; $m<$pdlistcount; $m++)
			{
				if(trim($pdlistarr[$m]) !== '')
				{
					$exc = explode(' ', trim($pdlistarr[$m]));
					$npdlist = $exc[0].' '.$exc[1];				
					$targettrade = $tradehash.' successful';
					if($targettrade == trim($npdlist))
					{
						$exc2 = explode('|', trim($pdlistarr[$m]));					
						$getplatform = $exc2[1];
						
						$postmessage = <<<EOS
Keep my offer link incase you want to sell next time: 
$offerlink

Dont forget to also add me @$owneruser  to your trusted trader list.

Please DONT FORGET TO LEAVE FEEDBACK.
EOS;

						if(!in_array($paid, $msgtypeArr))
						{
							$payload = [
								'trade_hash' => $tradehash,
								'message' => $postmessage
							];
							
							$endpoint = '/api/trade-chat/post';	
							$data = PAXquery($endpoint, $payload);
							
							$payload = [
								'trade_hash' => $tradehash
							];
							
							$endpoint = '/api/trade/paid';	
							$data = PAXquery($endpoint, $payload);	
							$justpaid = 'paid';
						}
					}
					array_push($npdlistarr, $npdlist);
				}
			}

			$successtrade = $tradehash.' successful';
			$failtrade = $tradehash.' failed';
			$availtradecount = count($allArr);
			
		}
		
		if($availtradecount && $availtradecount > 0)
		{
			$tradehash = $allArr[0]['trade_hash'];
			$owneruser = $allArr[0]['owner_username'];
			$tradeuser = $allArr[0]['responder_username'];
			$offertype = $allArr[0]['offer_type'];
			$offerlink = $allArr[0]['offer_link'];
			$fiatamount = $allArr[0]['fiat_amount_requested'];
			$requestedamount = $allArr[0]['crypto_amount_requested'];
			$canceltrade = ''.$allArr[0]['is_cancel'];
			$compare = ''.$allArr[0]['is_compare'];
			$seen = ''.$allArr[0]['is_seen'];
			$author = ''.$allArr[0]['is_author'];
			$raccountnumber = ''.$allArr[0]['bank_account'];
			$rbankacctname = ''.$allArr[0]['bank_accountname'];
			$rncode = ''.$allArr[0]['bank_code'];
			$incompleteacctname = ''.$allArr[0]['is_completename'];
			$msgtypeArr = $allArr[0]['type_msg'];
			$ownerMsgArr = $allArr[0]['owner_msg'];
			$sellerArr = $allArr[0]['seller_msg'];
			
			
			//WHEN USER IS BLOCKED
			if(((in_array($tradeuser, $paxblockArr) || in_array($raccountnumber, $paxblockArr)) || (in_array($tradeuser, $paxblockArr) && in_array($raccountnumber, $paxblockArr))) && !in_array($paid, $msgtypeArr))
			{
				
//FALSELY MARKED PAID
$postmessage = <<<EOS
$msglogs
EOS;
				if(!in_array($postmessage, $ownerMsgArr))
				{
					
					
					//INCLUDE MESSAGE TO TREAT MANUALLY
							
					$payload = [
						'trade_hash' => $tradehash,
						'message' => $postmessage
					];
					
					$endpoint = '/api/trade-chat/post';	
					$data = PAXquery($endpoint, $payload);
					
					// this wayok
					
					$payload = [
						'trade_hash' => $tradehash
					];
					
					$endpoint = '/api/trade/cancel';	
					$data = PAXquery($endpoint, $payload);
					
				}
				
				die('User Blocked');
			}
			
			//WHEN USER IS BLOCKED 2
			if(((in_array($tradeuser, $paxblockArr) || in_array($raccountnumber, $paxblockArr)) || (in_array($tradeuser, $paxblockArr) && in_array($raccountnumber, $paxblockArr))) && in_array($paid, $msgtypeArr))
			{
				die('User Blocked');	
			}
			
			
			//WHEN USER IS OWING
			if(((in_array($tradeuser, $paxoweArr) || in_array($raccountnumber, $paxoweArr)) || (in_array($tradeuser, $paxoweArr) && in_array($raccountnumber, $paxoweArr))) && !in_array($paid, $msgtypeArr))
			{

$postmessage = <<<EOS
Gimme a min pls...
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
						'trade_hash' => $tradehash
					];
					
					$endpoint = '/api/trade/paid';	
					$data = PAXquery($endpoint, $payload);	
				}	
				die('User Owing');
			}
			
			//WHEN USER IS OWING 2
			if(((in_array($tradeuser, $paxoweArr) || in_array($raccountnumber, $paxoweArr)) || (in_array($tradeuser, $paxoweArr) && in_array($raccountnumber, $paxoweArr))) && in_array($paid, $msgtypeArr))
			{
				die('User Owing');
			}
			
			//WHEN USER BANK IS UNAVAILABLE
			if(in_array($rncode, $bankrestrictArr) && !in_array($paid, $msgtypeArr))
			{
				

$postmessage = <<<EOS
Your bank is currently either delaying funds or not going
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
				die('Unresponsive Bank');
			}	
			
			//WHEN ALLOWED TRADE TIMEOUT AND NO BANK INFO DROPPED
			if($canceltrade == '1' && !in_array($paid, $msgtypeArr))
			{

$postmessage = <<<EOS
Please open a new trade, Cant pay this trade because of the time left.
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
						'message' => $postmessage
					];
					
					$endpoint = '/api/trade/cancel';	
					$data = PAXquery($endpoint, $payload);						
				}	
				die('Payment Window Timedout');
			}
			
			//WHEN USER DOESNT DROP FULL BANK NAME
			if($canceltrade == '0' && $incompleteacctname == '1' && !in_array($paid, $msgtypeArr))
			{

$postmessage = <<<EOS
Please send full account name
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
				die('Incomplete Bank Account Name');
			}
			
			//WHEN USER DOESNT DROP REQUIRED DETAILS
			if($canceltrade == '0' && $compare == "" && !in_array($paid, $msgtypeArr))
			{

$postmessage = <<<EOS
Please send the bank details with full account names
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
				die('Incomplete Bank Account Details');
			}
			
			//WHEN USER DOESNT DROP BANK INFO
			if($canceltrade == '0' && $compare == "notreceived")
			{
				
$postmessage = <<<EOS
Please send your bank details
EOS;

$postmessage2 = <<<EOS
Only one bank account is allowed. Payment split is NOT allowed
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
				die('Incomplete Bank Account Details 2');
			}
			
			//WHEN USER HAVENT RELEASED IN ALLOWED TRADE TIME
			if($canceltrade == '1' && in_array($paid, $msgtypeArr) && in_array($successtrade, $npdlistarr))
			{
			
$postmessage = <<<EOS
Please dont forget to release bitcoin. Thanks
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
				die('Trade Release Reminder');
			}
			
			
			
			if($canceltrade == '0' && $seen == '1' && !in_array($tradeuser, $paxblockArr) && !in_array($tradeuser, $paxoweArr) && !in_array($raccountnumber, $paxblockArr) && !in_array($raccountnumber, $paxoweArr))
			{
				$total = $fiatamount;
				$topay = $total * 100;
				$topaycheck = $topay + 5000;
				$recipientname = $rbankacctname;
				$id = $tradehash;
				$account_number = $raccountnumber;
				$bank_code = $rncode;				
								
				include_once('nairabal.php'); 
				//PAYOUT MODULE
				//$nairabalance = 4500000;
				$nairabalance = $balance;	
				
				if($nairabalance >= $topaycheck && $total !== '' && !in_array($paid, $msgtypeArr) && !in_array($successtrade, $npdlistarr) && !in_array($failtrade, $npdlistarr))
				{
									
					//die($traderate." ".$offercurarr['NGN2']." ".$paxblockArr[0]);
					$data = array("amount"=>$topay, "acctname"=>$recipientname, "id"=>$id, "bankacct"=> $account_number, "bankcode"=> $bank_code);
					$string = http_build_query($data);
					
					$url = 'http://139.162.89.151/paxful/Main2/nairapay.php'; //.$nairaplatform;
					$ch = curl_init($url);
					curl_setopt($ch, CURLOPT_POST, true);
					curl_setopt($ch, CURLOPT_POSTFIELDS, $string);
					curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
					curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
					curl_setopt($ch, CURLOPT_HTTPHEADER, array(
						'Content-Type: application/x-www-form-urlencoded')
					);
					$ccc = trim(curl_exec($ch));
					curl_close($ch); 
					
					$mailsubject = "Sike Auto-Payout Done for ".$id;
					
					//Pass $rbankacctname to get from $ccc by exploding
					
					$key = array_search($bank_code, $codelist);
					$targetbank = $rubieslist[$key];
					
					$info = "================CurrencyNG==============\n";
					$info .= "Trade ID: ".$id."\n";
					$info .= "Amount Paid: ".($topay / 100)."\n";
					$info .= "Credited Account No: ".$account_number."\n";
					$info .= "Credited Account Name: ".$recipientname."\n";
					$info .= "Credited Bank Name: ".$targetbank."\n";
					$info .= "==============SwayHost===========\n";
					
					if($ccc == 'Success')
					{
	$link = "https://swayhost.com/paxful/Main2/mail.php?subject='".$mailsubject."&info=".$info;
			/* $ch = curl_init($link);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
			$exec = curl_exec($ch); */
			
			$exec= file_get_contents($link);
			
						/* mail("aduragbemi.ogundijo@gmail.com",$mailsubject,$info,"From: PaxfulAutoPay<support@e-currency.com.ng>"); */
$postmessage = <<<EOS
Keep my offer link incase you want to sell next time: 
$offerlink

Dont forget to also add me @$owneruser  to your trusted trader list.

Please DONT FORGET TO LEAVE FEEDBACK.
EOS;

					
						$logfile = $root.'/'.$loggood;
						if(file_exists($logfile)){
							$fgc = file_get_contents($logfile);
							$fpc = file_put_contents($logfile, $fgc.gmdate("d.m.Y h:i:s a", time()+$offset)."; Success Trade Hash $tradehash Funded with $fiatamount\n");
						}else{
							$fpc = file_put_contents($logfile, gmdate("d.m.Y h:i:s a", time()+$offset)."; Success Trade Hash $tradehash Funded with $fiatamount\n");
						}					
						
						if($justpaid == 'notpaid')
						{
							$payload = [
								'trade_hash' => $tradehash,
								'message' => $postmessage
							];
							
							$endpoint = '/api/trade-chat/post';	
							$data = PAXquery($endpoint, $payload);
						}
						
						
						$payload = [
							'trade_hash' => $tradehash
						];
						
						$endpoint = '/api/trade/paid';	
						$data = PAXquery($endpoint, $payload);

						die('SuccessPaid');						
					}
					else
					{

$postmessage = <<<EOS
Oops sending failed, Resending it in a bit
EOS;
						if(!in_array($postmessage, $ownerMsgArr))
						{
							
							$logfile = $root.'/'.$logfail;
							if(file_exists($logfile)){
								$fgc = file_get_contents($logfile);
								$fpc = file_put_contents($logfile, $fgc.gmdate("d.m.Y h:i:s a", time()+$offset)."; Retry Trade Hash $tradehash Funded with $fiatamount\n");
							}else{
								$fpc = file_put_contents($logfile, gmdate("d.m.Y h:i:s a", time()+$offset)."; Retry Trade Hash $tradehash Funded with $fiatamount\n");
							}
							
							$payload = [
								'trade_hash' => $tradehash,
								'message' => $postmessage
							];
							
							$endpoint = '/api/trade-chat/post';	
							$data = PAXquery($endpoint, $payload);				
						}								
					}
				}						
				
				if(in_array($paid, $msgtypeArr) && in_array($successtrade, $npdlistarr))
				{

$postmessage = <<<EOS
I don pay to your account no $raccountnumber from my $getplatform
EOS;

//Release bitcoin asap. Thanks
$postmessage2 = <<<EOS
Confirm and Release Please. No delay
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
					die('SuccessPaid Confirmed');
				}				
				
				if(in_array($paid, $msgtypeArr) && in_array($failtrade, $npdlistarr))
				{
				
$postmessage = <<<EOS
Please open a new trade, because this trade payment failed.
EOS;
					$lmsg = count($ownerMsgArr) - 1;
					
					if(!in_array($postmessage, $ownerMsgArr) && $fiatamount < 5000)
					{				
						$payload = [
							'trade_hash' => $tradehash,
							'message' => $postmessage
						];
						
						$endpoint = '/api/trade-chat/post';	
						$data = PAXquery($endpoint, $payload);
						
						$payload = [
							'trade_hash' => $tradehash
						];
						
						$endpoint = '/api/trade/cancel';	
						$data = PAXquery($endpoint, $payload);					
					}
					die('Sent Payment Failed Cancelling');
				}
				
				if($nairabalance < $topaycheck && !in_array($paid, $msgtypeArr))
				{
					
$postmessage = <<<EOS
Paying asap... One moment please
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
					die('Insufficient Balance');
				}
				
				if(in_array($successtrade, $npdlistarr))
				{
				
$postmessage = <<<EOS
Please dont forget to release bitcoin. Thanks
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
				}
				else
				{			
					die('Unknown Response');
				}						
			}			
			
		}
		
		if($tradestatus !== 'Released' && !in_array($paid, $msgtypeArr) && count($ownerMsgArr) == 0)
		{
		
$postmessage = <<<EOS
Abeg drop your bank account information
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
			die('Bank Details Request');
		}
		
		if($thisnacctno !== '' && $thisncode !== '')
		{
			die('Unable To Fetch: '.$thisnbankname);
		}
		
		if($availtradecount == 0)
		{
			die('No Acct');
		}
		
		
	}

?>