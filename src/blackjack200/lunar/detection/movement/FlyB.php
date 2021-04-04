<?php


namespace blackjack200\lunar\detection\movement;


use blackjack200\lunar\detection\DetectionBase;
use blackjack200\lunar\user\User;

class FlyB extends DetectionBase {
	private float $reward;

	public function __construct(User $user, string $name, $data) {
		parent::__construct($user, $name, $data);
		$this->reward = $this->getConfiguration()->getReward();
	}

	public function check(...$data) : void {
		$info = $this->getUser()->getMovementInfo();
		if ($info->offGroundTick > 5 &&
			!$info->onGround &&
			!$info->lastOnGround &&
			$this->preVL++ > 1 &&
			$info->checkFly &&
			$info->timeSinceTeleport() > 0.5
		) {
			$this->preVL = 0;
			$this->addVL(1);
			if ($this->overflowVL()) {
				$this->fail("off={$info->offGroundTick}");
			}
		} else {
			$this->rewardPreVL($this->reward);
		}
	}
}