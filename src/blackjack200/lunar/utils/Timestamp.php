<?php


namespace blackjack200\lunar\utils;


use pocketmine\Server;

final class Timestamp {
	private int $tick;

	public function __construct() {
		$this->reset();
	}

	public function reset() : void {
		$this->tick = self::tick();
	}

	private static function tick() : int {
		return Server::getInstance()->getTick();
	}

	public function duration() : int {
		return self::tick() - $this->tick;
	}
}