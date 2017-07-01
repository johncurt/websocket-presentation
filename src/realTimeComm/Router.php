<?php

namespace realTimeComm;
use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;

class Router implements MessageComponentInterface {
	
	public $controller;
	
	public $coins;
	
	public function __construct(&$coins){
		$this->controller = new \realTimeComm\Controller();
		$this->controller->clients = new \SplObjectStorage();
		$this->coins = &$coins;
	}
	
	/**
	 * When a new connection is opened it will be passed to this method
	 * @param  ConnectionInterface $conn The socket/connection that just connected to your application
	 * @throws \Exception
	 */
	function onOpen(ConnectionInterface $conn)
	{
		$this->controller->clients->attach($conn);
		$message = json_encode(['action'=>'bitcoin', 'price'=>number_format($this->coins['bitcoin'],2), 'error'=>false]);
		$conn->send($message);
		$message = json_encode(['action'=>'ether', 'price'=>number_format($this->coins['ether'],2), 'error'=>false]);
		$conn->send($message);
	}
	
	/**
	 * This is called before or after a socket is closed (depends on how it's closed).  SendMessage to $conn will not result in an error if it has already been closed.
	 * @param  ConnectionInterface $conn The socket/connection that is closing/closed
	 * @throws \Exception
	 */
	function onClose(ConnectionInterface $conn)
	{
		$this->controller->clients->detach($conn);
	}
	
	/**
	 * If there is an error with one of the sockets, or somewhere in the application where an Exception is thrown,
	 * the Exception is sent back down the stack, handled by the Server and bubbled back up the application through this method
	 * @param  ConnectionInterface $conn
	 * @param  \Exception $e
	 * @throws \Exception
	 */
	function onError(ConnectionInterface $conn, \Exception $e)
	{
		try {
			$conn->send(json_encode(['action' => 'notification', 'message' => $e->getMessage(), 'error' => true]));
		} catch (\Exception $e){
			//should probably log this...
		}
	}
	
	/**
	 * Triggered when a client sends data through the socket
	 * @param  \Ratchet\ConnectionInterface $from The socket/connection that sent the message to your application
	 * @param  string $msg The message received
	 * @throws \Exception
	 */
	function onMessage(ConnectionInterface $from, $msg)
	{
		try {
			$msgJSON = json_decode($msg);
			if (
				!empty($msgJSON->action)
				&& is_string($msgJSON->action)
				&& $msgJSON->action !== '__construct'
				&& method_exists($this->controller, $msgJSON->action)
			){
				$reflection = new \ReflectionMethod($this->controller, $msgJSON->action);
				if ($reflection->isPublic()) {
					$this->controller->{$msgJSON->action}($from, $msgJSON);
				} else {
					throw new \Exception('You can\'t do that!');
				}
			} else {
				throw new \Exception('Invalid Operation');
			}
		} catch (\Exception $e){
			//there was an error
			$from->send(json_encode(['action'=>'notification', 'message'=>$e->getMessage(), 'error'=>true]));
		}
	}
	
	
}