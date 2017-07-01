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

$server->loop->addPeriodicTimer(5, function() use ($app, $coins){
	$ticker = json_decode(file_get_contents('https://blockchain.info/ticker'));
	$usd = $ticker->USD->last;
	if ($coins['usd']!==$usd){
		$coins['usd']=$usd;
		$message = json_encode(['action'=>'bitcoin', 'price'=>number_format($usd,2), 'error'=>false]);
		foreach ($app->controller->clients as $conn){
			$conn->send($message);
		}
	}
});

//run server
$server->run();
