<?php


namespace blackjack200\lunar\utils;


use Ds\Map;
use pocketmine\Server;

final class ExpiredCollection {
	private Map $data;

	public function __construct(int $size) {
		$this->data = new Map();
		$this->data->allocate($size);
	}

	public function set($k) : void {
		$this->lazy($k);
		$this->data->put($k, Server::getInstance()->getTick());
	}

	private function lazy($k) : void {
		if (!$this->data->hasKey($k)) {
			$this->data->put($k, Server::getInstance()->getTick());
		}
	}

	public function duration($k) : int {
		$this->lazy($k);
		return microtime(true) - $this->data->get($k);
	}
}