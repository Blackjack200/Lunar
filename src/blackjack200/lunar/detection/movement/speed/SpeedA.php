<?php


namespace blackjack200\lunar\detection\movement\speed;


use blackjack200\lunar\detection\DetectionBase;
use blackjack200\lunar\user\User;
use pocketmine\network\mcpe\protocol\DataPacket;
use pocketmine\network\mcpe\protocol\MovePlayerPacket;

//Flight Speed
class SpeedA extends DetectionBase {
	protected float $maxDiff;
	protected float $reward;

	public function __construct(User $user, string $name, $data) {
		parent::__construct($user, $name, $data);
		$this->maxDiff = (float) $this->getConfiguration()->getExtraData()->Diff;
		$this->reward = $this->getConfiguration()->getReward();
	}

	public function handleReceive(DataPacket $packet) : void {
		$user = $this->getUser();
		if ($packet instanceof MovePlayerPacket && $user->getMovementInfo()->inAirTick > 2) {
			$lastMD = $user->getMovementInfo()->lastMoveDelta;
			$curtMD = $user->getMovementInfo()->moveDelta;
			$last = hypot($lastMD->x, $lastMD->z);
			$curt = hypot($curtMD->x, $curtMD->z);

			//reference: https://github.com/GladUrBad/Medusa/blob/7be8d34ae0470f0655b59e213d7619b98a3f43ff/Impl/src/main/java/com/gladurbad/medusa/check/impl/movement/speed/SpeedA.java#L25
			$predicted = $last * 0.91 + ($user->getPlayer()->isSprinting() ? 0.026 : 0.02);
			$diff = $curt - $predicted;
			if ($predicted > 0.075 &&
				$diff > $this->maxDiff &&
				$user->getMovementInfo()->timeSinceTeleport() >= 0.25 &&
				$user->getMovementInfo()->timeSinceMotion() >= 0.5 &&
				!$user->getPlayer()->isCreative(true) &&
				!$user->getPlayer()->isFlying()
			) {
				if ($this->preVL++ > 6) {
					$this->addVL(1);
					$this->suppress();
					$this->preVL = 0;
					if ($this->overflowVL()) {
						$this->fail(sprintf('DIFF=%.5f', $diff));
					}
				}

			} else {
				$this->rewardPreVL($this->reward);
			}
		}
	}
}
