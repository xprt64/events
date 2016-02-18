<?php

/** 
 * @copyright Constantin Galbenu <xprt64@gmail.com>
 * @link        https://github.com/xprt64/php-css
 * @license     https://opensource.org/licenses/MIT MIT License
 * 
 * Class for event triggering 
 * 
 * Usage: 
 * 
 * Xprt64\Events\EventTriggerer::trigger(array(
 *		'type' => 'missedCall', //any type
 *		'from' => '+40753234543,//any custom aditionala data
 *		...						//any aditiona properties
 *	));
 * 
 * @see EventListener
 */
namespace Xprt64\Events;

class	EventTriggerer
{
	/**
	 * The socket used to write/send events to EventManager
	 * @var resource
	 */
	protected	$writeSocket	=	null;
	
	const PORT	=	6969;
	const ADDR	=	'127.0.0.1';

	/**
	 * 
	 * @param array $data
	 */
	protected function __construct($data = [])
	{
		foreach($data as $k => $v)
			$this->{$k}	=	$v;
		
		if( !is_resource($this->writeSocket	= socket_create(AF_INET, SOCK_DGRAM, SOL_UDP)) )
			throw new Exception("socket_create error");
	}
	
	public function __destruct()
	{
		if($this->writeSocket)
			socket_close($this->writeSocket);
	}
	/**
	 * 
	 * @param array $data
	 * @return EventTriggerer
	 */
	public static function singleton($data = [])
	{
		return	new self($data);
	}
	
	protected function triggerEvent($eventType)
	{
		$data	=	$eventType;
		
		if( false === ($bw	=	socket_sendto($this->writeSocket, $data, strlen($data), 0, self::ADDR, self::PORT) ) )
			throw new Exception("socket_sendto error:" . socket_strerror(socket_last_error()));
		
		return	$bw;
	}
	
	/**
	 * Trigger an event
	 * 
	 * @param string|array $event
	 * @return int
	 */
	public static function trigger($event)
	{
		$instance	=	self::singleton();
		
		return	$instance->triggerEvent(json_encode($event));
	}
}