<?php


namespace blackjack200\lunar\detection\movement\motion;


use blackjack200\lunar\detection\DetectionBase;
use pocketmine\entity\Attribute;
use pocketmine\network\mcpe\protocol\DataPacket;
use pocketmine\network\mcpe\protocol\MovePlayerPacket;

class MotionA extends DetectionBase {
	public function handleClient(DataPacket $packet) : void {
		//reference: https://github.com/routerabfrage/badlion-src/blob/93a099e711f1e91f432fcf2aff084cf73d6b2c82/net/minecraft/entity/EntityLivingBase.java#L1060-L1073
		//   |\
		//   | \
		// a |  \ c
		//   |___\
		//     b
		// a**2 + b**2 = c**2
		$info = $this->getUser()->getMovementInfo();
		if (
			$packet instanceof MovePlayerPacket &&
			$info->timeSinceJump() < 0.052 &&
			$info->timeSinceTeleport() > 1
		) {
			$player = $this->getUser()->getPlayer();
			$lastDelta = clone $info->lastMoveDelta;
			$motion = $player->getMotion();
			$lastDelta->x -= $motion->x;
			$lastDelta->z -= $motion->z;
			$lastXZ = hypot($lastDelta->x, $lastDelta->z);
			if ($player->isSprinting()) {
				$f = $player->getYaw() * 0.017453292;
				$lastXZ = hypot($lastDelta->x - (sin($f) * 0.2), $lastDelta->z + (cos($f) * 0.2));
			}
			$curt = hypot($info->moveDelta->x, $info->moveDelta->z);
			$prediction = $lastXZ * 0.98 + $player->getAttributeMap()->getAttribute(Attribute::MOVEMENT_SPEED)->getValue();
			$diff = $curt - $prediction;
			if ($diff > 0.01) {
				$this->addVL(1);
				if ($this->overflowVL()) {
					$this->fail("diff=$diff");
				}
			}
		}
	}
}