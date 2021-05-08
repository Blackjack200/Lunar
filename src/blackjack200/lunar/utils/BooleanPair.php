<?php


namespace blackjack200\lunar\utils;


class BooleanPair {
	private Pair $pair;

	public function __construct() {
		$this->pair = new Pair();
	}

	public function last() : ?bool {
		return $this->pair->x;
	}

	public function current() : ?bool {
		return $this->pair->z;
	}

	public function push(bool $val) : void {
		$this->pair->x = $this->pair->z;
		$this->pair->z = $val;
	}
}