
var wsURL = 'ws://'+window.location.hostname+':8100';
// wsURL = 'ws://54.174.15.61/:8100';
var wsConn = null;
var jBtc;
var jEth;
function startConn(){
    try {
        wsConn = new WebSocket(wsURL);
        wsConn.onopen = function (e) {
            wsConnected = true;
            console.log("Connection established!");
        };
        wsConn.onmessage = function (e) {
            var incomingMessage;

            try {
                incomingMessage = JSON.parse(e.data);
            } catch (e) {
                console.log(e + ': ' + e.data);
                incomingMessage = [];
            }

            console.log(incomingMessage); //for debug

            switch (incomingMessage.action) {
                case 'notification':
                    notification(incomingMessage.message, incomingMessage.error);
                    break;
                case 'bitcoin':
                    if (jBtc.text()!==incomingMessage.price) jBtc.text(incomingMessage.price);
                    break;
                case 'ether':
                    if (jEth.text()!==incomingMessage.price) jEth.text(incomingMessage.price);
                    break;
                default:
                    console.log('Error: Invalid action received: ' + incomingMessage.type);
                    break;
            }

        };
        wsConn.onclose = function (e) {
            console.log("Connection closed...");
            checkConn(); //reconnect now!
        };
    } catch (e){
        console.log(e);
    }
}

function checkConn(){
    if(!wsConn || wsConn.readyState === 3) startConn();
}

$(document).ready(function() {
    jBtc = jQuery('#bitCoinPrice');
    jEth = jQuery('#etherPrice');
    startConn();
    setInterval(checkConn,5000);
});


function notification(message, isError){
    $.jGrowl(message);
}