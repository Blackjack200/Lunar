<?php


namespace blackjack200\lunar\detection\movement;


use blackjack200\lunar\detection\DetectionBase;
use blackjack200\lunar\user\User;
use pocketmine\entity\Effect;
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
			$info = $user->getMovementInfo();
			$player = $user->getPlayer();
			if (
				$info->inAirTick > 5 &&
				!$info->inVoid &&
				$info->checkFly &&
				$info->timeSinceTeleport() > 2 &&
				$info->timeSinceMotion() > 0.25 &&
				$user->timeSinceJoin() > 5 &&
				!$player->isFlying()
			) {
				//https://github.com/Tecnio/AntiHaxerman/blob/master/src/main/java/me/tecnio/antihaxerman/check/impl/movement/flight/FlightA.java
				$deltaY = $info->moveDelta->y;
				$lastDeltaY = $info->lastMoveDelta->y;
				$prediction = ($lastDeltaY - 0.08) * 0.9800000190734863;
				$fixed = abs($prediction) < 0.005 ? 0 : $prediction;
				$difference = abs($deltaY - $fixed);
				$limit = $info->timeSinceMotion() > 0.25 ? 0.001 : $player->getMotion()->y + 0.451;
				$airTicksLimit = $this->maxDiff + 8 + $user->getEffectLevel(Effect::JUMP);
				if ($difference > $limit && $info->inAirTick > $airTicksLimit) {
					if ($this->preVL++ > 5) {
						$this->addVL(1);
						if ($this->overflowVL()) {
							$this->fail("diff=$difference limit=$limit");
						}
					}
				} else {
					$this->rewardPreVL($this->reward);
				}
			}
		}
	}
}