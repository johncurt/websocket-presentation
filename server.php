<?php
use Ratchet\Server\IoServer;
use realTimeComm\Router;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;

//load composer
require 'vendor/autoload.php';

$app = new Router();

//setup server
$server = IoServer::factory(
	new HttpServer(
		new WsServer(
			$app
		)
	),
	8100
);

$coins = ['usd'=>0];

//$server->loop->addPeriodicTimer(5, function() use ($app, &$coins){
//	$ticker = json_decode(file_get_contents('https://blockchain.info/ticker'));
//	$usd = $ticker->USD->last;
//	if ($coins['usd']!==$usd){
//		$coins['usd']=$usd;
//		$message = json_encode(['action'=>'bitcoin', 'price'=>number_format($usd,2), 'error'=>false]);
//		foreach ($app->controller->clients as $conn){
//			$conn->send($message);
//		}
//	}
//});

//run server
$server->run();



$websocketClient = \Ratchet\Client\connect('wss://ws.pusherapp.com/app/de504dc5763aeef9ff52?protocol=7&client=js&version=2.1.6&flash=false')->then(function($conn) use ($app, &$coins, &$conn) {
	$conn->on('message', function($msg) use ($conn, &$coins, $app) {
		$msgObject = json_decode($msg);
		var_dump($msgObject);
		$usd = $msgObject->data->price;
		if ($coins['usd']!==$usd){
			$coins['usd']=$usd;
			$message = json_encode(['action'=>'bitcoin', 'price'=>number_format($usd,2), 'error'=>false]);
			foreach ($app->controller->clients as $serverConn){
				$serverConn->send($message);
			}
		}
	});
	$conn->send('{"event":"pusher:subscribe","data":{"channel":"live_orders"}}');
}, function ($e) {
	echo "Could not connect: {$e->getMessage()}\n";
});

var_dump($websocketClient);