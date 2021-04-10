<?php


namespace blackjack200\lunar\detection\movement\motion;


use blackjack200\lunar\detection\DetectionBase;
use blackjack200\lunar\user\User;
use pocketmine\network\mcpe\protocol\DataPacket;
use pocketmine\network\mcpe\protocol\MovePlayerPacket;

class MotionC extends DetectionBase {
	private float $reward;

	public function __construct(User $user, string $name, $data) {
		parent::__construct($user, $name, $data);
		$this->reward = $this->getConfiguration()->getReward();
	}

	public function handleClient(DataPacket $packet) : void {
		if ($packet instanceof MovePlayerPacket) {
			$info = $this->getUser()->getMovementInfo();
			if (
				$info->checkFly &&
				$info->timeSinceTeleport() > 0.5
			) {
				$deltaY = (float) $info->moveDelta->y;
				$lastDeltaY = (float) $info->lastMoveDelta->y;
				if ($deltaY === -$lastDeltaY && $deltaY !== 0.0) {
					if ($this->preVL++ > 4) {
						$this->addVL(1);
						if ($this->overflowVL()) {
							$this->fail('equals');
						}
					} else {
						$this->rewardPreVL($this->reward);
					}
				}
			}
		}
	}
}