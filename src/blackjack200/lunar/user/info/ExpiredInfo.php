<?php


namespace blackjack200\lunar\user\info;


use Ds\Map;

final class ExpiredInfo {
	private Map $data;

	public function __construct(int $size) {
		$this->data = new Map();
		$this->data->allocate($size);
	}

	public function set($k) : void {
		$this->lazy($k);
		$this->data->put($k, microtime(true));
	}

	private function lazy($k) : void {
		if (!$this->data->hasKey($k)) {
			$this->data->put($k, microtime(true));
		}
	}

	public function duration($k) : float {
		$this->lazy($k);
		return microtime(true) - $this->data->get($k);
	}
}