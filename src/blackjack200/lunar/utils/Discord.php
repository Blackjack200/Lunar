<?php


namespace blackjack200\lunar\utils;


use blackjack200\lunar\Lunar;
use Exception;
use libbot\discord\DiscordMessage;

class Discord {
	public static function submit(string $message) : void {
		$URL = Lunar::getInstance()->getURL();
		if ($URL !== '_') {
			try {
				$msg = new DiscordMessage();
				$msg->content($message);
				Lunar::getInstance()->getBot()->send($msg);
			} catch (Exception $e) {
			}
		}
	}
}