<?php


namespace blackjack200\lunar\utils;


use pocketmine\level\Location;

final class LocationPair {
	private Pair $pair;

	public function __construct() {
		$this->pair = new Pair();
	}

	public function last() : ?Location {
		return $this->pair->x;
	}

	public function current() : ?Location {
		return $this->pair->z;
	}

	public function push(Location $val) : void {
		$this->pair->x = $this->pair->z;
		$this->pair->z = $val;
	}
}