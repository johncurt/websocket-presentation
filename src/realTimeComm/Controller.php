<?php

namespace realTimeComm;

use Ratchet\ConnectionInterface;

class Controller {
	
	public function hello(ConnectionInterface $conn, $msgJson) {
		$this->send($conn, ['action'=>'hello', 'message'=>'Hi back!']);
	}
	
	private function send(ConnectionInterface $conn, $msgJson){
		$conn->send(json_encode($msgJson));
	}
}
