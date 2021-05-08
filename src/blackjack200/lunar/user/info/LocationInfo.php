<?php


namespace blackjack200\lunar\user\info;


use blackjack200\lunar\utils\BooleanPair;
use blackjack200\lunar\utils\Duration;
use blackjack200\lunar\utils\LocationPair;
use blackjack200\lunar\utils\Vector3Pair;
use pocketmine\math\Vector3;

final class LocationInfo {
	public LocationHistory $history;
	public LocationPair $location;
	public Vector3Pair $delta;
	public Vector3 $velocity;

	public BooleanPair $onGround;
	public BooleanPair $onIce;
	public BooleanPair $inLiquid;
	public BooleanPair $inVoid;
	public BooleanPair $onClimbable;
	public BooleanPair $inCobweb;

	public Duration $air;
	public Duration $liquid;
	public Duration $climbable;
	public Duration $cobweb;
	public Duration $ice;

	public function __construct() {
		$this->history = new LocationHistory(16);
		$this->location = new LocationPair();
		$this->delta = new Vector3Pair();
		$this->onGround = new BooleanPair();
		$this->onIce = new BooleanPair();
		$this->inLiquid = new BooleanPair();
		$this->inVoid = new BooleanPair();
		$this->air = new Duration();
		$this->liquid = new Duration();
		$this->climbable = new Duration();
		$this->cobweb = new Duration();
		$this->ice = new Duration();
	}
}