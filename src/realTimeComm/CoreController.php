<?php
namespace realTimeComm;
use Ratchet\ConnectionInterface;
abstract class CoreController {
	
	/* @var \SplObjectStorage */
	public $clients;

	/**
	 * Sends a JSON-encoded message to the client
	 *
	 * @param ConnectionInterface $conn
	 * @param array|object $msgArray
	 */
	protected function send(ConnectionInterface $conn, $msgArray){
		$conn->send(json_encode($msgArray));
	}
	protected function sendToAll($msgArray){
		$msgJson = json_encode($msgArray);
		foreach ($this->clients as $conn){
			$conn->send($msgJson);
		}
	}
}
