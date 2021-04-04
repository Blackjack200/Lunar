<?php


namespace blackjack200\lunar\detection\movement;


use blackjack200\lunar\detection\DetectionBase;
use blackjack200\lunar\user\User;
use pocketmine\network\mcpe\protocol\DataPacket;
use pocketmine\network\mcpe\protocol\MovePlayerPacket;

class FlyE extends DetectionBase {
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
			$info = $user->getMovementInfo();
			$player = $user->getPlayer();
			if (
				!$info->onGround &&
				!$info->inVoid &&
				$info->checkFly &&
				$info->timeSinceTeleport() > 2 &&
				$info->timeSinceMotion() > 0.25 &&
				$user->timeSinceJoin() > 5 &&
				!$player->isFlying()
			) {
				//https://github.com/Tecnio/AntiHaxerman/blob/master/src/main/java/me/tecnio/antihaxerman/check/impl/movement/flight/FlightC.java
				$deltaY = $info->moveDelta->y;
				$lastDeltaY = $info->lastMoveDelta->y;

				$difference = abs($deltaY - $lastDeltaY);

				if ($difference < $this->maxDiff && $this->preVL++ > 4) {
					$this->addVL(1);
					if ($this->overflowVL()) {
						$this->fail("diff=$difference");
					}
				} else {
					$this->rewardPreVL($this->reward);
				}
			}
		}
	}
}