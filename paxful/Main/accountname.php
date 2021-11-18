<?php

function cors() {
	// Allow from any origin
	if (isset($_SERVER['HTTP_ORIGIN'])) {
		// Decide if the origin in $_SERVER['HTTP_ORIGIN'] is one
		// you want to allow, and if so:
		header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
		header('Access-Control-Allow-Credentials: true');
		header('Access-Control-Max-Age: 86400');    // cache for 1 day
	}

	// Access-Control headers are received during OPTIONS requests
	if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {

		if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD']))
			// may also be using PUT, PATCH, HEAD etc
			header("Access-Control-Allow-Methods: GET, POST, OPTIONS");         

		if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']))
			header("Access-Control-Allow-Headers: {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}");

		exit(0);
	}
}
cors();

if (isset($_GET['account']) && isset($_GET['code'])) {
	
	$account = $_GET['account'];
	$bankcode = $_GET['code'];

	
	
	$codelist = array('044','023','063','050','070','011','214','058','030','082','076','221','068','232','032','033','215','035','057','090175','090267','305','301','101','100','090110');
	$rubieslist = array('ACCESS BANK PLC','CITIBANK NIGERIA','ACCESS(DIAMOND)BANK','ECOBANK BANK','FIDELITY BANK','FIRST BANK OF NIGERIA PLC','FCMB','GUARANTY TRUST BANK PLC','HERITAGE BANK','KEYSTONE BANK','POLARIS BANK','STANBIC IBTC BANK PLC','STANDARD CHARTERED BANK','STERLING BANK PLC','UNION BANK','UNITED BANK FOR AFRICA PLC','UNITY BANK','WEMA/ALAT','ZENITH BANK PLC','RUBIES MICROFINANCE BANK','KUDA MICROFINANCE BANK','PAYCOM(OPAY)','JAIZ BANK','PROVIDUS BANK','SUNTRUST BANK','VFD MICROFINANCE BANK');
	
	
	$response = '{"responsecode":"00","banklist":[{"bankname":"OLABISI ONABANJO UNIV MFB","bankcode":"090272"},{"bankname":"3LINE","bankcode":"110005"},{"bankname":"AB MICROFINANCE BANK","bankcode":"090270"},{"bankname":"ABBEY MORTGAGE BANK","bankcode":"070010"},{"bankname":"ABOVE ONLY MICROFINANCE BANK","bankcode":"090260"},{"bankname":"ABU MICROFINANCE BANK","bankcode":"090197"},{"bankname":"ACCESS BANK PLC","bankcode":"000014"},{"bankname":"ACCESS YELLO AND BETA","bankcode":"100052"},{"bankname":"ACCESS(DIAMOND)BANK","bankcode":"000005"},{"bankname":"ACCION MICROFINANCE BANK","bankcode":"090134"},{"bankname":"ADDOSSER MICROFINANCE BANK","bankcode":"090160"},{"bankname":"ADEYEMI COLLEGE STAFF MFB","bankcode":"090268"},{"bankname":"AFEKHAFE MICROFINANCE BANK","bankcode":"090292"},{"bankname":"AG MORTGAGE BANK","bankcode":"100028"},{"bankname":"ALEKUN MICROFINANCE BANK","bankcode":"090259"},{"bankname":"ALEKUN MICROFINANCE BANK","bankcode":"090259"},{"bankname":"ALERT MICROFINANCE BANK","bankcode":"090297"},{"bankname":"AL-HAYAT MICROFINANCE BANK","bankcode":"090277"},{"bankname":"ALLWORKERS MICROFINANCE BANK","bankcode":"090131"},{"bankname":"ALPHA KAPITAL MFB","bankcode":"090169"},{"bankname":"AMJU UNIQUE MFB","bankcode":"090180"},{"bankname":"AMML MICROFINANCE BANK","bankcode":"090116"},{"bankname":"APEKS MICROFINANCE BANK","bankcode":"090143"},{"bankname":"ARISE MICROFINANCE BANK","bankcode":"090282"},{"bankname":"ASO SAVINGS AND LOANS","bankcode":"090001"},{"bankname":"ASSETMATRIX MFB","bankcode":"090287"},{"bankname":"ASTRAPOLARIS MICROFINANCE","bankcode":"090172"},{"bankname":"BAINES CREDIT MFB","bankcode":"090188"},{"bankname":"BALOGUN GAMBARI MFB","bankcode":"090326"},{"bankname":"BAOBAB MICROFINANCE BANK","bankcode":"090136"},{"bankname":"BOCTRUST MICROFINANCE BANK","bankcode":"090117"},{"bankname":"BOSAK MICROFINANCE BANK","bankcode":"090176"},{"bankname":"BOWEN MICROFINANCE BANK","bankcode":"090148"},{"bankname":"BRENT MORTGAGE BANK","bankcode":"070015"},{"bankname":"BRETHREN MICROFINANCE BANK","bankcode":"090293"},{"bankname":"BRIGHTWAY MICROFINANCE BANK","bankcode":"090308"},{"bankname":"CEMCS MICROFINANCE BANK","bankcode":"090154"},{"bankname":"CHIKUM MICROFINANCE BANK","bankcode":"090141"},{"bankname":"CIT MICROFINANCE BANK","bankcode":"090144"},{"bankname":"CITIBANK NIGERIA","bankcode":"000009"},{"bankname":"CONSUMER MICROFINANCE BANK","bankcode":"090130"},{"bankname":"CONTEC GLOBAL INFOTECH LTD","bankcode":"100032"},{"bankname":"CORONATION MERCHANT BANK LIM","bankcode":"060001"},{"bankname":"COVENANT","bankcode":"070006"},{"bankname":"COVENANT MICROFINANCE BANK","bankcode":"070006"},{"bankname":"CREDIT AFRIQUE MFB","bankcode":"090159"},{"bankname":"e-BARCS MICROFINANCE BANK ","bankcode":"090156"},{"bankname":"ECOBANK BANK","bankcode":"000010"},{"bankname":"ECOBANK XPRESS ACCOUNT","bankcode":"100008"},{"bankname":"EDFIN MICROFINANCE BANK","bankcode":"090310"},{"bankname":"EKONDO MICROFINANCE BANK","bankcode":"090097"},{"bankname":"EMERALDS MICROFINANCE BANK","bankcode":"090273"},{"bankname":"EMPIRE TRUST MFB","bankcode":"090114"},{"bankname":"ENTERPRISE BANK","bankcode":"000019"},{"bankname":"ESAN MICROFINANCE BANK","bankcode":"090189"},{"bankname":"ESO-E MICROFINANCE BANK","bankcode":"090166"},{"bankname":"E-TRANZACT","bankcode":"100006"},{"bankname":"EVANGEL MICROFINANCE BANK","bankcode":"090304"},{"bankname":"FAST MICROFINANCE BANK","bankcode":"090179"},{"bankname":"FBNQUEST MERCHANT BANK","bankcode":"060002"},{"bankname":"FCMB","bankcode":"000003"},{"bankname":"FCMB EASY ACCOUNT","bankcode":"100031"},{"bankname":"FCT MICROFINANCE BANK","bankcode":"090290"},{"bankname":"FEDERAL UNIVERSITY DUTSE MFB","bankcode":"090318"},{"bankname":"FEDPOLY NASARAWA MFB","bankcode":"090298"},{"bankname":"FFS MICROFINANCE","bankcode":"090153"},{"bankname":"FIDELITY BANK","bankcode":"000007"},{"bankname":"FIDELITY MOBILE","bankcode":"100019"},{"bankname":"FIDFUND MICROFINANCE BANK","bankcode":"090126"},{"bankname":"FINATRUST MICROFINANCE BANK","bankcode":"090111"},{"bankname":"FIRST BANK OF NIGERIA PLC","bankcode":"000016"},{"bankname":"FIRST GEN MORTGAGE BANK","bankcode":"070014"},{"bankname":"FIRST OPTION MFB","bankcode":"090285"},{"bankname":"FIRST ROYAL MFB","bankcode":"090164"},{"bankname":"FIRST TRUST MORTGAGE BANK PL","bankcode":"090005"},{"bankname":"FIRST TRUST MORTGAGE BANK PL","bankcode":"090107"},{"bankname":"FLUTTERWAVE TECH SOLUTIONS","bankcode":"110002"},{"bankname":"FORTIS MICROFINANCE BANK","bankcode":"070002"},{"bankname":"FSDH MERCHANT BANK","bankcode":"400001"},{"bankname":"GASHUA MICROFINANCE BANK","bankcode":"090168"},{"bankname":"GATEWAY MORTGAGE BANK","bankcode":"070009"},{"bankname":"GLOBUS BANK","bankcode":"000027"},{"bankname":"GLORY MICROFINANCE BANK","bankcode":"090278"},{"bankname":"GOMONEY","bankcode":"100022"},{"bankname":"GOWANS MICROFINANCE BANK","bankcode":"090122"},{"bankname":"GREENBANK MICROFINANCE BANK","bankcode":"090178"},{"bankname":"GREENVILLE MICROFINANCE BANK","bankcode":"090269"},{"bankname":"GROOMING MICROFINANCE BANK","bankcode":"090195"},{"bankname":"GUARANTY TRUST BANK PLC","bankcode":"000013"},{"bankname":"HAGGAI MORTGAGE BANK LIMITED","bankcode":"070017"},{"bankname":"HAGGAI MORTGAGE BANK LTD","bankcode":"070017"},{"bankname":"HASAL MICROFINANCE BANK","bankcode":"090121"},{"bankname":"HERITAGE BANK","bankcode":"000020"},{"bankname":"IBILE MICROFINANCE BANK","bankcode":"090118"},{"bankname":"IKIRE MICROFINANCE BANK","bankcode":"090275"},{"bankname":"IMO STATE MICROFINANCE BANK","bankcode":"090258"},{"bankname":"IMPERIAL HOMES MORTGAGE BANK","bankcode":"100024"},{"bankname":"INFINITY MICROFINANCE BANK","bankcode":"090157"},{"bankname":"INFINITY TRUST MORTGAGE BANK","bankcode":"070016"},{"bankname":"INNOVECTIVES KESH","bankcode":"100029"},{"bankname":"JAIZ BANK","bankcode":"000006"},{"bankname":"JUBILEE LIFE","bankcode":"090003"},{"bankname":"KADPOLY MICROFINANCE BANK","bankcode":"090320"},{"bankname":"KCMB MICROFINANCE BANK","bankcode":"090191"},{"bankname":"KEYSTONE BANK","bankcode":"000002"},{"bankname":"KUDA MICROFINANCE BANK","bankcode":"090267"},{"bankname":"LAGOS BUILDING INV COMPANY","bankcode":"070012"},{"bankname":"LAPO MICROFINANCE BANK","bankcode":"090177"},{"bankname":"LAVENDER MICROFINANCE BANK ","bankcode":"090271"},{"bankname":"LOVONUS MICROFINANCE BANK","bankcode":"090265"},{"bankname":"MAINSTREET MICROFINANCE BANK","bankcode":"090171"},{"bankname":"MEGAPRAISE MICROFINANCE BANK","bankcode":"090280"},{"bankname":"MIDLAND MICROFINANCE BANK","bankcode":"090192"},{"bankname":"MIMONEY INTELLIFIN SOLUTIONS","bankcode":"100027"},{"bankname":"MINT-FINEX MFB","bankcode":"090281"},{"bankname":"MONEY TRUST MFB","bankcode":"090129"},{"bankname":"MUTUAL BENEFITS MIFB","bankcode":"090190"},{"bankname":"MUTUAL TRUST MFB","bankcode":"090151"},{"bankname":"NDIORAH MICROFINANCE BANK","bankcode":"090128"},{"bankname":"NEW DAWN MICROFINANCE BANK","bankcode":"090205"},{"bankname":"NEW PRUDENTIAL BANK","bankcode":"090108"},{"bankname":"NIGERIAN NAVY MFB","bankcode":"090263"},{"bankname":"NIP VIRTUAL BANK","bankcode":"999999"},{"bankname":"NIRSAL MICROFINANCE BANK","bankcode":"090194"},{"bankname":"NNEW WOMEN MICROFINANCE BANK","bankcode":"090283"},{"bankname":"NOVA MERCHANT BANK","bankcode":"060003"},{"bankname":"Nova Merchant Bank Ltd","bankcode":"060003"},{"bankname":"NPF MICROFINANCE","bankcode":"070001"},{"bankname":"OHAFIA MICROFINANCE BANK","bankcode":"090119"},{"bankname":"OKPOGA MICROFINANCE BANK","bankcode":"090161"},{"bankname":"OMIYE MICROFINANCE BANK","bankcode":"090295"},{"bankname":"OMOLUABI MORTGAGE BANK","bankcode":"070007"},{"bankname":"ONE FINANCE","bankcode":"100026"},{"bankname":"ONE FINANCE","bankcode":"100026"},{"bankname":"PAGA","bankcode":"100002"},{"bankname":"PAGE FINANCIALS","bankcode":"070008"},{"bankname":"PALMPAY LIMITED","bankcode":"100033"},{"bankname":"PARALLEX MFB","bankcode":"090004"},{"bankname":"PARKWAY-READYCASH","bankcode":"100003"},{"bankname":"PATRICKGOLD MFB","bankcode":"090317"},{"bankname":"PAYATTITUDE ONLINE","bankcode":"110001"},{"bankname":"PAYCOM(OPAY)","bankcode":"100004"},{"bankname":"PECANTRUST MICROFINANCE BANK","bankcode":"090137"},{"bankname":"PENNYWISE MICROFINANCE BANK","bankcode":"090196"},{"bankname":"PERSONAL TRUST MFB","bankcode":"090135"},{"bankname":"PETRA MICROFINANCE BANK","bankcode":"090165"},{"bankname":"PILLAR MICROFINANCE BANK","bankcode":"090289"},{"bankname":"PLATINUM MORTGAGE BANK","bankcode":"070013"},{"bankname":"POLARIS BANK","bankcode":"000008"},{"bankname":"POLYUWANA MICROFINANCE BANK","bankcode":"090296"},{"bankname":"PRESTIGE MICROFINANCE BANK","bankcode":"090274"},{"bankname":"PROVIDUS BANK","bankcode":"000023"},{"bankname":"QUICKFUND MICROFINANCE BANK","bankcode":"090261"},{"bankname":"QUICKFUND MICROFINANCE BANK","bankcode":"090261"},{"bankname":"RAHAMA MICROFINANCE BANK","bankcode":"090170"},{"bankname":"RAND MERCHANT BANK","bankcode":"000024"},{"bankname":"REFUGE MORTGAGE BANK","bankcode":"070011"},{"bankname":"REGENT MICROFINANCE BANK","bankcode":"090125"},{"bankname":"RELIANCE MICROFINANCE BANK","bankcode":"090173"},{"bankname":"RENMONEY MICROFINANCE BANK","bankcode":"090198"},{"bankname":"RICHWAY MICROFINANCE BANK","bankcode":"090132"},{"bankname":"ROYAL EXCHANGE MFB","bankcode":"090138"},{"bankname":"RUBIES MICROFINANCE BANK","bankcode":"090175"},{"bankname":"SAFE HAVEN MICROFINANCE BANK","bankcode":"090286"},{"bankname":"SAFETRUST MORTGAGE BANK ","bankcode":"090006"},{"bankname":"SAGAMU MICROFINANCE BANK","bankcode":"090140"},{"bankname":"SEED CAPITAL MFB","bankcode":"090112"},{"bankname":"SPARKLE","bankcode":"090325"},{"bankname":"STANBIC IBTC @EASE WAL","bankcode":"100007"},{"bankname":"STANBIC IBTC BANK PLC","bankcode":"000012"},{"bankname":"STANDARD CHARTERED BANK","bankcode":"000021"},{"bankname":"STELLAS MICROFINANCE BANK","bankcode":"090262"},{"bankname":"STERLING BANK PLC","bankcode":"000001"},{"bankname":"SULSPAP MICROFINANCE BANK","bankcode":"090305"},{"bankname":"SUNTRUST BANK","bankcode":"000022"},{"bankname":"TAGPAY","bankcode":"100023"},{"bankname":"TAJ BANK","bankcode":"000026"},{"bankname":"TCF MICROFINANCE BANK","bankcode":"090115"},{"bankname":"TEASY MOBILE","bankcode":"100010"},{"bankname":"TITAN TRUST BANK","bankcode":"000025"},{"bankname":"TRIDENT MICROFINANCE BANK","bankcode":"090146"},{"bankname":"TRUSTBANC J6 MFB","bankcode":"090123"},{"bankname":"TRUSTFUND MICROFINANCE BANK","bankcode":"090276"},{"bankname":"UNIBEN MICROFINANCE BANK","bankcode":"090266"},{"bankname":"UNICAL MICROFINANCE BANK","bankcode":"090193"},{"bankname":"UNION BANK","bankcode":"000018"},{"bankname":"UNITED BANK FOR AFRICA PLC","bankcode":"000004"},{"bankname":"UNITY BANK","bankcode":"000011"},{"bankname":"VFD MICROFINANCE BANK","bankcode":"090110"},{"bankname":"VIRTUE MICROFINANCE BANK","bankcode":"090150"},{"bankname":"VISA MICROFIN BANK","bankcode":"090139"},{"bankname":"WEMA/ALAT","bankcode":"000017"},{"bankname":"WETLAND MICROFINANCE BANK","bankcode":"090120"},{"bankname":"YES MICROFINANCE BANK","bankcode":"090142"},{"bankname":"ZENITH BANK PLC","bankcode":"000015"},{"bankname":"ZINTERNET","bankcode":"100025"}],"responsemessage":"success"}';

	$resp = json_decode($response, true);	
	$allrubies = $resp['banklist'];
	$key = array_search($bankcode, $codelist);
	$targetbank = $rubieslist[$key];
	
	for($i=0; $i<count($allrubies); $i++){
		
		if($allrubies[$i]['bankname'] == $targetbank)
		{
			$rubiescode = $allrubies[$i]['bankcode'];
			$i = count($allrubies);
		}		
	}
	
	$query = array(		
		"accountnumber" => $account,
		"bankcode" => $rubiescode
	);
	
	$data_string = json_encode($query);                                                                    
	$ch = curl_init('https://openapi.rubiesbank.io/v1/nameenquiry');                                                                      
	curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
	curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);                                              
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_HTTPHEADER, array(
		'Authorization: ', //RUBIES AUTHORIZATION KEY HERE
		'Content-Type: application/json'
	));

	$response = curl_exec($ch);
	$header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
	$header = substr($response, 0, $header_size);
	$body = substr($response, $header_size);

	curl_close($ch);

	$resp = json_decode($response, true);	
	
	if($resp['accountname'] && $resp['responsecode'] == "00" && $resp['accountname'] !== ''){
		echo $resp['accountname'];
	}else{
		echo "";
	}
	

	
	
}

?>