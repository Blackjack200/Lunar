<?php


namespace blackjack200\lunar\user\info;


class PlayerActionInfo {
	public float $lastStopFly = 0;
	public bool $isFlying = false;

	public function timeSinceFly() : float { return $this->isFlying ? -1 : microtime(true) - $this->lastStopFly; }
}