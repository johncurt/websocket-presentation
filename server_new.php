<?php
// Make sure composer dependencies have been installed
require __DIR__ . '/vendor/autoload.php';

$router = new \realTimeComm\Router();

$app = new Ratchet\App('localhost', 8100);
$app->route('/', $router);

$app->flashServer->loop->addPeriodicTimer(5, function () use ($router) {
	$ticker = json_decode(file_get_contents('https://blockchain.info/ticker'));
	$usd = $ticker->USD->last;
	$message = json_encode(['action'=>'notification', 'message'=>number_format($usd,2), 'error'=>false]);
	foreach ($router->controller->clients as $conn){
		$conn->send($message);
	}
});
$app->run();
