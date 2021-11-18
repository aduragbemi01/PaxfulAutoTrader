var tN = 0;
var trigger;
Start();

function Check(){
	CheckApp();
}

function CheckApp(){
	tN++;
	var request = require("request");
	request({
		url: "http://139.162.89.151/paxful/Main2/checkofferview2.php",
		json: true
	}, function(error, response, body){
	//console.log(tN);
		Start();
	})
}



function Start(){
	trigger = setTimeout(Check, 10000);
}
		