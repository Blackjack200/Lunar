<?php


namespace blackjack200\lunar\utils;


class Duration {
	private int $dur;

	public function __construct() {
		$this->dur = 0;
	}

	public function add() : void {
		$this->dur++;
	}

	public function reset() : void {
		$this->dur = 0;
	}

	public function duration() : int {
		return $this->dur;
	}
}