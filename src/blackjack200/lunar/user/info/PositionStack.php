<?php


namespace blackjack200\lunar\user\info;


use pocketmine\level\Location;
use SplFixedArray;

final class PositionStack {
	/** @var SplFixedArray<Location> */
	private SplFixedArray $data;
	private int $size;
	private int $curt = 0;

	public function __construct(int $size) {
		$this->size = $size;
		$this->reset();
	}

	public function reset() : void {
		$this->data = new SplFixedArray($this->size);
		$this->curt = 0;
	}

	public function pop() : ?Location {
		$curt = &$this->curt;
		$ret = $this->data[$curt];
		$this->data[$curt] = null;
		if ($this->curt > 0) {
			$curt--;
		}
		return $ret;
	}

	public function push(Location $location) : void {
		if ($this->curt + 1 > $this->size) {
			$this->reset();
		}
		$this->data[$this->curt++] = $location;
	}
}