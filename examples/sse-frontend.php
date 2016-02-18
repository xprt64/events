<?php

/** 
 * @copyright Constantin Galbenu <xprt64@gmail.com>
 * @link        https://github.com/xprt64/php-css
 * @license     https://opensource.org/licenses/MIT MIT License
 * 
 * Server-Sent Events
 * EventManager/app.js must be running under nodejs!
 */
use Xprt64\Events as Events;

require_once __DIR__ . '/../vendor/autoload.php';

/**
 * turn off any output buffering
 */
while(ob_end_clean());//empty op

header("Content-Type: text/event-stream");

while(true)
{
	try
	{
		$el	=	Events\EventListener::singleton();
		
		$event = $el->waitEvent();
		
		if($event)
		{
			/**
			 * Filter events based on some logic; ex: only display current user events
			 */
			if(!isset($event['userId']) || $event['userId'] == 123)
			{
				echo "event: " . ($event['type'] ?: 'ping') . "\n";
				//echo "id: " . time() . "\n";
				echo "data: " . json_encode($event) . "\n\n";

				flush();
			}
			else //@todo remove else
			{
				echo "event: not-for-this-user-" . ($event['type'] ?: 'ping') . "\n";
				//echo "id: " . time() . "\n";
				echo "data: " . json_encode($event) . "\n\n";
				
				flush();
			}
		}
		else
			throw new Exception("No event received");
	}
	catch(Exception $ex)
	{
		echo "event: server-error\n";
		echo "data: " . json_encode($ex->getMessage()) . "\n\n";
		
		flush();
		
		sleep(1);
		if($el)
			$el->reconnect();
	}
}
	