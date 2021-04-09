<?php


namespace blackjack200\lunar\user\info;


use pocketmine\level\Location;

final class LocationStack {
	/** @var Location[] */
	private array $data;
	private int $size;
	private int $curt;

	public function __construct(int $size) {
		$this->size = $size;
		$this->reset();
	}

	public function reset() : void {
		$this->data = [];
		$this->curt = 0;
	}

	public function pop() : ?Location {
		if ($this->curt - 1 > 0) {
			$this->curt--;
		}
		return array_pop($this->data);
	}

	public function push(Location $location) : void {
		if (count($this->data) > $this->size) {
			$this->reset();
		}
		$this->data[] = $location;
		$this->curt++;
	}
}