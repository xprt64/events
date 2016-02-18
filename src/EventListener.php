<?php

/** 
 * @copyright Constantin Galbenu <xprt64@gmail.com>
 * @link        https://github.com/xprt64/php-css
 * @license     https://opensource.org/licenses/MIT MIT License
 * 
 * Library for event listeners. 
 * 
 * Every script that needs to listen to global inter-process events, should call
 * EventListener::singleton()->waitEvent()  which connect to EventManager and waits (is blocking) 
 * until a event arrives from EventManager;
 * EventManager is a running nodejs application server located at 
 * EventManager/app.js
 * The EventManager acts as a multiplexer, forwarning events triggered by 
 * EventTriggerer::trigger() to all connected event listeners
 * 
 * @see Xprt64\Events\EventTriggerer
 * @see EventManager/app.js
 * @see examples/sse.php
 */
namespace Xprt64\Events;

class	EventListener
{
	/**
	 * The socket used to connect to EventManager on ADDR:PORT
	 * @var resource
	 */
	protected	$socket	=	null;
	
	const PORT	=	6970;
	const ADDR	=	'127.0.0.1';

	/**
	 *
	 * @var EventListener 
	 */
	protected static	$instance;
	
	/**
	 * Constructor
	 * 
	 * @param array $data
	 */
	protected function __construct($data = [])
	{
		foreach($data as $k => $v)
			$this->{$k}	=	$v;
		
		$this->init();
	}
	
	/**
	 * Connects to EventManager
	 * @throws Exception
	 */
	protected function init()
	{
		if( !is_resource($this->socket	= socket_create(AF_INET, SOCK_STREAM, SOL_TCP)) )
			throw new Exception("socket_create error:" . socket_strerror(socket_last_error()));
	
		if(!socket_connect($this->socket, self::ADDR, self::PORT))
			throw new Exception("Socket connect error(" .  self::ADDR . "," . self::PORT . "):" . socket_strerror(socket_last_error()));
	}
	
	/**
	 * Close the connection to EventManager
	 */
	protected function close()
	{
		if($this->socket)
		{
			socket_close($this->socket);
			unset($this->socket);
		}
	}
	
	public function __destruct()
	{
		$this->close();
	}
	
	/**
	 * Returns the single instance
	 * @param array $data
	 * @return EventListener
	 */
	public static function singleton($data = [])
	{
		if(!self::$instance)
			self::$instance = new EventListener($data);
		
		return	self::$instance;
	}
	
	/**
	 * Reconnects to EventManager
	 */
	public function reconnect()
	{
		$this->close();
		$this->init();
	}
	
	/**
	 * Blocks until an event arrives and returns it as an array
	 * 
	 * @return array
	 * @throws Exception
	 */
	public function waitEvent()
	{
		$br	= socket_read($this->socket, 999999, PHP_BINARY_READ);
		
		if( false === $br || '' === $br )
			throw new Exception("socket_read error:" . socket_strerror(socket_last_error()));
		
		$obj	=	json_decode($br, true);
		
		return	$obj;
	}
}