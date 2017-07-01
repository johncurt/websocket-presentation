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

$ticker = json_decode(file_get_contents('https://blockchain.info/ticker'));
$btc = $ticker->USD->last;
$ticker = json_decode(file_get_contents('https://etherlive.ethnews.com/api/v2/live?exchange=All&currency=USD'));
$eth = $ticker->price;
$coins = ['bitcoin'=>$btc,'ether'=>$eth];


$server->loop->addPeriodicTimer(10, function() use ($app, &$coins){
	$ticker = json_decode(file_get_contents('https://blockchain.info/ticker'));
	$usd = $ticker->USD->last;
	if ($coins['bitcoin']!==$usd){
		$coins['bitcoin']=$usd;
		$message = json_encode(['action'=>'bitcoin', 'price'=>number_format($usd,2), 'error'=>false]);
		foreach ($app->controller->clients as $conn){
			$conn->send($message);
		}
	}
});
$server->loop->addPeriodicTimer(10, function() use ($app, &$coins){
	$ticker = json_decode(file_get_contents('https://etherlive.ethnews.com/api/v2/live?exchange=All&currency=USD'));
	$usd = $ticker->price;
	if ($coins['ether']!==$usd){
		$coins['ether']=$usd;
		$message = json_encode(['action'=>'ether', 'price'=>number_format($usd,2), 'error'=>false]);
		foreach ($app->controller->clients as $conn){
			$conn->send($message);
		}
	}
});

//run server
$server->run();



