<?php


namespace blackjack200\lunar\user\info;


use pocketmine\level\Location;
use pocketmine\math\Vector3;

class PlayerMovementInfo {
	public bool $lastOnGround = true;
	public bool $onGround = true;

	public bool $inVoid = false;
	public bool $onIce = false;
	//collied with transparent, liquid, climbing block and levitation immobile
	public bool $checkFly = true;

	public int $inAirTick = 0;
	public int $onGroundTick = 0;
	public int $flightTick = 0;
	public int $sprintTick = 0;

	public Vector3 $lastMoveDelta;
	public Vector3 $moveDelta;
	public Location $lastLocation;
	public Location $location;

	public float $lastTeleport = 0;
	public float $lastMotion = 0;
	public float $lastJump = 0;

	public LocationHistory $locationHistory;

	public function __construct() {
		$this->locationHistory = new LocationHistory(64);
	}

	public function timeSinceTeleport() : float { return microtime(true) - $this->lastTeleport; }

	public function timeSinceMotion() : float { return microtime(true) - $this->lastMotion; }

	public function timeSinceJump() : float { return microtime(true) - $this->lastJump; }
}