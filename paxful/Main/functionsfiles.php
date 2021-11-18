<?php

//hook triggers checkofferview to deflate the price and puts the offer viewed in updateoffer.txt
//checkopentrades inflates the prices in the updateoffer.txt through inflateoffer on its next visit
//offers that did not make it into updateoffer.txt never gets inflated.
//checkofferview2 checks for new offer views to deflate every 10secs

	$ch = curl_init('https://blockchain.info/tobtc?currency=USD&value=1');
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
	$usdbtc = curl_exec($ch);
	
	
	
	define("PUB_KEY","");// Also in $paxfulkey
	define("PRIV_KEY",""); //Also in $paxfulsecret
	
	
	function PAXquery($path, array $payload = array())
	{
		$mt = explode(' ', microtime());
		$nonce = $mt[1].substr($mt[0], 2, 6);
			
		if($payload){			
			$payload['apikey'] = PUB_KEY;
			$payload['nonce'] = $nonce;
			$req = http_build_query($payload, null, '&', PHP_QUERY_RFC3986);
			$apiseal = hash_hmac('sha256', $req, PRIV_KEY);
			$payload['apiseal'] = $apiseal;
			$string = http_build_query($payload, null, '&', PHP_QUERY_RFC3986);
		}
		
		$ch = null;
		$ch = curl_init('https://paxful.com'.$path);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $string);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
		curl_setopt($ch, CURLOPT_HTTPHEADER, [
			'Accept: application/json; version=1',
			'Content-Type: text/plain',
		]);
		$response = curl_exec($ch);
		$info = curl_getinfo($ch);		
		if ($response === false) throw new Exception('Curl error: '.curl_error($ch));
		$data = json_decode($response);
		if (!$data) throw new Exception('Invalid data: '.$response);
		curl_close($ch);

		return $data;
	}
	
	function getURL2($tradehash){
		//$rootdir not declared. Should be here or made public.I t worked there ebfore... since we didnt call the function here
		//We need to change time also
		$rootdir ='http://178.79.134.138/paxful/Main2/';
		$ch = curl_init($rootdir.'/runtrade.php?tradehash='.$tradehash);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
		$exec = curl_exec($ch);
	}
	
	
	function getURL($tradehash, $type){
		//$rootdir not declared. Should be here or made public.I t worked there ebfore... since we didnt call the function here
		//We need to change time also
		$rootdir ='http://178.79.134.138/paxful/Main2/';
		if($type == 'buy'){
			$ch = curl_init($rootdir.'/runtrade.php?tradehash='.$tradehash);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
			$exec = curl_exec($ch);
		}
		if($type == 'sell'){
			$ch = curl_init($rootdir.'/runtradesell.php?tradehash='.$tradehash);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
			$exec = curl_exec($ch);
		}
		
	}

	function validateBankNamePax($accountNumber)
	{	
		$sumArr = array();	
		$outArr = array();	
		$codesumArr = array();
		$accountNumberLength = strlen($accountNumber);
		$lastNumber = trim(substr($accountNumber, 9 ,1) * 1);
			
		$bankcodearray = array('044','023','063','050','070','011','214','058','030','082','076','221','068','232','032','033','215','035','057','090175','301','101','100','090267','305'.'090110');
		
		$bankarray = array('Access Bank','Citi Bank Nigeria','Access Bank','Ecobank','Fidelity Bank','First Bank','First City Monument Bank','Guaranty Trust Bank','Heritage Bank','Keystone Bank','Polaris Bank','Stanbic IBTC Bank','Standard Chartered Bank','Sterling Bank','Union Bank','United Bank of Africa','Unity Bank','Wema Bank','Zenith Bank','Rubies MFB','Jaiz Bank','Providus Bank','SunTrust Bank','Kuda MFB','Paycom Opay','VFD MFB');
		
		if($accountNumberLength != 10){
			return 0;		
		}else{
			for($i=0; $i<count($bankcodearray); $i++){
				$banksumArr = array();
				$bankCode = $bankcodearray[$i];	
				$bankCodeLength = strlen($bankCode);
				if($bankCode == '090267')
				{
					$Format = '232373';
				}
				else
				{
					$Format = $bankCodeLength == 3 ? '373' : '454373';
				}
				for($j=0; $j<$bankCodeLength; $j++){						
					$m = $bankCode[$j]; //substr($bankCode, [$j], 1);
					$f = $Format[$j]; //substr($Format, [$j], 1);				
					$thisVal = $m * $f;
					array_push($banksumArr, $thisVal);								
				}
				$thisSum = array_sum($banksumArr);
				array_push($codesumArr, $thisSum);
			}
			
			$Format = '373373373';
			for($i=0; $i<($accountNumberLength-1); $i++){
				$m = substr($accountNumber, $i, 1);
				$f = substr($Format, $i, 1);
				$thisVal = $m * $f;
				array_push($sumArr, $thisVal);
			}	
			
			$sum = array_sum($sumArr);
			for($j=0; $j<count($codesumArr); $j++){	
				$codeSum = $codesumArr[$j];
				$summ = $sum + $codeSum;
				$checkSum = 10 - ($summ % 10);
				$checkSum = $checkSum == 10 ? 0 : $checkSum;
				if($lastNumber == $checkSum){
					array_push($outArr, $bankcodearray[$j]);
				}
			}
			return implode("|", $outArr);
		}	
	}

	function checkaccountNumberPax($string)
	{
		$string = preg_replace('#[^0-9]#i', '', $string);
		if(strlen($string) == 10)
		{
			return $string;
		}
		return false;
	}

	function checkaccountNumberPax2($string)
	{
		$string = preg_replace('#[^0-9]#i', '', $string);
		if(strlen($string) == 10 || strlen($string) == 11)
		{
			return $string;
		}
		return false;
	}

	function accountNumberPax($string)
	{
		
		$bankcodearray = array('044','023','063','050','070','011','214','058','030','082','076','221','068','232','032','033','215','035','057','090175','301','101','100','090267','305','090110');
		$bankarray = array('Access Bank','Citi Bank Nigeria','Access Bank','Ecobank','Fidelity Bank','First Bank','First City Monument Bank','Guaranty Trust Bank','Heritage Bank','Keystone Bank','Polaris Bank','Stanbic IBTC Bank','Standard Chartered Bank','Sterling Bank','Union Bank','United Bank of Africa','Unity Bank','Wema Bank','Zenith Bank','Rubies MFB','Jaiz Bank','Providus Bank','SunTrust Bank','Kuda MFB','Paycom Opay','VFD MFB');
		$bankarray2 = array('access|abp|acces|acess','citi','diamond|dbp|diamnd|access|abp|daimond|acces|acess|acees|aces','eco','fidelity','first|fbn|fisrt|1st','fcmb|monument','gtb|guarantee|guaranty|gua|gt','heritage','keystone','skye|polaris','stanbicibtc|stanbic|ibtc|stan','scb|standardchartered|standard','sterling|sbp','union|ubn','uba|united','unity','wema|wena','zenith|zenit|zenite|zenitt|zennit','rubies|high|street|rubbies','jaiz','providus|provid','suntrust|sun','kuda','paycom|opay|payco','vfd');		
		$nos = array();
		$string = strtolower($string);
		$string = str_replace("\r\n",' ',$string);
		$string = str_replace("\n",' ',$string);

		foreach(preg_split('/ /', $string) as $token)
		{
			$no = checkaccountNumberPax($token);		
			if($no !== false)
			{
				$option = validateBankNamePax($no);
				$codeArr = explode('|', $option);
				for($j=0; $j<count($codeArr); $j++)
				{
					$cline = $codeArr[$j];
					$key = array_search($cline, $bankcodearray);
					$bline = $bankarray2[$key];
					$bankArr = explode('|', $bline);
					for($k=0; $k<count($bankArr); $k++)
					{
						if(strpos($string, $bankArr[$k]) !== false)
						{
							$nos[] = $no.'|'.$cline;
						}
					}
				}			
			}
		}
		
		return $nos;
	}

	function accountNumberPax2($string)
	{
		
		$bankcodearray = array('044','023','063','050','070','011','214','058','030','082','076','221','068','232','032','033','215','035','057','090175','301','101','100','090267','305','090110');
		$bankarray = array('Access Bank','Citi Bank Nigeria','Access Bank','Ecobank','Fidelity Bank','First Bank','First City Monument Bank','Guaranty Trust Bank','Heritage Bank','Keystone Bank','Polaris Bank','Stanbic IBTC Bank','Standard Chartered Bank','Sterling Bank','Union Bank','United Bank of Africa','Unity Bank','Wema Bank','Zenith Bank','Rubies MFB','Jaiz Bank','Providus Bank','SunTrust Bank','Kuda MFB','Paycom Opay','VFD MFB');
		$bankarray2 = array('access|abp|acces|acess','citi','diamond|dbp|diamnd|access|abp|daimond|acces|acess|acees|aces','eco','fidelity','first|fbn|fisrt|1st','fcmb|monument','gtb|guarantee|guaranty|gua|gt','heritage','keystone','skye|polaris','stanbicibtc|stanbic|ibtc|stan','scb|standardchartered|standard','sterling|sbp','union|ubn','uba|united','unity','wema','zenith|zenit|zenite|zenitt|zennit','rubies|high|street|rubbies','jaiz','providus|provid','suntrust|sun','kuda','paycom|opay|payco|pay','vfd');		
		$nos = array();
		$string = strtolower($string);
		$string = str_replace("\r\n",' ',$string);
		$string = str_replace("\n",' ',$string);

		foreach(preg_split('/ /', $string) as $token)
		{
			$no = checkaccountNumberPax2($token);		
			if($no !== false)
			{
				
				$bline = $bankarray2[24];
				$cline = $bankcodearray[24];
				$bankArr = explode('|', $bline);
				for($k=0; $k<count($bankArr); $k++)
				{
					if(strpos($string, $bankArr[$k]) !== false)
					{
						$nos[] = $no.'|'.$cline;
					}
				}						
			}
		}
		
		return $nos;
	}
	
	function accountNumberPax3($string, $cline)
	{
		
		$bankcodearray = array('044','023','063','050','070','011','214','058','030','082','076','221','068','232','032','033','215','035','057','090175','301','101','100','090267','305','090110');
		$bankarray = array('Access Bank','Citi Bank Nigeria','Access Bank','Ecobank','Fidelity Bank','First Bank','First City Monument Bank','Guaranty Trust Bank','Heritage Bank','Keystone Bank','Polaris Bank','Stanbic IBTC Bank','Standard Chartered Bank','Sterling Bank','Union Bank','United Bank of Africa','Unity Bank','Wema Bank','Zenith Bank','Rubies MFB','Jaiz Bank','Providus Bank','SunTrust Bank','Kuda MFB','Paycom Opay','VFD MFB');
		$bankarray2 = array('access|abp|acces|acess','citi','diamond|dbp|diamnd|access|abp|daimond|acces|acess|acees|aces','eco','fidelity','first|fbn|fisrt|1st','fcmb|monument','gtb|guarantee|guaranty|gua|gt','heritage','keystone','skye|polaris','stanbicibtc|stanbic|ibtc|stan','scb|standardchartered|standard','sterling|sbp','union|ubn','uba|united','unity','wema','zenith|zenit|zenite|zenitt|zennit','rubies|high|street|rubbies','jaiz','providus|provid','suntrust|sun','kuda','paycom|opay|payco|pay|paycomm|0pay','vfd');		
		$nos = array();
		$string = strtolower($string);
		$string = str_replace("\r\n",' ',$string);
		$string = str_replace("\n",' ',$string);

		foreach(preg_split('/ /', $string) as $token)
		{
			$no = checkaccountNumberPax($token);		
			if($no !== false)
			{			
				$nos[] = $no.'|'.$cline;					
			}
		}
		
		return $nos;
	}

	function getBankName($bankcode, $account)
	{	
		$rootdir ='http://178.79.134.138/paxful/Main2/';
		$curl = $rootdir.'/accountname.php?account='.$account.'&code='.$bankcode;
		$ch = curl_init();     
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
		curl_setopt($ch, CURLOPT_URL, $curl);		
		$confirm = curl_exec($ch);	
		$info = curl_getinfo($ch);
		curl_close($ch);
		
		$statuscode = $info['http_code'];
		if($statuscode !== 200)
		{
			die('Please try again');
		}		
		
		if($confirm){
			return $confirm;
		}else{
			return "";
		}  
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
	
	
	
	$paxbankcodearray = array('044','023','063','050','070','011','214','058','030','082','076','221','068','232','032','033','215','035','057','090175','090267');
	$paxbankarray = array('ABP','CITI','ABP','ECO','FBP','FBN','FCMB','GTB','HTG','KEYSTONE','POLARIS','STANBIC','SCB','SBP','UBN','UBA','UNITY','WEMA','ZIB','RUBIES','KUDA');	
	$paxbanklongarray = array('Access Bank','Citi Bank Nigeria','Access Bank','Ecobank','Fidelity Bank','First Bank','First City Monument Bank','Guaranty Trust Bank','Heritage Bank','Keystone Bank','Polaris Bank','Stanbic IBTC Bank','Standard Chartered Bank','Sterling Bank','Union Bank','United Bank of Africa','Unity Bank','Wema Bank','Zenith Bank','Rubies MFB','Kuda MFB');
	
	/* $btsellratewr = 450;
	$btsellratebt = 453; */
	$btsellratebt2 = $btsellratebt + 3;
	$paxfulkey = 'cx7meGkOvX2Q3TBk6Kny7pZn8Z95KxHm';
	$paxfulsecret = 'HtPZlXYmm0uU7SEAB69UqsWLtQqwdwAr';
	$ngncode = '103';
	$myrcode = '100';
	$usdcode = '144';
	$compare = "notreceived";	
	$paid = 'marked_paid';
	$tradecancelled = "trade_cancelled";
	$tradeexpired = "trade_expired";
	$released = 'released_completed';
	$feedbackreceived = 'feedback_received_chat';
	$justpaid = 'notpaid';
	$owneruser = 'swayhost';
	$owneruser2 = 'swayhost';
	$allowed = 1400;
	$getdirect = false;
	$thisnacctno = "";
	$thisncode = "";
	$thisnbankname = "";
	$loggood = './log.txt';
	$loggood1 = './././loggood.txt';
	$logfail1 = './././logfail.txt';
	
	//still not hitting it
	//let use run runtrade.php?trade= direc
	
	/* $midofferhash = 'C4qSvy5wNwp';
	$maxofferhash = 'CZMNA3bhHdu';
	$minofferhash = 'KXuEbYz9HnE';  */
	
	//You can change the Offer Type in offercategory
	$offerhash1 = '1m72RkcbuzN';//Bank Transfer
	$offerhash2 = '1m72RkcbuzN';//Bank Transfer
	$offerhash3 = '1m72RkcbuzN';//Bank Transfer
	$offerhash4 = '3KUeV7VzvBa';//Bank Transfer
	$offerhash5 = 'oy5KrpqhPKr';//Cash Deposit to Bank
	$offerhash6 = 'Mu9frkJhgyp';//Domestic Wire Transfer
	$offerhash7 = 'UqzSesXxzA2';//Western Union
	
	
	
	$offerhash1r = 'i8enLuaLiB7';//International Wire Transfer (SWIFT)PqKjivovJ6n
	$offerhash2r = 'izdRjmvVjGJ';//Domestic Wire Transfer
	$offerhash3r = 'tTexRdFJtkA';//Cash Deposit to Bank
	$offerhash4r = 'qeUiCjiZszq';//Bank Transfer
	$offerhash5r = 'BMkNe81YWid';//Domestic Wire Transfer
	$offerhash6r = 'NiSrxWTJidQ';//Cash Deposit to Bank
	$offerhash7r = 'vfjk9WyniFZ';//International Wire Transfer (SWIFT)
	
	
	
	
	//These are rates
	$offerrate1 = 440;
	$offerrate2 = 440;
	$offerrate3 = 440;
	$offerrate4 = 445;
	$offerrate5 = 447;
	$offerrate6 = 450;
	$offerrate7 = 0.90; 
	
	$offerrate1r = 470;
	$offerrate2r = 480;
	$offerrate3r = 490;
	$offerrate4r = 475;
	$offerrate5r = 480;
	$offerrate6r = 485;
	$offerrate7r = 0.90; 
		
		
	//These are CurrencyCode
	$offercode1 = $ngncode;
	$offercode2 = $ngncode;
	$offercode3 = $ngncode;
	 $offercode4 = $ngncode;
	$offercode5 = $ngncode;
	$offercode6 = $ngncode;
	$offercode7 = $usdcode; 
	
	$offercode1r = $ngncode;
	$offercode2r = $ngncode;
	$offercode3r = $ngncode;
	$offercode4r = $ngncode;
	$offercode5r = $ngncode;
	$offercode6r = $ngncode;
	$offercode7r = $usdcode;
	
	
	$offercur1 = 'NGN';
	$offercur2 = 'NGN';
	$offercur3 = 'NGN';
	$offercur4 = 'NGN';
	$offercur5 = 'NGN';
	$offercur6 = 'NGN';
	$offercur7 = 'USD';
	
	$offercur1r = 'NGN';
	$offercur2r = 'NGN';
	$offercur3r = 'NGN';
	$offercur4r = 'NGN';
	$offercur5r = 'NGN';
	$offercur6r = 'NGN';
	$offercur7r = 'USD';
	
	$offercurarr = array('NGN'=>$offerrate1, 'MYR'=>4.05, 'USD'=>1, 'NGN2'=>($offerrate1 + 6), 'MYR2'=>4.05, 'USD2'=>1);
	
	$offertraderatearr = array($offerhash1=>$offerrate1, $offerhash2=>$offerrate2, $offerhash3=>$offerrate3 , $offerhash4=>$offerrate4, $offerhash5=>$offerrate5, $offerhash6=>$offerrate6, $offerhash7=>$offerrate7 );
	
	$offertraderatearr2 = array($offerhash1r=>$offerrate1r, $offerhash2r=>$offerrate2r, $offerhash3r=>$offerrate3r , $offerhash4r=>$offerrate4r, $offerhash5r=>$offerrate5r, $offerhash6r=>$offerrate6r, $offerhash7r=>$offerrate7r );
	
	
	$offertradecodearr = array($offerhash1=>$offercode1, $offerhash2=>$offercode2, $offerhash3=>$offercode3 , $offerhash4=>$offercode4, $offerhash5=>$offercode5, $offerhash6=>$offercode6, $offerhash7=>$offercode7 );
	
	$offertradecodearr2 = array($offerhash1r=>$offercode1r, $offerhash2r=>$offercode2r, $offerhash3r=>$offercode3r , $offerhash4r=>$offercode4r, $offerhash5r=>$offercode5r, $offerhash6r=>$offercode6r, $offerhash7r=>$offercode7r );
	
	
	$offertradecurarr = array($offerhash1=>$offercur1, $offerhash2=>$offercur2, $offerhash3=>$offercur3 , $offerhash4=>$offercur4, $offerhash5=>$offercur5, $offerhash6=>$offercur6, $offerhash7=>$offercur7 );
	
	$offertradecurarr2 = array($offerhash1r=>$offercur1r, $offerhash2r=>$offercur2r, $offerhash3r=>$offercur3r , $offerhash4r=>$offercur4r, $offerhash5r=>$offercur5r, $offerhash6r=>$offercur6r, $offerhash7r=>$offercur7r );
	
	$offerarr = array($offerhash1, $offerhash2, $offerhash3 , $offerhash4, $offerhash5, $offerhash6, $offerhash7 );
	
	$offerarr2 = array($offerhash1r, $offerhash2r, $offerhash3r , $offerhash4r, $offerhash5r, $offerhash6r, $offerhash7r );
	
	/* $offerarr3 = array($offerhashr1, $offerhashr2, $offerhashr3, $offerhashr4, $offerhashr5, $offerhashr6, $offerhashr7);
	
	$offerarrr = array($offerhash1r, $offerhash2r, $offerhash3r, $offerhash4r, $offerhash5r, $offerhash6r, $offerhash7r); */
		
	$offercategory = array("International Wire Transfer (SWIFT)"=>array($offerhash1r, $offerhash7r), "Domestic Wire Transfer"=>array($offerhash2r, $offerhash5r), "Cash Deposit to Bank"=>array($offerhash3r, $offerhash6r), "Bank Transfer"=>array($offerhash4r));
	
	
	
	$paxblockArr = array();
	
	$paxoweArr = array('','');
	
	$bankrestrictArr = array();
	//$bankrestrictArr = array('033','044','063');
	
	$codelist = array('044','023','063','050','070','011','214','058','030','082','076','221','068','232','032','033','215','035','057','090175','090267','305','301','101','100','090110');
	
	$rubieslist = array('ACCESS BANK PLC','CITIBANK NIGERIA','ACCESS(DIAMOND)BANK','ECOBANK BANK','FIDELITY BANK','FIRST BANK OF NIGERIA PLC','FCMB','GUARANTY TRUST BANK PLC','HERITAGE BANK','KEYSTONE BANK','POLARIS BANK','STANBIC IBTC BANK PLC','STANDARD CHARTERED BANK','STERLING BANK PLC','UNION BANK','UNITED BANK FOR AFRICA PLC','UNITY BANK','WEMA/ALAT','ZENITH BANK PLC','RUBIES MICROFINANCE BANK','KUDA MICROFINANCE BANK','PAYCOM(OPAY)','JAIZ BANK','PROVIDUS BANK','SUNTRUST BANK','VFD MICROFINANCE BANK');
	
	$rootdir ='http://178.79.134.138/paxful/Main2/';
	
	
	$rubiespaxacct = "4460593884"; //
		
	
	
?>