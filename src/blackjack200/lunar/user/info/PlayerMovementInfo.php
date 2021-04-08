<?php


namespace blackjack200\lunar\user\info;


use pocketmine\level\Location;
use pocketmine\math\Vector3;

class PlayerMovementInfo {
	public bool $lastOnGround = true;
	public bool $onGround = true;
	public bool $inVoid = false;
	public bool $checkFly = true;
	public int $inAirTick = 0;
	public bool $onIce = false;
	public int $onGroundTick = 0;
	public Vector3 $lastMoveDelta;
	public Vector3 $moveDelta;
	public Location $lastLocation;
	public Location $location;
	public float $lastTeleport = 0;
	public float $lastMotion = 0;
	public LocationStack $stack;

	public function __construct() {
		$this->stack = new LocationStack(4);
	}

	public function timeSinceTeleport() : float {
		return microtime(true) - $this->lastTeleport;
	}

	public function timeSinceMotion() : float {
		return microtime(true) - $this->lastMotion;
	}
}