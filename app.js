var trigger = null;
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
                url: "http://139.162.89.151/paxful/Main2/checkopentrade.php",
                json: true
        }, function(error, response, body){
//              console.log(tN);
                Start();
        })
}



function Start(){
        trigger = setTimeout(Check, 60000);
}