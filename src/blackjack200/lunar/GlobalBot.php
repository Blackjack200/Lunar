<?php


namespace blackjack200\lunar;


use Exception;
use libbot\Bot;
use libbot\discord\DiscordMessage;

class GlobalBot {
	private static ?Bot $bot = null;

	public static function set(?Bot $bot) : void {
		if (self::$bot !== null) {
			self::$bot->shutdown();
		}
		self::$bot = $bot;
	}

	public static function get() : ?Bot {
		return self::$bot;
	}

	public static function send(string $message) : void {
		if (self::$bot !== null) {
			try {
				$msg = self::$bot->newMessage();
				$msg->content($message);
				self::$bot->send($msg);
			} catch (Exception $e) {
			}
		}
	}

	public static function start() : void {
		if (self::$bot !== null) {
			self::$bot->start();
		}
	}
}