const crypto = require('crypto');
const express = require('express');
const app = express();
const port = 7000;
const bodyParser = require('body-parser');
const apiSecret = '';

app.use(bodyParser.urlencoded({
	extended: true
}));

app.use(bodyParser.json());

app.use((req, res, next) => {
	if (!Object.keys(req.body).length && !req.get('x-paxful-signature')) {
        //console.log('Address verification request received.');
        const challengeHeader = 'x-paxful-request-challenge';
        res.set(challengeHeader, req.get(challengeHeader));
        res.end();
    } else {
        next();
    }
});

app.use((req, res, next) => {
    const providedSignature = req.get('x-paxful-signature');
    const calculatedSignature = crypto.createHmac('sha256', apiSecret).update(JSON.stringify(req.body)).digest('hex');
    if (providedSignature !== calculatedSignature) {
        //console.log('Request signature verification failed.');
        res.status(403).end();
    } else {
        next();
    }
});

function runApp(run, func){	
	var requesturl = require("request");
	requesturl({
        url: "http://139.162.89.151/paxful/Main2/checkofferview.php"+run,
		json: true
	}, function(error, response, body){
		const challengrHeader = 'x-paxful-request-challenge';
		func(challengrHeader);		
	});
}

app.post('*', async (req, res) => {
	var x, paxful, signature, request, challenge, reqsig, mes, message, c, rating;
	//{"time":1604754937,"type":"offer.viewed","payload":{"offer_hash":"9LfiTxmjEHk","username":"adekunleogundijo"}}
	if(req.headers['x-paxful-signature']){
		reqsig = req.headers['x-paxful-signature'];
		c = 'x-paxful-signature';
		res.set(c, req.get(c));

		if(req.body.type && (req.body.type == 'trade.chat.message' || req.body.type == 'trade.incoming'))
		{
			message = req.body.payload.trade_id;
		}
		else if(req.body.type && (req.body.type == 'bitcoin.purchased' || req.body.type == 'feedback.received'))
		{
			message = req.body.payload.trade_hash;		
			if(req.body.type == 'feedback.received')
			{
				rating = req.body.payload.rating;
				message = message+'&rating='+rating;
			}

		}
		else if(req.body.type && req.body.type == 'offer.viewed')
		{
			message = req.body.payload.offer_hash+'&time='+req.body.time;
		}
		
	}else if(req.headers['x-paxful-request-challenge']){
		reqsig = req.headers['x-paxful-request-challenge'];
		c = 'x-paxful-request-challenge';
		res.set(c, req.get(c));
	}else{
		c = 'x-paxful-request-challenge';
		res.set(c, req.get(c));
	}
	
	//if(req.body.type && (req.body.type == 'trade.chat.message' || req.body.type == 'bitcoin.purchased' || req.body.type == 'trade.incoming' || req.body.type == 'feedback.received'))
	
	if(req.body.type && req.body.type == 'offer.viewed')
	{
		mes = '?tradehash='+message;
		runApp(mes, function(r){
			console.log('200 - Offer Viewed:');
			console.log(JSON.stringify(req.body));
			console.log('');
			res.end();
		});
	}
	
	/*
	else if(req.body.type && req.body.type !== 'trade.chat.message')
	{
		res.end();
	}
	else
	{
		
		res.end();
	}
	
*/    
});

app.get('*', function(req, res) {	
	var x, paxful, signature, request, challenge, reqsig, mes, message, c;	
	reqsig = req.headers['x-paxful-signature'];
	c = 'x-paxful-signature';
	res.set(c, req.get(c));
	res.end();
});

app.listen(port, function(){
	console.log('Paxful Server On '+port);
});
