<?php


namespace blackjack200\lunar\detection\combat\velocity;


use blackjack200\lunar\detection\DetectionBase;
use pocketmine\network\mcpe\protocol\DataPacket;
use pocketmine\network\mcpe\protocol\MovePlayerPacket;

class VelocityB extends DetectionBase {
	public function handleClient(DataPacket $packet) : void {
		if ($packet instanceof MovePlayerPacket) {
			$user = $this->getUser();
			$info = $user->getMovementInfo();
			//1 tick = 0.05s
			if (
				$info->checkFly &&
				!$user->getActionInfo()->isFlying &&
				$info->timeSinceMotion() < 0.1 &&
				$user->getExpiredInfo()->duration('checkFly') > 0.5
			) {
				$deltaY = $info->moveDelta->y;
				$velocityY = $info->velocity->y;
				try {
					$percentage = round(($deltaY * 100.0) / $velocityY);
					$invalid = $percentage < 100 || $percentage > 100;
					if ($invalid) {
						$this->addVL(1, "percentage=$percentage");
						if ($this->overflowVL()) {
							$this->fail("percentage=$percentage");
						}
					}
				} catch (\ErrorException $ignored) {
				}
			}
		}
	}
}