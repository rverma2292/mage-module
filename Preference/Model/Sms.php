<?php

namespace Advik\Preference\Model;

use Advik\Preference\Api\NotifyInterface;

class Sms implements NotifyInterface {

	/**
	 * @param $message
	 * @return string
	 */
	public function send($message): string
	{
		return "SMS message sent from preference ". $message;
	}
}