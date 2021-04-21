<?php


namespace blackjack200\lunar\detection\movement\fly;


use blackjack200\lunar\detection\DetectionBase;
use pocketmine\entity\Effect;
use pocketmine\network\mcpe\protocol\DataPacket;
use pocketmine\network\mcpe\protocol\MovePlayerPacket;

class FlyA extends DetectionBase {
	public function handleClient(DataPacket $packet) : void {
		if ($packet instanceof MovePlayerPacket) {
			$user = $this->getUser();
			$info = $user->getMovementInfo();
			$player = $user->getPlayer();
			$airTicksLimit = 10 + ($user->getEffectLevel(Effect::JUMP) * 2);
			if (
				!$info->inVoid &&
				$info->checkFly &&
				$info->inAirTick > $airTicksLimit &&
				$info->timeSinceTeleport() > 2 &&
				$info->timeSinceMotion() > 1 &&
				$user->timeSinceJoin() > 5 &&
				!$player->isFlying() &&
				$user->getExpiredInfo()->duration('flight') > 1 &&
				$user->getExpiredInfo()->duration('checkFly') > 1
			) {
				//https://github.com/Tecnio/AntiHaxerman/blob/master/src/main/java/me/tecnio/antihaxerman/check/impl/movement/flight/FlightA.java
				$deltaY = $info->moveDelta->y;
				$lastDeltaY = $info->lastMoveDelta->y;

				$predicted = ($lastDeltaY - 0.08) * 0.9800000190734863;

				$difference = abs($deltaY - $predicted);

				if (abs($predicted) > 0.005 && $difference > 1.0E-4 && $this->preVL++ > 2) {
					$this->addVL(1, "diff=$difference pred=$predicted");
					$this->revertMovement();
					if ($this->overflowVL()) {
						$this->fail("diff=$difference pred=$predicted");
					}
				}
			}
		}
	}
}