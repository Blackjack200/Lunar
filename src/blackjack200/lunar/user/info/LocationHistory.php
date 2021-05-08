<?php


namespace blackjack200\lunar\user\info;


use Ds\Vector;
use pocketmine\level\Location;
use UnderflowException;

final class LocationHistory {
	/** @var Vector<Location> */
	private Vector $vector;
	private int $size;
	private int $curt;

	public function __construct(int $size) {
		$this->size = $size;
		$this->vector = new Vector();
		$this->vector->allocate($size);
		$this->rewind();
	}

	public function rewind() : void {
		$this->vector->clear();
		$this->curt = 0;
	}

	public function pop() : ?Location {
		if ($this->curt - 1 > 0) {
			$this->curt--;
		}

		$ret = null;
		try {
			$ret = $this->vector->pop();
		} catch (UnderflowException $ignored) {
		}
		return $ret;
	}

	public function push(Location $location) : void {
		if (count($this->vector) > $this->size) {
			$this->rewind();
		}
		$this->vector->push($location);
		$this->curt++;
	}
}