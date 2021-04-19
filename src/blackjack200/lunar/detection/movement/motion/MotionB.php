<?php


namespace blackjack200\lunar\detection\movement\motion;


use blackjack200\lunar\detection\DetectionBase;
use pocketmine\entity\Effect;
use pocketmine\network\mcpe\protocol\DataPacket;
use pocketmine\network\mcpe\protocol\MovePlayerPacket;

class MotionB extends DetectionBase {
	public function handleClient(DataPacket $packet) : void {
		if ($packet instanceof MovePlayerPacket) {
			$user = $this->getUser();
			$info = $user->getMovementInfo();
			$player = $user->getPlayer();
			if (
				!$info->inVoid &&
				$info->checkFly &&
				$info->timeSinceTeleport() > 2 &&
				$info->timeSinceMotion() > 0.0055 &&
				$user->timeSinceJoin() > 5 &&
				!$player->isFlying()
			) {
				$deltaY = $info->moveDelta->y;

				$modifierJump = $user->getEffectLevel(Effect::JUMP) * 0.1;
				$modifierVelocity = $info->timeSinceMotion() < 0.25 ? $player->getMotion()->y + 0.5 : 0.0;

				$maximum = 0.6 + $modifierJump + $modifierVelocity;

				if ($deltaY > $maximum) {
					$this->addVL(1);
					if ($this->overflowVL()) {
						$this->fail("dy=$deltaY pred_max=$maximum");
					}
				}
			}
		}
	}
}