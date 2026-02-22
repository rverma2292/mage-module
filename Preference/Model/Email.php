<?php

namespace Advik\Preference\Model;

use Advik\Preference\Api\NotifyInterface;

class Email implements NotifyInterface {

	/**
	 * @param $message
	 * @return string
	 */
	public function send($message): string
	{
		return "Email sent from preference ". $message;
	}
}