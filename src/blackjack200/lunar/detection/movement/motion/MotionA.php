<?php


namespace blackjack200\lunar\detection\movement\motion;


use blackjack200\lunar\detection\DetectionBase;
use pocketmine\entity\Effect;
use pocketmine\network\mcpe\protocol\DataPacket;
use pocketmine\network\mcpe\protocol\MovePlayerPacket;

class MotionA extends DetectionBase {
	public function handleClient(DataPacket $packet) : void {
		if ($packet instanceof MovePlayerPacket) {
			$user = $this->getUser();
			$info = $user->getMovementInfo();
			if (
				$info->checkFly &&
				!$info->onGround &&
				$info->timeSinceTeleport() > 0.5 &&
				$info->timeSinceMotion() > 0.25 &&
				!$user->getPlayer()->isFlying()
			) {
				$deltaY = $info->moveDelta->y;
				$lastY = $info->lastLocation->y;

				$deltaModulo = (float) $deltaY % 0.015625 === 0.0;
				$lastGround = (float) $lastY % 0.015625 === 0.0;

				$step = $deltaModulo && $lastGround;

				$modifierJump = $user->getEffectLevel(Effect::JUMP) * 0.1;
				$expected = 0.42 + $modifierJump;

				if (
					(
						$deltaY !== $expected &&
						$deltaY > 0.0 &&
						$lastGround &&
						!$step
					) ||
					($step && $deltaY > 0.6)
				) {
					if ($this->preVL++ > 2) {
						$this->addVL(1);
						if ($this->overflowVL()) {
							$this->fail("d=$deltaY diff=" . ($deltaY - $expected));
						}
					}
				}
			}
		}
	}
}