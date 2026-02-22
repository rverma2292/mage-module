<?php
namespace Advik\Preference\Api;

interface NotifyInterface {

	/**
	 * @param string $message
	 * @return string
	 */
	public function send($message);

}