<?php


namespace blackjack200\lunar\user\info;


use pocketmine\level\Location;
use pocketmine\math\Vector3;

class MovementInfo {
	public bool $onGround = true;
	public int $offGroundTick = 0;
	public Vector3 $lastMoveDelta;
	public Vector3 $moveDelta;
	public Location $lastLocation;
	public Location $location;
	public float $lastTeleport = 0;
	public float $lastMotion = 0;

	public function timeSinceTeleport() : float {
		return microtime(true) - $this->lastTeleport;
	}

	public function timeSinceMotion() : float {
		return microtime(true) - $this->lastMotion;
	}
}