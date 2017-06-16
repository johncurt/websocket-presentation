<?php
use Ratchet\Server\IoServer;
use realTimeComm\Router;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;

//load composer
require 'vendor/autoload.php';

//setup server
$server = IoServer::factory(
	new HttpServer(
		new WsServer(
			new Router(true)
		)
	),
	8080
);

//run server
$server->run();
