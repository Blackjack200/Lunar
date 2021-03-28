<?php


namespace blackjack200\lunar\detection\movement;


use blackjack200\lunar\detection\DetectionBase;
use blackjack200\lunar\user\User;
use pocketmine\network\mcpe\protocol\DataPacket;
use pocketmine\network\mcpe\protocol\MovePlayerPacket;

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
		if ($packet instanceof MovePlayerPacket && $user->getMoveData()->offGroundTick > 3) {
			$lastMD = $user->getMoveData()->lastMoveDelta;
			$curtMD = $user->getMoveData()->moveDelta;
			$last = hypot($lastMD->x, $lastMD->z);
			$curt = hypot($curtMD->x, $curtMD->z);

			//reference: https://github.com/GladUrBad/Medusa/blob/7be8d34ae0470f0655b59e213d7619b98a3f43ff/Impl/src/main/java/com/gladurbad/medusa/check/impl/movement/speed/SpeedA.java#L25
			$diff = $curt - ($last * 0.91 + ($user->getPlayer()->isSprinting() ? 0.0263 : 0.02));
			if ($diff > $this->maxDiff &&
				$user->getMoveData()->timeSinceTeleport() >= 2 &&
				$user->getMoveData()->timeSinceMotion() >= 2 &&
				!$user->getPlayer()->isSpectator() &&
				!$user->getPlayer()->isFlying()
			) {
				$this->addVL(1);
				if ($this->overflowVL()) {
					$this->fail(sprintf('DIFF=%.5f', $diff));
				}
			}
		}
	}
}