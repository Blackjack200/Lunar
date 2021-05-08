<?php


namespace blackjack200\lunar\user\info;


use blackjack200\lunar\utils\Timestamp;

class ActionInfo {
	public bool $isFlying = false;
	public bool $isSprinting = false;
	public bool $isGliding = false;
	public bool $isSneaking = false;
	public bool $isSwimming = false;
	public Timestamp $swing;

	public function __construct() {
		$this->swing = new Timestamp();
	}
}