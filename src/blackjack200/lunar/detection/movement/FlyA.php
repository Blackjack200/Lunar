<?php


namespace blackjack200\lunar\detection\movement;


use blackjack200\lunar\detection\DetectionBase;
use blackjack200\lunar\user\User;
use pocketmine\network\mcpe\protocol\DataPacket;
use pocketmine\network\mcpe\protocol\MovePlayerPacket;

class FlyA extends DetectionBase {
	private float $maxDiff;
	private float $reward;

	public function __construct(User $user, string $name, $data) {
		parent::__construct($user, $name, $data);
		$this->maxDiff = (float) $this->getConfiguration()->getExtraData()->Diff;
		$this->reward = $this->getConfiguration()->getReward();
	}

	public function handleReceive(DataPacket $packet) : void {
		if ($packet instanceof MovePlayerPacket) {
			$user = $this->getUser();
			$movementInfo = $user->getMovementInfo();
			if (
				$movementInfo->offGroundTick > 5 &&
				!$movementInfo->inVoid &&
				$movementInfo->checkFly &&
				$movementInfo->timeSinceTeleport() > 2 &&
				$user->timeSinceJoin() > 5 &&
				!$user->getPlayer()->isFlying()
			) {
				//https://github.com/Tecnio/AntiHaxerman/blob/master/src/main/java/me/tecnio/antihaxerman/check/impl/movement/flight/FlightA.java
				$deltaY = $movementInfo->moveDelta->y;
				$lastDeltaY = $movementInfo->lastMoveDelta->y;
				$prediction = ($lastDeltaY - 0.08) * 0.9800000190734863;
				$fixed = abs($prediction) < 0.005 ? 0 : $prediction;
				$difference = abs($deltaY - $fixed);
				$limit = $movementInfo->timeSinceMotion() > 0.25 ? 0.001 : $user->getPlayer()->getMotion()->y + 0.451;
				if ($difference > $limit) {
					if ($this->preVL++ > 5) {
						$this->addVL(1);
						if ($this->overflowVL()) {
							$this->fail("diff=$difference limit=$limit");
						}
						$this->preVL = 0;
					}
				} else {
					$this->rewardPreVL($this->reward);
				}
			}
		}
	}
}