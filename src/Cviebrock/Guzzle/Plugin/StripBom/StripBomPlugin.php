<?php namespace Cviebrock\Guzzle\Plugin\StripBom;


use GuzzleHttp\Event\CompleteEvent;
use GuzzleHttp\Event\SubscriberInterface;


/**
 * Strips BOM from request body if it exists.  Helps with JSON/XML decoding of .NET service responses.
 */
class StripBomPlugin implements SubscriberInterface
{

	public function getEvents()
	{
		return array(
			'complete' => ['onComplete'],
		);
	}

	/**
	 * When the request is complete, check the message body and strip any BOMs, if they exist.
	 *
	 * @param Event $event
	 */
	public function onComplete(CompleteEvent $event)
	{
		if ($body = $event->getResponse()->getBody()) {
			if (substr($body, 0, 3) === "\xef\xbb\xbf") {
				// UTF-8
				$event->getResponse()->setBody(substr($body, 3));
			} else if (substr($body, 0, 4) === "\xff\xfe\x00\x00" ||
					   substr($body, 0, 4) === "\x00\x00\xfe\xff"
			) {
				// UTF-32
				$event->getResponse()->setBody(substr($body, 4));
			} else if (substr($body, 0, 2) === "\xff\xfe" ||
					   substr($body, 0, 2) === "\xfe\xff"
			) {
				// UTF-16
				$event->getResponse()->setBody(substr($body, 2));
			}
		}
	}

}
