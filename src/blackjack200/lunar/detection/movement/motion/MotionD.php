<?php


namespace blackjack200\lunar\detection\movement\motion;


use blackjack200\lunar\detection\DetectionBase;
use pocketmine\entity\Effect;
use pocketmine\network\mcpe\protocol\DataPacket;
use pocketmine\network\mcpe\protocol\MovePlayerPacket;

class MotionD extends DetectionBase {
	public function handleClient(DataPacket $packet) : void {
		if ($packet instanceof MovePlayerPacket) {
			$user = $this->getUser();
			$info = $user->getMovementInfo();
			if (
				$info->checkFly &&
				$info->timeSinceTeleport() > 0.5 &&
				$info->timeSinceMotion() > 0.25 &&
				!$user->getPlayer()->isFlying()
			) {
				$deltaY = (float) $info->moveDelta->y;
				$modifierJump = $user->getEffectLevel(Effect::JUMP) * 0.1;
				$modifierVelocity = $user->getPlayer()->getMotion()->y + 0.5;
				$max = 0.6 + $modifierJump + $modifierVelocity;
				if ($deltaY > $max && $this->preVL++ > 2) {
					$this->addVL(1);
					$this->preVL = 0;
					if ($this->overflowVL()) {
						$this->fail('diff' . ($max - $deltaY));
					}
				}
			}
		}
	}
}