<?php


namespace blackjack200\lunar\detection\movement\speed;


use blackjack200\lunar\detection\DetectionBase;
use blackjack200\lunar\user\User;
use pocketmine\entity\Effect;
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

	public function handleClient(DataPacket $packet) : void {
		$user = $this->getUser();
		$info = $user->getMovementInfo();
		$expiredInfo = $user->getExpiredInfo();
		$player = $user->getPlayer();
		if (
			$packet instanceof MovePlayerPacket &&
			$info->inAirTick > 2 &&
			!$info->inVoid &&
			$info->checkFly &&
			$info->timeSinceTeleport() >= 1 &&
			$info->timeSinceMotion() >= 1 &&
			!$player->isCreative() &&
			!$player->isFlying() &&
			$expiredInfo->duration(Effect::SPEED) > 1 &&
			$expiredInfo->duration('flight') > 1
		) {
			$lastMD = $info->lastMoveDelta;
			$curtMD = $info->moveDelta;
			$last = hypot($lastMD->x, $lastMD->z);
			$curt = hypot($curtMD->x, $curtMD->z);

			//reference: https://github.com/GladUrBad/Medusa/blob/7be8d34ae0470f0655b59e213d7619b98a3f43ff/Impl/src/main/java/com/gladurbad/medusa/check/impl/movement/speed/SpeedA.java#L25
			$predicted = ($last * 0.91) + ($player->isSprinting() ? 0.026 : 0.02);
			$diff = $curt - $predicted;
			if ($predicted > 0.075 &&
				$diff > $this->maxDiff
			) {
				if ($this->preVL++ > 2) {
					$this->addVL(1);
					$this->revertMovement();
					$this->preVL = 0;
					if ($this->overflowVL()) {
						$this->fail(sprintf('DIFF=%.5f', $diff));
					}
				}

			} else {
				$this->preVL *= $this->reward;
			}
		}
	}
}
