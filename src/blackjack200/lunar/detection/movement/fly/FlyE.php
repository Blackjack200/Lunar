<?php


namespace blackjack200\lunar\detection\movement\fly;


use blackjack200\lunar\detection\DetectionBase;
use blackjack200\lunar\user\User;
use pocketmine\network\mcpe\protocol\DataPacket;
use pocketmine\network\mcpe\protocol\MovePlayerPacket;

class FlyE extends DetectionBase {
	private float $maxDiff;
	private float $reward;

	public function __construct(User $user, string $name, string $fmt, ?string $webhookFmt, $data) {
		parent::__construct($user, $name, $fmt, $webhookFmt, $data);
		$this->maxDiff = (float) $this->getConfiguration()->getExtraData()->MaxDiff;
		$this->reward = $this->getConfiguration()->getReward();
	}

	public function handleClient(DataPacket $packet) : void {
		if ($packet instanceof MovePlayerPacket) {
			$user = $this->getUser();
			$info = $user->getMovementInfo();
			$player = $user->getPlayer();
			$deltaY = $info->moveDelta->y;
			$lastDeltaY = $info->lastMoveDelta->y;
			if (
				!$info->onGround &&
				!$info->inVoid &&
				$info->checkFly &&
				!$user->getActionInfo()->isFlying &&
				$info->timeSinceTeleport() > 2 &&
				$info->timeSinceMotion() > 0.25 &&
				$user->timeSinceJoin() > 5 &&
				!$player->isCreative() &&
				abs($deltaY) < 3 &&
				abs($lastDeltaY) < 3 &&
				$user->getExpiredInfo()->duration('checkFly') > 0.25
			) {
				//https://github.com/Tecnio/AntiHaxerman/blob/master/src/main/java/me/tecnio/antihaxerman/check/impl/movement/flight/FlightC.java
				$difference = abs($deltaY - $lastDeltaY);

				if ($difference < $this->maxDiff && $this->preVL++ > 4) {
					$this->addVL(1);
					$this->revertMovement();
					if ($this->overflowVL()) {
						$this->fail("diff=$difference");
					}
				} else {
					$this->preVL *= $this->reward;
				}
			}
		}
	}
}