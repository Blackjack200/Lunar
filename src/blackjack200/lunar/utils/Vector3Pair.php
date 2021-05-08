<?php


namespace blackjack200\lunar\utils;


use pocketmine\math\Vector3;

class Vector3Pair {
	private Pair $pair;

	public function __construct() {
		$this->pair = new Pair();
	}

	public function last() : ?Vector3 {
		return $this->pair->x;
	}

	public function current() : ?Vector3 {
		return $this->pair->z;
	}

	public function push(Vector3 $val) : void {
		$this->pair->x = $this->pair->z;
		$this->pair->z = $val;
	}
}