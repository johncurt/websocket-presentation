<?php

use Ratchet\Server\IoServer;
use realTimeComm\Router;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;

//load composer
require 'vendor/autoload.php';

$ticker = json_decode(file_get_contents('https://blockchain.info/ticker'));
$btc = $ticker->USD->last;
$ticker = json_decode(file_get_contents('https://etherlive.ethnews.com/api/v2/live?exchange=All&currency=USD'));
$eth = $ticker->price;
$coins = [ 'bitcoin' => $btc, 'ether' => $eth ];

$app = new Router($coins);

$wsServer = new WsServer($app);

//setup server
$server = IoServer::factory(
	new HttpServer(
		$wsServer
	),
	8100
);

$httpClient = new React\HttpClient\Client($server->loop);

$server->loop->addPeriodicTimer(10, function () use (&$app, &$coins, $httpClient) {
	$jsonData = '';
	$request = $httpClient->request('GET', 'https://blockchain.info/ticker');
	$request->on('response', function ($response) use (&$app, &$coins, &$jsonData) {
		$response->on('data', function ($chunk) use (&$jsonData) {
			$jsonData .= $chunk;
		});
		$response->on('end', function () use (&$app, &$coins, &$jsonData) {
			$ticker = json_decode($jsonData);
			$usd = $ticker->USD->last;
			if ($coins['bitcoin'] !== $usd) {
				$coins['bitcoin'] = $usd;
				$message = json_encode([ 'action' => 'bitcoin', 'price' => number_format($usd, 2), 'error' => false ]);
				foreach ($app->clients as $conn) {
					$conn->send($message);
				}
			}
		});
	});
	$request->on('error', function (\Exception $e) {
		echo $e;
	});
	$request->end();
	unset($request);
});
$server->loop->addPeriodicTimer(10, function () use (&$app, &$coins, $httpClient) {
	$jsonData = '';
	$request = $httpClient->request('GET', 'https://etherlive.ethnews.com/api/v2/live?exchange=All&currency=USD');
	$request->on('response', function ($response) use (&$app, &$coins, &$jsonData) {
		$response->on('data', function ($chunk) use (&$jsonData) {
			$jsonData .= $chunk;
		});
		$response->on('end', function () use (&$app, &$coins, &$jsonData) {
			$ticker = json_decode($jsonData);
			$usd = $ticker->price;
			if ($coins['ether'] !== $usd) {
				$coins['ether'] = $usd;
				$message = json_encode([ 'action' => 'ether', 'price' => number_format($usd, 2), 'error' => false ]);
				foreach ($app->clients as $conn) {
					$conn->send($message);
				}
			}
		});
	});
	$request->on('error', function (\Exception $e) {
		echo $e;
	});
	$request->end();
	unset($request);
	print "Getting ether...  mem: ".(memory_get_usage()/1024)."K\n";
});

$server->loop->addPeriodicTimer(120, function(){print "Collected ".gc_collect_cycles()." cycles\n";});

$wsServer->enableKeepAlive($server->loop, 30);


//run server
$server->run();



