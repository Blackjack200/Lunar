<?php


namespace blackjack200\lunar\user\info;


use Ds\Stack;
use pocketmine\level\Location;

final class LocationStack {
	/** @var Stack<Location> */
	private Stack $stack;
	private int $size;
	private int $curt;

	public function __construct(int $size) {
		$this->size = $size;
		$this->stack = new Stack();
		$this->stack->allocate($size);
		$this->reset();
	}

	public function reset() : void {
		$this->stack->clear();
		$this->curt = 0;
	}

	public function pop() : ?Location {
		if ($this->curt - 1 > 0) {
			$this->curt--;
		}
		return $this->stack->pop();
	}

	public function push(Location $location) : void {
		if (count($this->stack) > $this->size) {
			$this->reset();
		}
		$this->stack->push($location);
		$this->curt++;
	}
}