<?php

namespace realTimeComm;

use Ratchet\ConnectionInterface;

class Controller {
	
	/* @var \SplObjectStorage */
	public $clients;
	
	public function hello(ConnectionInterface $conn, $msgJson) {
		$this->send($conn, ['action'=>'notification', 'message'=>'Hi back!', 'error'=>false]);
	}
	
	/**
	 * Sends a JSON-encoded message to the client
	 *
	 * @param ConnectionInterface $conn
	 * @param array|object $msgArray
	 */
	private function send(ConnectionInterface $conn, $msgArray){
		$conn->send(json_encode($msgArray));
	}
	private function sendToAll($msgArray){
		$msgJson = json_encode($msgArray);
		foreach ($this->clients as $conn){
			$conn->send($msgJson);
		}
	}
}
