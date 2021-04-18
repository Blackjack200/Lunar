<?php


namespace blackjack200\lunar\utils;


use blackjack200\lunar\Lunar;
use blackjack200\lunar\webhook\discord\DiscordMessage;
use blackjack200\lunar\webhook\discord\DiscordRequest;
use Exception;

class Discord {
	public static function submit(string $message) : void {
		$URL = Lunar::getInstance()->getURL();
		if ($URL !== '_') {
			try {
				$msg = new DiscordMessage();
				$msg->content($message);
				$req = new DiscordRequest($URL, $msg);
				Lunar::getInstance()->getClient()->submit($req);
			} catch (Exception $e) {
			}
		}
	}
}