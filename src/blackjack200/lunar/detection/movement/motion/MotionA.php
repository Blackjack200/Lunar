<?php


namespace blackjack200\lunar\detection\movement\motion;


use blackjack200\lunar\detection\DetectionBase;
use pocketmine\entity\Attribute;
use pocketmine\network\mcpe\protocol\DataPacket;
use pocketmine\network\mcpe\protocol\MovePlayerPacket;

class MotionA extends DetectionBase {
	public function handleClient(DataPacket $packet) : void {
		//reference: https://github.com/routerabfrage/badlion-src/blob/93a099e711f1e91f432fcf2aff084cf73d6b2c82/net/minecraft/entity/EntityLivingBase.java#L1060-L1073
		$user = $this->getUser();
		$info = $user->getInfo();
		if (
			$packet instanceof MovePlayerPacket &&
			$info->jump < 2 &&
			$info->timeSinceTeleport() > 1 &&
			$user->getExpiredInfo()->duration('ice') > 1
		) {
			$lastDelta = clone $info->lastMoveDelta;
			$motion = $info->velocity;
			$lastDelta->x -= $motion->x;
			$lastDelta->z -= $motion->z;
			$lastXZ = hypot($lastDelta->x, $lastDelta->z);
			if ($user->getActionInfo()->isSprinting) {
				$f = $info->location->yaw * 0.017453292;
				$lastXZ = hypot($lastDelta->x - (sin($f) * 0.2), $lastDelta->z + (cos($f) * 0.2));
			}
			$curt = hypot($info->moveDelta->x, $info->moveDelta->z);
			$prediction = $lastXZ * 0.98 + $user->getPlayer()->getAttributeMap()->getAttribute(Attribute::MOVEMENT_SPEED)->getValue();
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