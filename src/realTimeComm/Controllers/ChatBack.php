<?php
namespace realTimeComm\Controllers;
use Ratchet\ConnectionInterface;
class ChatBack extends \realTimeComm\CoreController {
	public function hello(ConnectionInterface $conn, $msg) {
		$this->send(
			$conn,
			[
				'route'   => 'ChatBack',
				'action'  => 'notification',
				'message' => 'Hi there!',
			]
		);
	}
	public function helloName(ConnectionInterface $conn, $msg) {
		if (empty($msg->name)) throw new \Exception('What\s your name?');
		$this->send(
			$conn,
			[
				'route'   => 'ChatBack',
				'action'  => 'notification',
				'message' => $this->buildNameResponse($msg->name),
			]
		);
	}
	private function buildNameResponse($name) {
		return "Hello, {$name}";
	}
}