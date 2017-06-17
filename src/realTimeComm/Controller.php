<?php

namespace realTimeComm;

use Ratchet\ConnectionInterface;

class Controller {
	
	public function hello(ConnectionInterface $conn, $msgJson) {
		$this->send($conn, ['action'=>'notification', 'message'=>'Hi back!', 'error'=>false]);
	}
	
	/**
	 * Sends a JSON-encoded message to the client
	 *
	 * @param ConnectionInterface $conn
	 * @param array|object $msgJson
	 */
	private function send(ConnectionInterface $conn, $msgJson){
		$conn->send(json_encode($msgJson));
	}
}
